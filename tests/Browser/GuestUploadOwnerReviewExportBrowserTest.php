<?php

use App\Models\Event;
use App\Models\EventAsset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

function seedGuestAlbumState(
    mixed $page,
    Event $event,
    string $guestName,
    string $guestToken,
): void {
    $shareToken = json_encode($event->share_token, JSON_THROW_ON_ERROR);
    $guestNameValue = json_encode($guestName, JSON_THROW_ON_ERROR);
    $guestTokenValue = json_encode($guestToken, JSON_THROW_ON_ERROR);

    $page->script(<<<JS
() => {
    const appElement = document.getElementById('app');

    if (!appElement?.dataset.page) {
        return;
    }

    const pagePayload = JSON.parse(appElement.dataset.page);
    const props = pagePayload.props ?? {};
    const welcomeScreen = props.welcomeScreen ?? {};
    const appearance = props.appearance ?? {};
    const customWelcomeEnabled = Boolean(welcomeScreen.enabled);
    const eventName =
        typeof props.eventName === 'string' && props.eventName.trim().length > 0
            ? props.eventName.trim()
            : 'Event';
    const welcomeTitle =
        customWelcomeEnabled &&
        typeof welcomeScreen.title === 'string' &&
        welcomeScreen.title.trim().length > 0
            ? welcomeScreen.title.trim()
            : eventName;
    const welcomeSubtitle =
        customWelcomeEnabled &&
        typeof welcomeScreen.subtitle === 'string' &&
        welcomeScreen.subtitle.trim().length > 0
            ? welcomeScreen.subtitle.trim()
            : 'Share your photos & videos with us!';
    const welcomeButtonText =
        customWelcomeEnabled &&
        typeof welcomeScreen.buttonText === 'string' &&
        welcomeScreen.buttonText.trim().length > 0
            ? welcomeScreen.buttonText.trim()
            : 'Continue';
    const defaultField = {
        id: 'name',
        type: 'text',
        label: 'Name',
        help_text: 'Write your name',
        required: true,
        enabled: true,
    };
    let fieldsSource = Array.isArray(welcomeScreen.fields)
        ? welcomeScreen.fields.filter((field) => field?.enabled !== false)
        : [];

    if (fieldsSource.length === 0) {
        fieldsSource = [defaultField];

        if (Boolean(welcomeScreen.collectEmail)) {
            fieldsSource.push({
                id: 'email',
                type: 'email',
                label: 'Email',
                help_text: 'Write your email',
                required: false,
                enabled: true,
            });
        }

        if (Boolean(welcomeScreen.collectPhone)) {
            fieldsSource.push({
                id: 'phone',
                type: 'phone',
                label: 'Phone',
                help_text: 'Write your phone',
                required: false,
                enabled: true,
            });
        }
    }

    const enabledFields = fieldsSource
        .map((field) => ({
            id: typeof field.id === 'string' ? field.id : '',
            type: typeof field.type === 'string' ? field.type : 'text',
            label: typeof field.label === 'string' ? field.label : 'Field',
            help_text: typeof field.help_text === 'string' ? field.help_text : '',
            required: Boolean(field.required),
            enabled: field.enabled !== false,
        }))
        .filter((field) => field.enabled);

    const welcomeFingerprint = JSON.stringify({
        enabled: customWelcomeEnabled,
        title: welcomeTitle,
        subtitle: welcomeSubtitle,
        buttonText: welcomeButtonText,
        font: typeof welcomeScreen.font === 'string' ? welcomeScreen.font : 'montserrat',
        animated: Boolean(welcomeScreen.animated),
        logoUrl: welcomeScreen.logoUrl ?? appearance.logoUrl ?? null,
        backgroundUrl: welcomeScreen.backgroundUrl ?? null,
        fields: enabledFields.map((field) => ({
            id: field.id,
            type: field.type,
            label: field.label,
            help_text: field.help_text,
            required: field.required,
            enabled: field.enabled,
        })),
    });

    window.localStorage.setItem(
        'kululu-guest-profile:' + {$shareToken},
        JSON.stringify({
            fields: {
                name: {$guestNameValue},
            },
            intent: 'upload_media',
            welcomeFingerprint,
            guestToken: {$guestTokenValue},
        }),
    );
}
JS);
}

