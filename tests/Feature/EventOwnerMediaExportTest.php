<?php

use App\Jobs\GenerateEventMediaExport;
use App\Models\Event;
use App\Models\EventAsset;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Config::set('filesystems.default', 'public');
    Config::set('events.upload_disk', 'public');
    Storage::fake('public');
});

it('queues an album export for the event owner', function () {
    Queue::fake();

    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'is_paid' => true,
        'paid_at' => now(),
        'download_all_enabled' => true,
    ]);

    EventAsset::query()->create([
        'event_id' => $event->id,
        'user_id' => $owner->id,
        'kind' => 'text',
        'disk' => 'public',
        'path' => 'events/unused/export-source.txt',
        'mime_type' => 'text/plain',
        'size_bytes' => 10,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Sofia',
            'text' => 'Celebrate well.',
        ],
    ]);

    $response = $this
        ->actingAs($owner)
        ->post(route('events.exports.media.start', $event));

    $response->assertRedirect();

    $event->refresh();

    expect($event->media_export_status)->toBe('pending')
        ->and($event->media_export_token)->not->toBeNull()
        ->and($event->media_export_requested_at)->not->toBeNull();

    Queue::assertPushed(GenerateEventMediaExport::class, function (GenerateEventMediaExport $job) use ($event): bool {
        return $job->eventId === $event->id && $job->token === $event->media_export_token;
    });
});

it('downloads a ready album export for the event owner', function () {
    if (! class_exists(ZipArchive::class)) {
        test()->markTestSkipped('ZipArchive is not installed.');
    }

    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'is_paid' => true,
        'paid_at' => now(),
        'download_all_enabled' => true,
    ]);

    Storage::disk('public')->put("events/{$event->id}/exports/photo.jpg", 'photo-binary');
    Storage::disk('public')->put("events/{$event->id}/exports/video.mp4", 'video-binary');

    EventAsset::query()->create([
        'event_id' => $event->id,
        'user_id' => $owner->id,
        'kind' => 'photo',
        'disk' => 'public',
        'path' => "events/{$event->id}/exports/photo.jpg",
        'original_filename' => 'party-photo.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 12,
        'moderation_status' => 'approved',
        'metadata' => ['guest_name' => 'Elena'],
    ]);

    EventAsset::query()->create([
        'event_id' => $event->id,
        'user_id' => $owner->id,
        'kind' => 'video',
        'disk' => 'public',
        'path' => "events/{$event->id}/exports/video.mp4",
        'original_filename' => 'dance-floor.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 12,
        'moderation_status' => 'approved',
        'metadata' => ['guest_name' => 'James'],
    ]);

    EventAsset::query()->create([
        'event_id' => $event->id,
        'user_id' => $owner->id,
        'kind' => 'text',
        'disk' => 'public',
        'path' => 'events/unused/text-post.txt',
        'original_filename' => 'wish.txt',
        'mime_type' => 'text/plain',
        'size_bytes' => 0,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Sofia',
            'text' => 'Love you both forever.',
            'message' => 'From our table',
        ],
    ]);

    EventAsset::query()->create([
        'event_id' => $event->id,
        'user_id' => $owner->id,
        'kind' => 'photo',
        'disk' => 'public',
        'path' => "events/{$event->id}/exports/rejected-photo.jpg",
        'original_filename' => 'rejected-photo.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 12,
        'moderation_status' => 'rejected',
        'metadata' => ['guest_name' => 'Blocked'],
    ]);

    $token = 'export-token-1';
    $event->forceFill([
        'media_export_status' => 'pending',
        'media_export_token' => $token,
        'media_export_requested_at' => now(),
    ])->save();

    (new GenerateEventMediaExport($event->id, $token))->handle();
    $event->refresh();

    expect($event->media_export_status)->toBe('ready')
        ->and($event->media_export_disk)->toBe('public')
        ->and($event->media_export_path)->not->toBeNull();

    Storage::disk('public')->assertExists((string) $event->media_export_path);

    $response = $this
        ->actingAs($owner)
        ->get(route('events.exports.media.download', $event));

    $response->assertOk();
    $response->assertDownload();

    $archivePath = tempnam(sys_get_temp_dir(), 'qr-export-');
    expect(is_file($archivePath))->toBeTrue();
    file_put_contents($archivePath, $response->streamedContent());

    $zip = new ZipArchive;
    expect($zip->open($archivePath))->toBeTrue();

    $entryNames = [];
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $stat = $zip->statIndex($index);
        if (is_array($stat) && is_string($stat['name'] ?? null)) {
            $entryNames[] = $stat['name'];
        }
    }

    expect($entryNames)->toHaveCount(3)
        ->and(collect($entryNames)->contains(fn (string $name): bool => str_starts_with($name, 'photos/')))->toBeTrue()
        ->and(collect($entryNames)->contains(fn (string $name): bool => str_starts_with($name, 'videos/')))->toBeTrue()
        ->and(collect($entryNames)->contains(fn (string $name): bool => str_starts_with($name, 'text-posts/')))->toBeTrue()
        ->and(collect($entryNames)->contains(fn (string $name): bool => str_contains($name, 'rejected')))->toBeFalse();

    $textEntryName = collect($entryNames)->first(
        fn (string $name): bool => str_starts_with($name, 'text-posts/'),
    );

    expect($textEntryName)->not->toBeFalse();
    $textContents = $zip->getFromName((string) $textEntryName);

    expect($textContents)->toContain('Guest: Sofia')
        ->toContain('Message: From our table')
        ->toContain('Love you both forever.');

    $zip->close();
    @unlink($archivePath);
});

it('blocks exports when the plan does not include download all', function () {
    Queue::fake();

    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'is_paid' => true,
        'paid_at' => now(),
        'download_all_enabled' => false,
    ]);

    EventAsset::query()->create([
        'event_id' => $event->id,
        'user_id' => $owner->id,
        'kind' => 'photo',
        'disk' => 'public',
        'path' => 'events/unused/export-source.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 10,
        'moderation_status' => 'approved',
    ]);

    $this->actingAs($owner)
        ->post(route('events.exports.media.start', $event))
        ->assertRedirect()
        ->assertSessionHas('error', 'Download all is available on Plus and Pro after payment.');

    Queue::assertNothingPushed();
});
