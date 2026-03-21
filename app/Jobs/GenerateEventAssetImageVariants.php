<?php

namespace App\Jobs;

use App\Models\EventAsset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;
use ImagickDraw;
use ImagickException;
use ImagickPixel;
use Throwable;

class GenerateEventAssetImageVariants implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly int $assetId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $asset = EventAsset::query()->find($this->assetId);
        if (! $asset instanceof EventAsset) {
            return;
        }

        if ($asset->kind !== 'photo' || ! str_starts_with((string) $asset->mime_type, 'image/')) {
            return;
        }

        $disk = Storage::disk($asset->disk);
        if (! $this->storagePathExists($asset->disk, $asset->path)) {
            return;
        }

        try {
            $originalContents = $disk->get($asset->path);
        } catch (Throwable) {
            return;
        }

        if ($originalContents === '') {
            return;
        }

        $format = $this->normalizedFormat();
        $baseDirectory = sprintf('events/%d/variants/%d', $asset->event_id, $asset->id);
        $thumbnailPath = sprintf('%s/thumb.%s', $baseDirectory, $format);
        $previewPath = sprintf('%s/preview.%s', $baseDirectory, $format);
        $watermarkedThumbnailPath = sprintf('%s/thumb-watermarked.%s', $baseDirectory, $format);
        $watermarkedPreviewPath = sprintf('%s/preview-watermarked.%s', $baseDirectory, $format);
        $watermarkedDownloadPath = sprintf('%s/download-watermarked.%s', $baseDirectory, $format);
        $watermarkText = trim((string) config('events.image_variants.watermark_text', 'QREVENTS'));

        try {
            $thumbnailBlob = $this->variantBlob(
                $originalContents,
                max(160, (int) config('events.image_variants.thumbnail_max_pixels', 640)),
                $format,
            );
            $previewBlob = $this->variantBlob(
                $originalContents,
                max(480, (int) config('events.image_variants.preview_max_pixels', 1600)),
                $format,
            );
            $watermarkedThumbnailBlob = $this->variantBlob(
                $originalContents,
                max(160, (int) config('events.image_variants.thumbnail_max_pixels', 640)),
                $format,
                $watermarkText,
            );
            $watermarkedPreviewBlob = $this->variantBlob(
                $originalContents,
                max(480, (int) config('events.image_variants.preview_max_pixels', 1600)),
                $format,
                $watermarkText,
            );
            $watermarkedDownloadBlob = $this->variantBlob(
                $originalContents,
                max(800, (int) config('events.image_variants.watermarked_download_max_pixels', 2400)),
                $format,
                $watermarkText,
            );
        } catch (ImagickException) {
            return;
        }

        $writtenPaths = [];
        foreach ([
            $thumbnailPath => $thumbnailBlob,
            $previewPath => $previewBlob,
            $watermarkedThumbnailPath => $watermarkedThumbnailBlob,
            $watermarkedPreviewPath => $watermarkedPreviewBlob,
            $watermarkedDownloadPath => $watermarkedDownloadBlob,
        ] as $path => $blob) {
            if (! $this->writeVariantToStorage($asset->disk, $path, $blob)) {
                $disk->delete($writtenPaths);

                return;
            }

            $writtenPaths[] = $path;
        }

        $asset->forceFill([
            'thumbnail_path' => $thumbnailPath,
            'preview_path' => $previewPath,
            'watermarked_thumbnail_path' => $watermarkedThumbnailPath,
            'watermarked_preview_path' => $watermarkedPreviewPath,
            'watermarked_download_path' => $watermarkedDownloadPath,
            'is_watermarked' => true,
        ])->save();
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
            max(40, min(95, (int) config('events.image_variants.quality', 82))),
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

    private function normalizedFormat(): string
    {
        $format = Str::lower((string) config('events.image_variants.format', 'jpg'));

        return in_array($format, ['jpg', 'jpeg', 'webp', 'png'], true)
            ? $format
            : 'jpg';
    }

    private function writeVariantToStorage(string $disk, string $path, string $contents): bool
    {
        try {
            $stored = Storage::disk($disk)->put($path, $contents, ['visibility' => 'private']);
        } catch (Throwable $exception) {
            Log::warning('Image variant storage write failed.', [
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
            Log::warning('Image variant storage write did not persist.', [
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
            Log::warning('Image variant existence check failed.', [
                'disk' => $disk,
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
