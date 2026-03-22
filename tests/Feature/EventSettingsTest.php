<?php

use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('updates event settings and recalculates event windows', function () {
    CarbonImmutable::setTestNow('2026-03-10 12:00:00');
    Config::set('events.upload_disk', 'public');
    Storage::fake('public');

    $user = User::factory()->create();
    $event = Event::factory()->for($user)->create([
        'type' => 'wedding',
        'name' => 'Original Event Name',
        'event_date' => '2026-05-14',
        'timezone' => 'Europe/Bucharest',
        'album_public' => true,
        'moderation_enabled' => true,
        'auto_moderation_enabled' => true,
        'branding' => null,
    ]);

    $this->actingAs($user);

    $response = $this->from(route('events.settings', $event))
        ->patch(route('events.settings.update', $event), [
            'type' => 'party',
            'name' => 'Updated Event Name',
            'event_date' => '2026-06-20',
            'timezone' => 'Europe/Bucharest',
            'album_public' => false,
            'album_permission' => 'upload_only',
            'moderation_enabled' => false,
            'auto_moderation_enabled' => true,
            'allowed_media_types' => ['photo', 'video', 'text'],
            'display_language' => 'ro',
            'hide_side_images' => true,
            'hide_qr_code' => true,
            'hide_caption' => true,
            'caption_theme' => 'light',
            'disable_guest_download' => true,
            'welcome_screen_enabled' => true,
            'welcome_screen_subtitle' => 'Please share your best memories.',
            'welcome_screen_collect_name' => true,
            'welcome_screen_collect_email' => true,
            'welcome_screen_collect_phone' => false,
            'album_background_enabled' => true,
            'album_background_mode' => 'image',
            'album_background_color' => '#0F172A',
            'album_background_file' => UploadedFile::fake()->image('album-bg.png')->size(350),
            'remove_album_background' => false,
            'text_posts_backgrounds_enabled' => true,
            'text_posts_background_palette' => ['#1D4ED8', '#0F766E', '#EA580C'],
            'moderation_filters' => ['adult', 'nudity', 'violence'],
            'logo_file' => UploadedFile::fake()->image('logo.png')->size(120),
            'remove_logo' => false,
            'branding' => [
                'primary_color' => '#E8A5A5',
                'accent_color' => '#8CB4E8',
                'welcome_message' => 'Welcome to our celebration!',
            ],
        ]);

    $response->assertRedirect(route('events.settings', $event));
    $response->assertSessionHas('success', 'Event settings saved.');

    $event->refresh();

    $branding = $event->branding;
    $logoPath = is_array($branding) ? ($branding['logo_path'] ?? null) : null;
    $albumBackgroundPath = is_array($branding) ? ($branding['album_background_path'] ?? null) : null;

    expect($event->type)->toBe('party')
        ->and($event->name)->toBe('Updated Event Name')
        ->and($event->event_date?->toDateString())->toBe('2026-06-20')
        ->and($event->status)->toBe(Event::STATUS_SCHEDULED)
        ->and($event->upload_window_starts_at?->toDateTimeString())->toBe('2026-06-20 00:00:00')
        ->and($event->upload_window_ends_at?->toDateTimeString())->toBe('2026-07-19 23:59:59')
        ->and($event->grace_ends_at?->toDateTimeString())->toBe('2026-07-26 23:59:59')
        ->and($event->payment_due_at?->toDateTimeString())->toBe('2026-06-20 00:00:00')
        ->and($event->album_public)->toBeFalse()
        ->and($event->moderation_enabled)->toBeFalse()
        ->and($event->auto_moderation_enabled)->toBeFalse()
        ->and(is_array($branding))->toBeTrue()
        ->and($branding['primary_color'] ?? null)->toBe('#E8A5A5')
        ->and($branding['accent_color'] ?? null)->toBe('#8CB4E8')
        ->and($branding['welcome_message'] ?? null)->toBe('Welcome to our celebration!')
        ->and($branding['logo_disk'] ?? null)->toBe('public')
        ->and($branding['display_language'] ?? null)->toBe('ro')
        ->and($branding['hide_side_images'] ?? null)->toBeTrue()
        ->and($branding['hide_qr_code'] ?? null)->toBeTrue()
        ->and($branding['hide_caption'] ?? null)->toBeTrue()
        ->and($branding['caption_theme'] ?? null)->toBe('light')
        ->and($branding['disable_guest_download'] ?? null)->toBeTrue()
        ->and($branding['welcome_screen_enabled'] ?? null)->toBeTrue()
        ->and($branding['welcome_screen_subtitle'] ?? null)->toBe('Please share your best memories.')
        ->and($branding['welcome_screen_collect_name'] ?? null)->toBeTrue()
        ->and($branding['welcome_screen_collect_email'] ?? null)->toBeTrue()
        ->and($branding['welcome_screen_collect_phone'] ?? null)->toBeFalse()
        ->and($branding['album_background_enabled'] ?? null)->toBeTrue()
        ->and($branding['album_background_mode'] ?? null)->toBe('image')
        ->and($branding['album_background_color'] ?? null)->toBe('#0F172A')
        ->and($branding['album_background_disk'] ?? null)->toBe('public')
        ->and($branding['text_posts_backgrounds_enabled'] ?? null)->toBeTrue()
        ->and($branding['text_posts_background_palette'] ?? null)->toBe(['#1D4ED8', '#0F766E', '#EA580C'])
        ->and($branding['moderation_filters'] ?? null)->toBe(['adult', 'nudity', 'violence'])
        ->and($branding['album_permission'] ?? null)->toBe('upload_only')
        ->and($branding['allowed_media_types'] ?? null)->toBe(['photo', 'video', 'text'])
        ->and(is_string($logoPath))->toBeTrue();

    expect($logoPath)->toStartWith("events/{$event->id}/branding/");
    Storage::disk('public')->assertExists((string) $logoPath);
    expect($albumBackgroundPath)->toStartWith("events/{$event->id}/branding/");
    Storage::disk('public')->assertExists((string) $albumBackgroundPath);

    CarbonImmutable::setTestNow();
});

