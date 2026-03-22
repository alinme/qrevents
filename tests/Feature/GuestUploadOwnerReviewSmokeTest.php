<?php

use App\Jobs\GenerateEventMediaExport;
use App\Models\Event;
use App\Models\EventAsset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Config::set('filesystems.default', 'public');
    Config::set('events.upload_disk', 'public');
    Storage::fake('public');
});

it('covers guest upload through owner review and export download', function () {
    if (! class_exists(ZipArchive::class)) {
        test()->markTestSkipped('ZipArchive is not installed.');
    }

    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Smoke Test Event',
        'is_paid' => true,
        'download_all_enabled' => true,
        'moderation_enabled' => true,
        'auto_moderation_enabled' => false,
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
    ]);

    $this->post(route('events.album.upload', $event->share_token), [
        'files' => [UploadedFile::fake()->image('smoke-photo.jpg')->size(300)],
        'guest_name' => 'Elena',
        'message' => 'Ready for review.',
        'guest_token' => 'smoke-guest-token',
        'guest_intent' => 'upload_media',
    ])->assertRedirect();

    /** @var EventAsset $asset */
    $asset = $event->assets()->latest('id')->firstOrFail();

    expect($asset->moderation_status)->toBe('processing');

    $this->actingAs($owner)
        ->get(route('events.media', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Media')
            ->where('currentEvent.name', 'Smoke Test Event')
            ->where('eventNavigation.0.title', 'Events')
            ->where('eventNavigation.1.title', 'Workspace')
            ->where('eventNavigation.2.title', 'Media')
            ->where('eventNavigation.3.title', 'Settings')
            ->where('mediaAssets.0.guestName', 'Elena')
            ->where('mediaAssets.0.moderationStatus', 'processing')
        );

    $this->actingAs($owner)
        ->from(route('events.media', $event))
        ->patch(route('events.assets.moderation.update', [$event, $asset]), [
            'moderation_status' => 'approved',
        ])
        ->assertRedirect(route('events.media', $event));

    $asset->refresh();

    expect($asset->moderation_status)->toBe('approved')
        ->and($asset->reviewed_at)->not->toBeNull();

    $this->actingAs($owner)
        ->post(route('events.exports.media.start', $event))
        ->assertRedirect();

    $event->refresh();

    expect(in_array($event->media_export_status, ['pending', 'ready'], true))->toBeTrue()
        ->and($event->media_export_token)->not->toBeNull();

    if ($event->media_export_status === 'pending') {
        (new GenerateEventMediaExport($event->id, (string) $event->media_export_token))->handle();
        $event->refresh();
    }

    expect($event->media_export_status)->toBe('ready')
        ->and($event->media_export_path)->not->toBeNull();

    $downloadResponse = $this->actingAs($owner)
        ->get(route('events.exports.media.download', $event));

    $downloadResponse->assertOk();
    $downloadResponse->assertDownload();
});
