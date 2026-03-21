<?php

use App\Models\Event;
use App\Models\EventAsset;
use App\Models\EventGuest;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    config(['events.upload_disk' => 'public']);
});

it('reports missing and orphan storage files for an event', function () {
    $event = Event::factory()->create();

    $asset = EventAsset::factory()->for($event)->create([
        'disk' => 'public',
        'path' => "events/{$event->id}/uploads/live-photo.jpg",
        'thumbnail_path' => "events/{$event->id}/variants/1/thumb.jpg",
        'preview_path' => "events/{$event->id}/variants/1/preview.jpg",
    ]);

    $guest = EventGuest::factory()->for($event)->create([
        'avatar_disk' => 'public',
        'avatar_path' => "events/{$event->id}/guests/{$asset->id}/avatar.jpg",
    ]);

    $branding = is_array($event->branding) ? $event->branding : [];
    $branding['logo_disk'] = 'public';
    $branding['logo_path'] = "events/{$event->id}/branding/logo.png";
    $event->forceFill(['branding' => $branding])->save();

    Storage::disk('public')->put($asset->path, 'photo');
    Storage::disk('public')->put($asset->thumbnail_path, 'thumb');
    Storage::disk('public')->put($guest->avatar_path, 'avatar');
    Storage::disk('public')->put("events/{$event->id}/uploads/orphan.jpg", 'orphan');

    $this->artisan("events:audit-storage {$event->id}")
        ->expectsOutput("Scope: event {$event->id} {$event->name}")
        ->expectsOutput('DB-managed paths: 5')
        ->expectsOutput('Storage files: 4')
        ->expectsOutput('Missing files: 2')
        ->expectsOutput('Orphan files: 1')
        ->assertSuccessful();
});

it('deletes orphan storage files when requested', function () {
    $event = Event::factory()->create();

    EventAsset::factory()->for($event)->create([
        'disk' => 'public',
        'path' => "events/{$event->id}/uploads/live-photo.jpg",
    ]);

    Storage::disk('public')->put("events/{$event->id}/uploads/live-photo.jpg", 'photo');
    Storage::disk('public')->put("events/{$event->id}/uploads/orphan.jpg", 'orphan');

    $this->artisan("events:audit-storage {$event->id} --delete-orphans")
        ->expectsOutput("Scope: event {$event->id} {$event->name}")
        ->expectsOutput('Deleted orphan files: 1')
        ->assertSuccessful();

    Storage::disk('public')->assertExists("events/{$event->id}/uploads/live-photo.jpg");
    Storage::disk('public')->assertMissing("events/{$event->id}/uploads/orphan.jpg");
});