it('rejects event dates too far in the future when updating settings', function () {
    CarbonImmutable::setTestNow('2026-03-10 12:00:00');

    $user = User::factory()->create();
    $event = Event::factory()->for($user)->create();

    $this->actingAs($user);

    $response = $this->from(route('events.settings', $event))
        ->patch(route('events.settings.update', $event), [
            'type' => 'wedding',
            'name' => 'My Event',
            'event_date' => '2030-12-31',
            'timezone' => 'Europe/Bucharest',
            'album_public' => true,
            'moderation_enabled' => true,
            'auto_moderation_enabled' => true,
            'remove_logo' => false,
            'branding' => [
                'primary_color' => '',
                'accent_color' => '',
                'welcome_message' => '',
            ],
        ]);

    $response->assertRedirect(route('events.settings', $event));
    $response->assertSessionHasErrors('event_date');

    CarbonImmutable::setTestNow();
});

it('forbids updating another users event settings', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($otherUser);

    $response = $this->patch(route('events.settings.update', $event), [
        'type' => 'wedding',
        'name' => 'My Event',
        'event_date' => '2026-05-14',
        'timezone' => 'Europe/Bucharest',
        'album_public' => true,
        'moderation_enabled' => true,
        'auto_moderation_enabled' => true,
        'remove_logo' => false,
        'branding' => [
            'primary_color' => '#E8A5A5',
            'accent_color' => '#8CB4E8',
            'welcome_message' => '',
        ],
    ]);

    $response->assertForbidden();
});