beforeEach(function (): void {
    File::deleteDirectory(public_path('browser-test-storage'));
    File::ensureDirectoryExists(public_path('browser-test-storage'));

    Config::set('filesystems.default', 'browser_public');
    Config::set('filesystems.disks.browser_public', [
        'driver' => 'local',
        'root' => public_path('browser-test-storage'),
        'url' => '/browser-test-storage',
        'visibility' => 'public',
        'throw' => false,
        'report' => false,
    ]);
    Config::set('events.upload_disk', 'browser_public');
});

afterEach(function (): void {
    File::deleteDirectory(public_path('browser-test-storage'));
});

it('guides guests into the upload composer in the browser', function (): void {
    $event = Event::factory()->create([
        'name' => 'Browser Guest Upload Event',
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
    ]);

    $page = visit(route('events.album', $event->share_token, false));

    $page->assertSee('Digital Album')
        ->fill('guest-name', 'Elena')
        ->click('@guest-onboarding-next')
        ->click('@guest-intent-upload_media')
        ->assertSee('What would you like to do first?')
        ->assertNoJavaScriptErrors();

    seedGuestAlbumState($page, $event, 'Elena', 'browser-guest-token');

    $storedGuestProfile = $page->script(
        sprintf(
            "() => window.localStorage.getItem('kululu-guest-profile:%s')",
            $event->share_token,
        ),
    );

    expect($storedGuestProfile)->toBeString()->not->toBe('');

    $page->refresh()
        ->click('button[aria-label="Camera"]')
        ->waitForText('Choose photos or videos')
        ->assertNoJavaScriptErrors();
});

it('lets owners review uploaded media and prepare an export in the browser', function (): void {
    if (! class_exists(ZipArchive::class)) {
        test()->markTestSkipped('ZipArchive is not installed.');
    }

    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Browser Review Event',
        'is_paid' => true,
        'paid_at' => now()->subDay(),
        'retention_ends_at' => now()->addDays(30),
        'moderation_enabled' => true,
        'auto_moderation_enabled' => false,
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
    ]);

    $albumPage = visit(route('events.album', $event->share_token, false));

    $albumPage->fill('guest-name', 'Elena')
        ->click('@guest-onboarding-next')
        ->click('@guest-intent-upload_media')
        ->assertSee('What would you like to do first?')
        ->assertNoJavaScriptErrors();

    $guestToken = 'browser-owner-review-token';

    seedGuestAlbumState($albumPage, $event, 'Elena', $guestToken);

    $storedGuestProfile = $albumPage->script(
        sprintf(
            "() => window.localStorage.getItem('kululu-guest-profile:%s')",
            $event->share_token,
        ),
    );

    expect($storedGuestProfile)->toBeString()->not->toBe('');

    $albumPage->refresh()
        ->click('button[aria-label="Camera"]')
        ->waitForText('Choose photos or videos')
        ->assertNoJavaScriptErrors();

    $this->post(route('events.album.upload', $event->share_token), [
        'files' => [UploadedFile::fake()->image('browser-smoke-photo.jpg')->size(300)],
        'guest_name' => 'Elena',
        'message' => 'Ready for review.',
        'guest_token' => $guestToken,
        'guest_intent' => 'upload_media',
    ])->assertRedirect();

    /** @var EventAsset $asset */
    $asset = $event->assets()->latest('id')->firstOrFail();

    expect($asset->moderation_status)->toBe('processing');

    $loginPage = visit(route('login', [], false));

    $loginPage->fill('email', $owner->email)
        ->fill('password', 'password')
        ->click('@login-button')
        ->assertRoute('events.show', ['event' => $event->getRouteKey()])
        ->assertNoJavaScriptErrors();

    $mediaPage = visit(route('events.media', $event, false));

    $mediaPage->waitForText('Elena')
        ->assertNoJavaScriptErrors();

    $this->actingAs($owner)
        ->patchJson(route('events.assets.moderation.update', [$event, $asset]), [
            'moderation_status' => 'approved',
        ])
        ->assertRedirect();

    $asset->refresh();

    expect($asset->moderation_status)->toBe('approved')
        ->and($asset->reviewed_at)->not->toBeNull();

    $eventPage = visit(route('events.show', $event, false));

    $eventPage->click('@export-album-button')
        ->assertDontSee('Billing still needs attention for this event.')
        ->waitForText('Ready since')
        ->assertNoJavaScriptErrors();

    $event->refresh();

    expect($event->media_export_status)->toBe('ready')
        ->and($event->media_export_path)->not->toBeNull();

    $this->actingAs($owner)
        ->get(route('events.exports.media.download', $event))
        ->assertOk()
        ->assertDownload();
});
