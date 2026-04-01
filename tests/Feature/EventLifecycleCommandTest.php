<?php

use App\Models\Event;
use App\Models\EventAsset;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('lifecycle dry run reports due events without deleting files', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Dry Run Wedding',
        'status' => Event::STATUS_GRACE,
        'is_paid' => false,
        'payment_due_at' => now()->subHour(),
        'storage_used_bytes' => 204800,
        'upload_count' => 1,
        'media_export_status' => 'ready',
        'media_export_disk' => 'public',
        'media_export_path' => 'exports/dry-run-wedding.zip',
    ]);

    EventAsset::factory()->for($event)->for($owner)->create([
        'disk' => 'public',
        'path' => 'events/dry-run/photo.jpg',
        'thumbnail_path' => 'events/dry-run/photo-thumb.jpg',
        'preview_path' => 'events/dry-run/photo-preview.jpg',
        'size_bytes' => 204800,
    ]);

    collect([
        'events/dry-run/photo.jpg',
        'events/dry-run/photo-thumb.jpg',
        'events/dry-run/photo-preview.jpg',
        'exports/dry-run-wedding.zip',
    ])->each(fn (string $path) => Storage::disk('public')->put($path, 'fixture'));

    $this->artisan('events:enforce-lifecycle --dry-run')
        ->expectsOutput('Checked 1 events.')
        ->expectsOutput('Updated 0 events.')
        ->expectsOutput('Deleted 1 events.')
        ->expectsOutput('Affected 1 assets.')
        ->expectsOutput('Affected 4 stored files.')
        ->expectsOutput('Dry run complete. No events were deleted.')
        ->assertExitCode(0);

    expect(Event::query()->whereKey($event->id)->exists())->toBeTrue();

    Storage::disk('public')->assertExists('events/dry-run/photo.jpg');
    Storage::disk('public')->assertExists('events/dry-run/photo-thumb.jpg');
    Storage::disk('public')->assertExists('events/dry-run/photo-preview.jpg');
    Storage::disk('public')->assertExists('exports/dry-run-wedding.zip');
});

test('lifecycle cleanup deletes overdue unpaid events and all derivative files', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Cleanup Wedding',
        'status' => Event::STATUS_GRACE,
        'is_paid' => false,
        'payment_due_at' => now()->subHour(),
        'storage_used_bytes' => 409600,
        'upload_count' => 1,
        'media_export_status' => 'ready',
        'media_export_disk' => 'public',
        'media_export_path' => 'exports/cleanup-wedding.zip',
    ]);

    EventAsset::factory()->for($event)->for($owner)->create([
        'disk' => 'public',
        'path' => 'events/cleanup/photo.jpg',
        'thumbnail_path' => 'events/cleanup/photo-thumb.jpg',
        'preview_path' => 'events/cleanup/photo-preview.jpg',
        'watermarked_thumbnail_path' => 'events/cleanup/photo-thumb-watermarked.jpg',
        'watermarked_preview_path' => 'events/cleanup/photo-preview-watermarked.jpg',
        'watermarked_download_path' => 'events/cleanup/photo-download-watermarked.jpg',
        'video_thumbnail_path' => 'events/cleanup/video-thumb.jpg',
        'watermarked_video_thumbnail_path' => 'events/cleanup/video-thumb-watermarked.jpg',
        'video_preview_path' => 'events/cleanup/video-preview.mp4',
        'watermarked_video_preview_path' => 'events/cleanup/video-preview-watermarked.mp4',
        'watermarked_video_download_path' => 'events/cleanup/video-download-watermarked.mp4',
        'size_bytes' => 409600,
    ]);

    collect([
        'events/cleanup/photo.jpg',
        'events/cleanup/photo-thumb.jpg',
        'events/cleanup/photo-preview.jpg',
        'events/cleanup/photo-thumb-watermarked.jpg',
        'events/cleanup/photo-preview-watermarked.jpg',
        'events/cleanup/photo-download-watermarked.jpg',
        'events/cleanup/video-thumb.jpg',
        'events/cleanup/video-thumb-watermarked.jpg',
        'events/cleanup/video-preview.mp4',
        'events/cleanup/video-preview-watermarked.mp4',
        'events/cleanup/video-download-watermarked.mp4',
        'exports/cleanup-wedding.zip',
    ])->each(fn (string $path) => Storage::disk('public')->put($path, 'fixture'));

    $this->artisan('events:enforce-lifecycle')
        ->expectsOutput('Checked 1 events.')
        ->expectsOutput('Deleted 1 events.')
        ->expectsOutput('Affected 1 assets.')
        ->assertExitCode(0);

    expect(Event::query()->withTrashed()->whereKey($event->id)->exists())->toBeFalse();

    collect([
        'events/cleanup/photo.jpg',
        'events/cleanup/photo-thumb.jpg',
        'events/cleanup/photo-preview.jpg',
        'events/cleanup/photo-thumb-watermarked.jpg',
        'events/cleanup/photo-preview-watermarked.jpg',
        'events/cleanup/photo-download-watermarked.jpg',
        'events/cleanup/video-thumb.jpg',
        'events/cleanup/video-thumb-watermarked.jpg',
        'events/cleanup/video-preview.mp4',
        'events/cleanup/video-preview-watermarked.mp4',
        'events/cleanup/video-download-watermarked.mp4',
        'exports/cleanup-wedding.zip',
    ])->each(fn (string $path) => Storage::disk('public')->assertMissing($path));
});

test('lifecycle cleanup deletes paid expired events after retention ends', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Paid Wedding',
        'status' => Event::STATUS_EXPIRED,
        'is_paid' => true,
        'retention_ends_at' => now()->subHour(),
        'storage_used_bytes' => 512000,
        'upload_count' => 1,
        'media_export_status' => 'ready',
        'media_export_disk' => 'public',
        'media_export_path' => 'exports/paid-wedding.zip',
    ]);

    EventAsset::factory()->for($event)->for($owner)->create([
        'disk' => 'public',
        'path' => 'events/paid/photo.jpg',
        'preview_path' => 'events/paid/photo-preview.jpg',
        'size_bytes' => 512000,
    ]);

    Storage::disk('public')->put('events/paid/photo.jpg', 'fixture');
    Storage::disk('public')->put('events/paid/photo-preview.jpg', 'fixture');
    Storage::disk('public')->put('exports/paid-wedding.zip', 'fixture');

    $this->artisan('events:enforce-lifecycle')
        ->expectsOutput('Deleted 1 events.')
        ->assertExitCode(0);

    expect(Event::query()->withTrashed()->whereKey($event->id)->exists())->toBeFalse();

    Storage::disk('public')->assertMissing('events/paid/photo.jpg');
    Storage::disk('public')->assertMissing('events/paid/photo-preview.jpg');
    Storage::disk('public')->assertMissing('exports/paid-wedding.zip');
});
