<?php

namespace App\Jobs;

use App\Models\EventAsset;
use App\Support\MediaWatermarkFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;
use ImagickException;
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
        $watermarkFactory = new MediaWatermarkFactory;

        try {
            $thumbnailBlob = $this->variantBlob(
                $asset,
                $originalContents,
                max(160, (int) config('events.image_variants.thumbnail_max_pixels', 640)),
                $format,
            );
            $previewBlob = $this->variantBlob(
                $asset,
                $originalContents,
                max(480, (int) config('events.image_variants.preview_max_pixels', 1600)),
                $format,
            );
            $watermarkedThumbnailBlob = $this->variantBlob(
                $asset,
                $originalContents,
                max(160, (int) config('events.image_variants.thumbnail_max_pixels', 640)),
                $format,
                true,
                $watermarkFactory,
            );
            $watermarkedPreviewBlob = $this->variantBlob(
                $asset,
                $originalContents,
                max(480, (int) config('events.image_variants.preview_max_pixels', 1600)),
                $format,
                true,
                $watermarkFactory,
            );
            $watermarkedDownloadBlob = $this->variantBlob(
                $asset,
                $originalContents,
                max(800, (int) config('events.image_variants.watermarked_download_max_pixels', 2400)),
                $format,
                true,
                $watermarkFactory,
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
        EventAsset $asset,
        string $contents,
        int $maxPixels,
        string $format,
        bool $watermarked = false,
        ?MediaWatermarkFactory $watermarkFactory = null,
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

        if ($watermarked) {
            $overlay = ($watermarkFactory ?? new MediaWatermarkFactory)->makeImageOverlay(
                $asset,
                (int) $image->getImageWidth(),
                (int) $image->getImageHeight(),
            );

            if ($overlay instanceof Imagick) {
                $marginX = max(14, (int) round($image->getImageWidth() * 0.03));
                $marginY = max(14, (int) round($image->getImageHeight() * 0.03));

                $image->compositeImage(
                    $overlay,
                    Imagick::COMPOSITE_OVER,
                    max(0, (int) $image->getImageWidth() - (int) $overlay->getImageWidth() - $marginX),
                    max(0, (int) $image->getImageHeight() - (int) $overlay->getImageHeight() - $marginY),
                );

                $overlay->clear();
                $overlay->destroy();
            }
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