it('restores saved settings in the settings page after refresh', function () {
    CarbonImmutable::setTestNow('2026-03-10 12:00:00');
    Config::set('events.upload_disk', 'public');
    Storage::fake('public');

    $user = User::factory()->create();
    $event = Event::factory()->for($user)->create([
        'type' => 'wedding',
        'name' => 'Refresh Test Event',
        'event_date' => '2026-05-14',
        'timezone' => 'Europe/Bucharest',
        'album_public' => true,
        'moderation_enabled' => true,
        'auto_moderation_enabled' => true,
        'branding' => null,
    ]);

    $this->actingAs($user);

    $this->patch(route('events.settings.update', $event), [
        'type' => 'party',
        'name' => 'Refreshed Event',
        'event_date' => '2026-06-15',
        'timezone' => 'Europe/Bucharest',
        'album_public' => false,
        'album_permission' => 'upload_only',
        'moderation_enabled' => true,
        'auto_moderation_enabled' => true,
        'allowed_media_types' => ['photo', 'text'],
        'display_language' => 'en',
        'hide_side_images' => true,
        'hide_qr_code' => false,
        'hide_caption' => true,
        'disable_guest_download' => true,
        'welcome_screen_enabled' => true,
        'welcome_screen_subtitle' => 'Share your best moments with us.',
        'welcome_screen_collect_name' => true,
        'welcome_screen_collect_email' => true,
        'welcome_screen_collect_phone' => true,
        'album_background_enabled' => true,
        'album_background_mode' => 'image',
        'album_background_color' => '#112233',
        'album_background_file' => UploadedFile::fake()->image('bg.jpg')->size(200),
        'remove_album_background' => false,
        'text_posts_backgrounds_enabled' => true,
        'text_posts_background_palette' => ['#123456', '#654321'],
        'moderation_filters' => ['adult', 'violence'],
        'logo_file' => UploadedFile::fake()->image('logo.png')->size(120),
        'remove_logo' => false,
        'branding' => [
            'primary_color' => '#334455',
            'accent_color' => '#667788',
            'welcome_message' => 'Welcome to our event!',
        ],
    ])->assertRedirect();

    $this->get(route('events.settings', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Settings')
            ->where('currentEvent.name', 'Refreshed Event')
            ->where('currentEvent.billing.canManage', false)
            ->where('eventNavigation.0.title', 'Events')
            ->where('eventNavigation.1.title', 'Workspace')
            ->where('eventNavigation.2.title', 'Media')
            ->where('eventNavigation.3.title', 'Settings')
            ->where('eventLinks.billingUpdate', route('events.billing.update', $event))
            ->where('currentEvent.type', 'party')
            ->where('currentEvent.planFeatures.customizationTier', 'advanced')
            ->where('currentEvent.planFeatures.allowsModerationTools', true)
            ->where('currentEvent.branding.primaryColor', '#334455')
            ->where('currentEvent.branding.welcomeMessage', 'Welcome to our event!')
            ->where('currentEvent.settings.displayLanguage', 'en')
            ->where('currentEvent.settings.hideSideImages', true)
            ->where('currentEvent.settings.hideCaption', true)
            ->where('currentEvent.settings.disableGuestDownload', true)
            ->where('currentEvent.settings.welcomeScreenEnabled', true)
            ->where('currentEvent.settings.welcomeScreenSubtitle', 'Share your best moments with us.')
            ->where('currentEvent.settings.welcomeScreenCollectName', true)
            ->where('currentEvent.settings.welcomeScreenCollectEmail', true)
            ->where('currentEvent.settings.welcomeScreenCollectPhone', true)
            ->where('currentEvent.settings.albumBackgroundEnabled', true)
            ->where('currentEvent.settings.albumBackgroundMode', 'image')
            ->where('currentEvent.settings.albumBackgroundColor', '#112233')
            ->where('currentEvent.settings.textPostsBackgroundsEnabled', true)
            ->where('currentEvent.settings.textPostsBackgroundPalette', ['#123456', '#654321'])
            ->where('currentEvent.settings.albumPermission', 'upload_only')
            ->where('currentEvent.settings.allowedMediaTypes', ['photo', 'text'])
            ->where('currentEvent.settings.moderationFilters', ['adult', 'violence'])
            ->where('currentEvent.settings.albumBackgroundImageUrl', fn ($url) => is_string($url) && $url !== '')
        );

    CarbonImmutable::setTestNow();
});

it('accepts greek as an event display language', function () {
    $user = User::factory()->create();
    $event = Event::factory()->for($user)->create([
        'type' => 'wedding',
        'name' => 'Greek Locale Event',
        'timezone' => 'Europe/Bucharest',
    ]);

    $this->actingAs($user);

    $this->from(route('events.settings', $event))
        ->patch(route('events.settings.update', $event), [
            'type' => 'wedding',
            'name' => 'Greek Locale Event',
            'timezone' => 'Europe/Bucharest',
            'album_public' => true,
            'moderation_enabled' => true,
            'auto_moderation_enabled' => true,
            'display_language' => 'el',
            'remove_logo' => false,
            'branding' => [
                'primary_color' => '#E8A5A5',
                'accent_color' => '#8CB4E8',
                'welcome_message' => '',
            ],
        ])
        ->assertRedirect(route('events.settings', $event));

    $event->refresh();

    expect($event->branding['display_language'] ?? null)->toBe('el');
});
