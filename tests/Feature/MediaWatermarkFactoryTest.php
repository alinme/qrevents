<?php

use App\Models\Event;
use App\Models\EventAsset;
use App\Models\User;
use App\Support\MediaWatermarkFactory;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('uses the business logo when the event owner has one', function () {
    $logoPath = 'business/logos/wide-logo.png';
    Storage::disk('public')->put($logoPath, testPngBlob(480, 120));

    $owner = User::factory()->business()->create([
        'business_profile' => [
            'company_name' => 'Studio Events SRL',
            'brand_name' => 'Studio Events',
            'billing_email' => 'billing@studio-events.test',
            'logo_disk' => 'public',
            'logo_path' => $logoPath,
        ],
    ]);

    $event = Event::factory()->for($owner)->create();
    $asset = EventAsset::factory()->for($event)->for($owner)->create([
        'kind' => 'photo',
        'mime_type' => 'image/jpeg',
    ]);

    $overlay = (new MediaWatermarkFactory)->makeImageOverlay($asset, 1600, 900);

    expect($overlay)->not->toBeNull()
        ->and((int) $overlay?->getImageWidth())->toBeGreaterThan((int) (($overlay?->getImageHeight() ?? 0) * 2.5));

    $overlay?->clear();
    $overlay?->destroy();
});

it('falls back to the app logo when the event owner has no business logo', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create();
    $asset = EventAsset::factory()->for($event)->for($owner)->create([
        'kind' => 'video',
        'mime_type' => 'video/mp4',
    ]);

    $overlayPath = (new MediaWatermarkFactory)->makeVideoOverlayPath($asset, 1280, 720);

    expect($overlayPath)->not->toBeNull()
        ->and(is_file((string) $overlayPath))->toBeTrue();

    if (is_string($overlayPath) && is_file($overlayPath)) {
        @unlink($overlayPath);
    }
});

function testPngBlob(int $width, int $height): string
{
    $image = imagecreatetruecolor($width, $height);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    $accent = imagecolorallocatealpha($image, 217, 119, 6, 20);
    imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $accent);

    ob_start();
    imagepng($image);
    $blob = (string) ob_get_clean();
    imagedestroy($image);

    return $blob;
}
