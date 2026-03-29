<?php

namespace App\Support;

use App\Models\EventAsset;
use Illuminate\Support\Facades\Storage;
use Imagick;
use ImagickException;
use Throwable;

class MediaWatermarkFactory
{
    public function makeImageOverlay(EventAsset $asset, int $frameWidth, int $frameHeight): ?Imagick
    {
        $overlay = $this->baseOverlay($asset, $frameWidth, $frameHeight);
        if (! $overlay instanceof Imagick) {
            return null;
        }

        return $overlay;
    }

    public function makeVideoOverlayPath(EventAsset $asset, int $frameWidth, int $frameHeight): ?string
    {
        $overlay = $this->baseOverlay($asset, $frameWidth, $frameHeight);
        if (! $overlay instanceof Imagick) {
            return null;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'qr-watermark-');
        if ($tempPath === false) {
            $overlay->clear();
            $overlay->destroy();

            return null;
        }

        $pngPath = $tempPath.'.png';

        try {
            $overlay->setImageFormat('png');
            $overlay->writeImage($pngPath);
        } catch (Throwable) {
            if (is_file($tempPath)) {
                @unlink($tempPath);
            }
            if (is_file($pngPath)) {
                @unlink($pngPath);
            }

            $overlay->clear();
            $overlay->destroy();

            return null;
        }

        if (is_file($tempPath)) {
            @unlink($tempPath);
        }

        $overlay->clear();
        $overlay->destroy();

        return is_file($pngPath) ? $pngPath : null;
    }

    private function baseOverlay(EventAsset $asset, int $frameWidth, int $frameHeight): ?Imagick
    {
        $blob = $this->watermarkBlob($asset);
        if (! is_string($blob) || $blob === '') {
            return null;
        }

        try {
            $overlay = new Imagick;
            $overlay->readImageBlob($blob);
            $overlay = $overlay->coalesceImages();
            $overlay->setIteratorIndex(0);
            $overlay->autoOrient();
            $overlay->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);

            $sourceWidth = max(1, (int) $overlay->getImageWidth());
            $sourceHeight = max(1, (int) $overlay->getImageHeight());

            $maxWidth = max(90, (int) round($frameWidth * 0.16));
            $maxHeight = max(28, (int) round($frameHeight * 0.085));
            $scale = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight, 1.0);

            $targetWidth = max(1, (int) round($sourceWidth * $scale));
            $targetHeight = max(1, (int) round($sourceHeight * $scale));

            $overlay->resizeImage($targetWidth, $targetHeight, Imagick::FILTER_LANCZOS, 1);
            $overlay->evaluateImage(Imagick::EVALUATE_MULTIPLY, 0.48, Imagick::CHANNEL_ALPHA);

            return $overlay;
        } catch (ImagickException|Throwable) {
            return null;
        }
    }

    private function watermarkBlob(EventAsset $asset): ?string
    {
        $businessBlob = $this->businessLogoBlob($asset);
        if (is_string($businessBlob) && $businessBlob !== '') {
            return $businessBlob;
        }

        foreach ([
            public_path('logo.png'),
            public_path('apple-touch-icon.png'),
        ] as $path) {
            if (is_file($path)) {
                $contents = @file_get_contents($path);

                if (is_string($contents) && $contents !== '') {
                    return $contents;
                }
            }
        }

        return null;
    }

    private function businessLogoBlob(EventAsset $asset): ?string
    {
        $event = $asset->relationLoaded('event')
            ? $asset->event
            : $asset->event()->with('user')->first();

        $owner = $event?->relationLoaded('user')
            ? $event->user
            : $event?->user()->first();

        if ($owner === null || ! $owner->isBusinessAccount()) {
            return null;
        }

        $profile = is_array($owner->business_profile) ? $owner->business_profile : [];
        $disk = $profile['logo_disk'] ?? null;
        $path = $profile['logo_path'] ?? null;

        if (! is_string($disk) || ! is_string($path) || trim($path) === '') {
            return null;
        }

        try {
            if (! Storage::disk($disk)->exists($path)) {
                return null;
            }

            $contents = Storage::disk($disk)->get($path);

            return is_string($contents) && $contents !== '' ? $contents : null;
        } catch (Throwable) {
            return null;
        }
    }
}
