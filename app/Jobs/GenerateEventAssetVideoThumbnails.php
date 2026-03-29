<?php

namespace App\Jobs;

use App\Models\EventAsset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;
use ImagickDraw;
use ImagickException;
use ImagickPixel;
use Symfony\Component\Process\Process;
use Throwable;

class GenerateEventAssetVideoThumbnails implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $assetId) {}

    public function handle(): void
    {
        $asset = EventAsset::query()->find($this->assetId);
        if (! $asset instanceof EventAsset) {
            return;
        }

        if ($asset->kind !== 'video' || ! str_starts_with((string) $asset->mime_type, 'video/')) {
            return;
        }

        $disk = Storage::disk($asset->disk);
        if (! $this->storagePathExists($asset->disk, $asset->path)) {
            return;
        }

        $inputPath = $this->materializeAssetToTempFile($disk, $asset->path);
        if ($inputPath === null) {
            return;
        }

        $baseDirectory = sprintf('events/%d/variants/%d', $asset->event_id, $asset->id);
        $format = $this->normalizedFormat();
        $thumbnailPath = sprintf('%s/video-thumb.%s', $baseDirectory, $format);
        $previewPath = sprintf('%s/video-preview.mp4', $baseDirectory);
        $watermarkText = trim((string) config('app.name', 'EventSmart'));

        try {
            $posterBlob = $this->extractPosterBlob($inputPath, $asset);
            if ($posterBlob === null) {
                return;
            }

            $thumbnailBlob = $this->variantBlob(
                $posterBlob,
                max(200, (int) config('events.video_variants.thumbnail_max_pixels', 960)),
                $format,
            );
            $previewVideoPath = $this->transcodeVariant(
                $inputPath,
                max(640, (int) config('events.video_variants.preview_max_width', 1280)),
                true,
                $watermarkText,
                $this->normalizedCrf(),
            );
            if ($previewVideoPath === null) {
                return;
            }
        } catch (ImagickException|Throwable) {
            return;
        } finally {
            if (is_file($inputPath)) {
                @unlink($inputPath);
            }
        }

        $previewVideoBlob = File::get($previewVideoPath);

        if (! is_string($previewVideoBlob)) {
            return;
        }

        $writtenPaths = [];
        foreach ([
            $thumbnailPath => $thumbnailBlob,
            $previewPath => $previewVideoBlob,
        ] as $path => $contents) {
            if (! $this->writeVariantToStorage($asset->disk, $path, $contents)) {
                $disk->delete($writtenPaths);

                return;
            }

            $writtenPaths[] = $path;
        }

        if (is_file($previewVideoPath)) {
            @unlink($previewVideoPath);
        }

        $asset->forceFill([
            'video_thumbnail_path' => $thumbnailPath,
            'watermarked_video_thumbnail_path' => null,
            'video_preview_path' => $previewPath,
            'watermarked_video_preview_path' => null,
            'watermarked_video_download_path' => null,
        ])->save();
    }

    private function materializeAssetToTempFile($disk, string $path): ?string
    {
        $stream = $disk->readStream($path);
        if (! is_resource($stream)) {
            return null;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'qr-video-input-');
        if ($tempPath === false) {
            fclose($stream);

            return null;
        }

        $tempHandle = fopen($tempPath, 'wb');
        if (! is_resource($tempHandle)) {
            fclose($stream);
            @unlink($tempPath);

            return null;
        }

        try {
            stream_copy_to_stream($stream, $tempHandle);
        } catch (Throwable) {
            fclose($stream);
            fclose($tempHandle);
            @unlink($tempPath);

            return null;
        }

        fclose($stream);
        fclose($tempHandle);

        return $tempPath;
    }

    private function extractPosterBlob(string $inputPath, EventAsset $asset): ?string
    {
        $outputPath = tempnam(sys_get_temp_dir(), 'qr-video-poster-');
        if ($outputPath === false) {
            return null;
        }

        $outputJpgPath = $outputPath.'.jpg';

        try {

            $timestampSeconds = max(
                0.1,
                min(
                    5.0,
                    (float) config('events.video_variants.poster_timestamp_seconds', 1.2),
                    max(0.1, ((float) ($asset->duration_seconds ?? 1)) / 2),
                ),
            );
            $ffmpegBinary = (string) config('events.video_variants.ffmpeg_binary', 'ffmpeg');

            $process = new Process([
                $ffmpegBinary,
                '-y',
                '-ss',
                number_format($timestampSeconds, 2, '.', ''),
                '-i',
                $inputPath,
                '-frames:v',
                '1',
                '-q:v',
                '2',
                $outputJpgPath,
            ]);
            $process->setTimeout(60);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($outputJpgPath)) {
                return null;
            }

            $blob = file_get_contents($outputJpgPath);

            return is_string($blob) && $blob !== '' ? $blob : null;
        } catch (Throwable) {
            return null;
        } finally {
            if (is_file($outputPath)) {
                @unlink($outputPath);
            }
            if (is_file($outputJpgPath)) {
                @unlink($outputJpgPath);
            }
        }
    }

    private function transcodeVariant(
        string $inputPath,
        int $maxWidth,
        bool $watermarked,
        ?string $watermarkText,
        int $crf,
    ): ?string {
        $outputPath = tempnam(sys_get_temp_dir(), 'qr-video-variant-');
        if ($outputPath === false) {
            return null;
        }

        $outputMp4Path = $outputPath.'.mp4';
        $ffmpegBinary = (string) config('events.video_variants.ffmpeg_binary', 'ffmpeg');
        $watermarkOverlayPath = null;
        if ($watermarked && $watermarkText !== null && trim($watermarkText) !== '') {
            $watermarkOverlayPath = $this->createWatermarkOverlay($maxWidth, $watermarkText);
        }

        $command = [
            $ffmpegBinary,
            '-y',
            '-i',
            $inputPath,
        ];

        if ($watermarkOverlayPath !== null) {
            $command[] = '-i';
            $command[] = $watermarkOverlayPath;
        }

        $command = array_merge($command, [
            '-map',
            '0:v:0',
            '-map',
            '0:a?',
        ]);

        if ($watermarkOverlayPath !== null) {
            $command[] = '-filter_complex';
            $command[] = sprintf(
                '[0:v]scale=min(%d\\,iw):-2[scaled];[scaled][1:v]overlay=main_w-overlay_w-36:main_h-overlay_h-28',
                $maxWidth,
            );
        } else {
            $command[] = '-vf';
            $command[] = sprintf('scale=min(%d\\,iw):-2', $maxWidth);
        }

        $command = array_merge($command, [
            '-c:v',
            'libx264',
            '-preset',
            (string) config('events.video_variants.preset', 'faster'),
            '-crf',
            (string) $crf,
            '-pix_fmt',
            'yuv420p',
            '-c:a',
            'aac',
            '-b:a',
            '128k',
            '-movflags',
            '+faststart',
            $outputMp4Path,
        ]);

        $process = new Process($command);
        $process->setTimeout(180);
        $process->run();

        if (! $process->isSuccessful() || ! is_file($outputMp4Path)) {
            if (is_file($outputPath)) {
                @unlink($outputPath);
            }
            if (is_file($outputMp4Path)) {
                @unlink($outputMp4Path);
            }
            if ($watermarkOverlayPath !== null && is_file($watermarkOverlayPath)) {
                @unlink($watermarkOverlayPath);
            }

            return null;
        }

        if (is_file($outputPath)) {
            @unlink($outputPath);
        }
        if ($watermarkOverlayPath !== null && is_file($watermarkOverlayPath)) {
            @unlink($watermarkOverlayPath);
        }

        return $outputMp4Path;
    }

    private function normalizedCrf(): int
    {
        $qualityPercent = max(
            1,
            min(100, (int) config('events.video_variants.quality_percent', 76)),
        );

        return (int) round(36 - (($qualityPercent / 100) * 18));
    }

    /**
     * @throws ImagickException
     */
    private function variantBlob(
        string $contents,
        int $maxPixels,
        string $format,
        ?string $watermarkText = null,
    ): string {
        $image = new Imagick;
        $image->readImageBlob($contents);
        $image = $image->coalesceImages();
        $image->setIteratorIndex(0);
        $image->autoOrient();
        $image->stripImage();

        $width = max(1, (int) $image->getImageWidth());
        $height = max(1, (int) $image->getImageHeight());
        $scale = min($maxPixels / $width, $maxPixels / $height, 1);
        if ($scale < 1) {
            $targetWidth = max(1, (int) round($width * $scale));
            $targetHeight = max(1, (int) round($height * $scale));
            $image->resizeImage($targetWidth, $targetHeight, Imagick::FILTER_LANCZOS, 1);
        }

        if ($watermarkText !== null && $watermarkText !== '') {
            $this->applyWatermark($image, $watermarkText);
        }

        $targetFormat = Str::lower($format);
        if ($targetFormat === 'jpg') {
            $targetFormat = 'jpeg';
        }

        if ($targetFormat === 'jpeg') {
            $image->setImageBackgroundColor('white');
            $image = $image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
        }

        $image->setImageFormat($targetFormat);
        $image->setImageCompressionQuality(
            max(40, min(95, (int) config('events.video_variants.quality', 80))),
        );

        $blob = $image->getImagesBlob();
        $image->clear();
        $image->destroy();

        return $blob;
    }

    /**
     * @throws ImagickException
     */
    private function applyWatermark(Imagick $image, string $text): void
    {
        $width = max(1, (int) $image->getImageWidth());
        $height = max(1, (int) $image->getImageHeight());
        $fontSize = max(28, (int) round(min($width, $height) * 0.14));

        $shadowDraw = new ImagickDraw;
        $fontPath = $this->watermarkFontPath();
        if ($fontPath !== null) {
            $shadowDraw->setFont($fontPath);
        }
        $shadowDraw->setFontSize($fontSize);
        $shadowDraw->setTextAlignment(Imagick::ALIGN_CENTER);
        $shadowDraw->setGravity(Imagick::GRAVITY_CENTER);
        $shadowDraw->setFillColor(new ImagickPixel('rgba(15,23,42,0.16)'));

        $draw = new ImagickDraw;
        if ($fontPath !== null) {
            $draw->setFont($fontPath);
        }
        $draw->setFontSize($fontSize);
        $draw->setTextAlignment(Imagick::ALIGN_CENTER);
        $draw->setGravity(Imagick::GRAVITY_CENTER);
        $draw->setFillColor(new ImagickPixel('rgba(255,255,255,0.18)'));
        $draw->setStrokeColor(new ImagickPixel('rgba(255,255,255,0.30)'));
        $draw->setStrokeWidth(max(1, $fontSize / 30));

        $overlay = new Imagick;
        $overlay->newImage($width, $height, new ImagickPixel('transparent'));
        $overlay->setImageFormat('png');
        $overlay->annotateImage($shadowDraw, 0, 4, 0, $text);
        $overlay->annotateImage($draw, 0, 0, 0, $text);
        $overlay->gaussianBlurImage(0.7, 0.5);

        $image->compositeImage($overlay, Imagick::COMPOSITE_OVER, 0, 0);
        $overlay->clear();
        $overlay->destroy();
    }

    private function watermarkFontPath(): ?string
    {
        $configured = trim((string) config('events.image_variants.watermark_font', ''));
        if ($configured !== '') {
            if (is_file($configured)) {
                return $configured;
            }

            $relativeToApp = base_path($configured);
            if (is_file($relativeToApp)) {
                return $relativeToApp;
            }
        }

        $candidates = [
            '/System/Library/Fonts/Supplemental/Arial.ttf',
            '/System/Library/Fonts/Supplemental/Helvetica.ttc',
            '/Library/Fonts/Arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/dejavu/DejaVuSans.ttf',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @throws ImagickException
     */
    private function createWatermarkOverlay(int $maxWidth, string $text): ?string
    {
        $path = tempnam(sys_get_temp_dir(), 'qr-video-watermark-');
        if ($path === false) {
            return null;
        }

        $pngPath = $path.'.png';
        $width = max(240, min(640, (int) round($maxWidth * 0.26)));
        $height = max(72, (int) round($width * 0.24));
        $fontSize = max(18, (int) round($height * 0.36));

        $shadowDraw = new ImagickDraw;
        $fontPath = $this->watermarkFontPath();
        if ($fontPath !== null) {
            $shadowDraw->setFont($fontPath);
        }
        $shadowDraw->setFontSize($fontSize);
        $shadowDraw->setTextAlignment(Imagick::ALIGN_RIGHT);
        $shadowDraw->setFillColor(new ImagickPixel('rgba(15,23,42,0.18)'));

        $draw = new ImagickDraw;
        if ($fontPath !== null) {
            $draw->setFont($fontPath);
        }
        $draw->setFontSize($fontSize);
        $draw->setTextAlignment(Imagick::ALIGN_RIGHT);
        $draw->setFillColor(new ImagickPixel('rgba(255,255,255,0.20)'));

        $overlay = new Imagick;
        $overlay->newImage($width, $height, new ImagickPixel('transparent'));
        $overlay->setImageFormat('png');
        $baseline = (int) round($height * 0.72);
        $rightInset = max(12, (int) round($width * 0.08));
        $overlay->annotateImage($shadowDraw, $width - $rightInset + 2, $baseline + 2, 0, $text);
        $overlay->annotateImage($draw, $width - $rightInset, $baseline, 0, $text);
        $overlay->gaussianBlurImage(0.5, 0.4);
        $overlay->writeImage($pngPath);
        $overlay->clear();
        $overlay->destroy();

        if (is_file($path)) {
            @unlink($path);
        }

        return is_file($pngPath) ? $pngPath : null;
    }

    private function normalizedFormat(): string
    {
        $format = Str::lower((string) config('events.video_variants.format', 'jpg'));

        return in_array($format, ['jpg', 'jpeg', 'webp', 'png'], true) ? $format : 'jpg';
    }

    private function writeVariantToStorage(string $disk, string $path, string $contents): bool
    {
        try {
            $stored = Storage::disk($disk)->put($path, $contents, ['visibility' => 'private']);
        } catch (Throwable $exception) {
            Log::warning('Video variant storage write failed.', [
                'disk' => $disk,
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        if (! $stored) {
            return false;
        }

        if (! $this->storagePathExists($disk, $path)) {
            Log::warning('Video variant storage write did not persist.', [
                'disk' => $disk,
                'path' => $path,
            ]);
            Storage::disk($disk)->delete($path);

            return false;
        }

        return true;
    }

    private function storagePathExists(string $disk, ?string $path): bool
    {
        if (! is_string($path) || trim($path) === '') {
            return false;
        }

        try {
            return Storage::disk($disk)->exists($path);
        } catch (Throwable $exception) {
            Log::warning('Video variant existence check failed.', [
                'disk' => $disk,
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
