<?php

use App\Models\Event;
use App\Models\EventAsset;
use App\Models\EventCollaborator;
use App\Models\EventGuest;
use App\Models\Plan;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('super admins land on the regular dashboard', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    Event::factory()->for($admin)->create([
        'name' => 'Admin Event',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));

    expect($admin->fresh()->account_type)->toBe(User::ACCOUNT_TYPE_SUPER_ADMIN);
});

test('authenticated users without owned or collaborator events are redirected into onboarding', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('onboarding.create'));
});

test('collaborator-only users can still enter the dashboard flow without owner onboarding', function () {
    $owner = User::factory()->create();
    $collaborator = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Shared Event',
        'onboarding_completed_at' => now(),
    ]);

    EventCollaborator::query()->create([
        'event_id' => $event->id,
        'email' => $collaborator->email,
        'user_id' => $collaborator->id,
        'role' => 'viewer',
        'status' => 'active',
        'invited_by_user_id' => $owner->id,
        'invited_at' => now(),
        'accepted_at' => now(),
    ]);

    $this->actingAs($collaborator)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('summary.collaboratorEventCount', 1)
        );
});

test('multi-event user accounts stay on the main dashboard without business tools', function () {
    $owner = User::factory()->create();

    $liveEvent = Event::factory()->for($owner)->create([
        'name' => 'Brand Launch',
        'status' => Event::STATUS_LIVE,
        'is_paid' => true,
        'storage_limit_bytes' => 10737418240,
        'storage_used_bytes' => 3221225472,
    ]);
    $scheduledUnpaidEvent = Event::factory()->for($owner)->create([
        'name' => 'Retail Tour',
        'status' => Event::STATUS_SCHEDULED,
        'is_paid' => false,
        'payment_due_at' => now()->addDay(),
        'storage_limit_bytes' => 5368709120,
        'storage_used_bytes' => 2147483648,
    ]);
    $lockedEvent = Event::factory()->for($owner)->create([
        'name' => 'Locked Expo',
        'status' => Event::STATUS_LOCKED,
        'is_paid' => false,
        'payment_due_at' => now()->subDay(),
        'storage_limit_bytes' => 4294967296,
        'storage_used_bytes' => 1073741824,
    ]);

    EventAsset::factory()->count(2)->for($liveEvent)->for($owner)->create();
    EventAsset::factory()->for($scheduledUnpaidEvent)->for($owner)->create([
        'moderation_status' => 'processing',
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.user.accountType', User::ACCOUNT_TYPE_USER)
            ->where('summary.ownedEventCount', 3)
        );
});

test('the removed account dashboard endpoint is not available to business users', function () {
    $owner = User::factory()->business()->create();

    $this->actingAs($owner)
        ->get('/dashboard/account')
        ->assertNotFound();
});

test('business accounts land on the regular dashboard from the main dashboard route', function () {
    $owner = User::factory()->business()->create();

    Event::factory()->for($owner)->create([
        'name' => 'Business Main Event',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));
});

test('normal owned-event users see the dashboard on the dashboard route', function () {
    $owner = User::factory()->create();
    Event::factory()->for($owner)->create([
        'name' => 'Spring Wedding',
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));
});

test('single-event owners in onboarding still see the dashboard on the dashboard route', function () {
    $owner = User::factory()->create();
    Event::factory()->for($owner)->create([
        'name' => 'Spring Wedding',
        'onboarding_step' => 'photos',
        'onboarding_completed_at' => null,
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));
});

test('event workspace exposes guest upload settings summary for owners', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Spring Wedding',
        'branding' => [
            'allowed_media_types' => ['photo', 'text'],
            'allowed_media_types_version' => 2,
        ],
    ]);

    $this->actingAs($owner)
        ->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Home')
            ->where('currentEvent.allowedMediaTypes', ['photo', 'text'])
            ->where('eventLinks.printPack', route('events.print-pack', $event))
        );
});

test('authenticated dashboard pages share localized host workspace strings', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Spring Wedding',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($owner)
        ->withUnencryptedCookies(['site_locale' => 'ro'])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('locale.current', 'ro')
            ->where('translations.app.language.label', 'Limba')
            ->where('translations.dashboard.hero.title', 'Continua de unde ai ramas cu evenimentul')
            ->where('translations.dashboard.sections.activity.title', 'Activitate recenta')
        );

    $this->actingAs($owner)
        ->withUnencryptedCookies(['site_locale' => 'ro'])
        ->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Home')
            ->where('locale.current', 'ro')
            ->where('translations.event_home.hero.kicker', 'Workspace')
            ->where('translations.event_home.section.share_title', 'Linkuri de distribuit')
            ->where('translations.event_home.media_types.text', 'Mesaje text')
            ->where('translations.event_home.print_pack.title', 'Pachet QR pentru print')
            ->where('translations.event_home.print_pack.open_studio', 'Deschide Studio QR')
            ->where('eventLinks.printPack', route('events.print-pack', $event))
        );
});

test('business owners return from an event workspace to the main dashboard', function () {
    $owner = User::factory()->business()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Studio Launch',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Home')
            ->where('eventLinks.accountDashboard', route('dashboard'))
            ->where('backNavigation.href', route('dashboard'))
        );
});

test('account dashboard shows owned event summaries, onboarding continuation, and recent activity', function () {
    $owner = User::factory()->create();
    $liveEvent = Event::factory()->for($owner)->create([
        'name' => 'Spring Wedding',
        'status' => Event::STATUS_LIVE,
        'is_paid' => false,
        'payment_due_at' => now()->addDays(2),
        'media_export_status' => 'ready',
        'upload_count' => 18,
        'upload_limit' => 300,
        'storage_used_bytes' => 5242880,
        'storage_limit_bytes' => 10485760,
    ]);
    $setupEvent = Event::factory()->for($owner)->create([
        'name' => 'Rooftop Rehearsal',
        'status' => Event::STATUS_DRAFT,
        'onboarding_step' => 'photos',
        'onboarding_completed_at' => null,
    ]);

    EventGuest::factory()->count(2)->for($liveEvent)->create();

    EventAsset::factory()->for($liveEvent)->for($owner)->create([
        'kind' => 'photo',
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Elena',
            'message' => 'Ceremony entrance',
        ],
        'created_at' => now()->subMinutes(5),
    ]);

    EventAsset::factory()->for($liveEvent)->for($owner)->create([
        'kind' => 'text',
        'moderation_status' => 'processing',
        'metadata' => [
            'guest_name' => 'Matei',
            'text' => 'Dance floor is already full.',
        ],
        'created_at' => now()->subMinute(),
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('summary.ownedEventCount', 2)
            ->where('summary.collaboratorEventCount', 0)
            ->where('summary.pendingSetupCount', 1)
            ->where('summary.totalUploadCount', 2)
            ->where('summary.pendingModerationCount', 1)
            ->where('summary.readyExportCount', 1)
            ->where('dashboardLinks.ownedEvents', route('dashboard').'#events')
            ->where('dashboardLinks.recentActivity', route('dashboard').'#activity')
            ->where('continueSetupEvent.name', 'Rooftop Rehearsal')
            ->where('continueSetupEvent.primaryAction.url', route('onboarding.photos', $setupEvent))
            ->where(
                'ownedEvents',
                fn ($events): bool => collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Spring Wedding'
                        && $event['planDetails']['slug'] !== null
                        && $event['roleLabel'] === 'Owner'
                        && $event['mediaExportLabel'] === 'Export ready'
                        && $event['assetCount'] === 2
                        && $event['guestCount'] === 2,
                ) && collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Rooftop Rehearsal'
                        && $event['statusLabel'] === 'Setup in progress'
                        && $event['primaryAction']['url'] === route('onboarding.photos', $setupEvent),
                ),
            )
            ->where(
                'recentActivity',
                fn ($activity): bool => count($activity) === 2
                    && $activity[0]['eventName'] === 'Spring Wedding'
                    && $activity[0]['activityUrl'] === route('events.media', ['event' => $liveEvent->id, 'asset' => $liveEvent->assets()->latest('id')->value('id')])
                    && $activity[0]['guestName'] === 'Matei'
                    && $activity[0]['moderationStatus'] === 'processing',
            )
        );
});

test('single collaborator event users land on the account dashboard from the dashboard landing page', function () {
    $owner = User::factory()->create();
    $collaboratorUser = User::factory()->create([
        'email' => 'collab@example.com',
    ]);
    $sharedEvent = Event::factory()->for($owner)->create([
        'name' => 'Shared Gala',
        'media_export_status' => 'processing',
    ]);

    EventCollaborator::query()->create([
        'event_id' => $sharedEvent->id,
        'email' => 'collab@example.com',
        'user_id' => $collaboratorUser->id,
        'role' => 'manager',
        'status' => 'active',
        'invited_by_user_id' => $owner->id,
        'invited_at' => now(),
        'accepted_at' => now(),
    ]);

    EventAsset::factory()->for($sharedEvent)->for($owner)->create([
        'kind' => 'photo',
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Daria',
            'message' => 'Welcome drinks',
        ],
        'created_at' => now()->subMinute(),
    ]);

    $this->actingAs($collaboratorUser)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('summary.collaboratorEventCount', 1)
        );
});

test('account dashboard exposes free plus and pro plan tiers for normal users', function () {
    $owner = User::factory()->create();

    $freePlan = Plan::factory()->create([
        'name' => 'Free',
        'slug' => 'free',
        'price_cents' => 0,
        'storage_limit_bytes' => 3221225472,
        'upload_limit' => 100,
        'upload_window_days' => 1,
        'download_all_enabled' => false,
        'moderation_tools_enabled' => false,
        'remove_app_branding' => false,
        'is_default' => true,
    ]);

    $plusPlan = Plan::factory()->create([
        'name' => 'Plus',
        'slug' => 'plus',
        'price_cents' => 4900,
        'storage_limit_bytes' => 12884901888,
        'upload_limit' => 500,
        'upload_window_days' => 30,
        'download_all_enabled' => true,
        'moderation_tools_enabled' => false,
        'remove_app_branding' => false,
        'is_default' => false,
    ]);

    $proPlan = Plan::factory()->create([
        'name' => 'Pro',
        'slug' => 'pro',
        'price_cents' => 9900,
        'storage_limit_bytes' => 32212254720,
        'upload_limit' => 1000000,
        'upload_window_days' => 365,
        'download_all_enabled' => true,
        'moderation_tools_enabled' => true,
        'remove_app_branding' => true,
        'is_default' => false,
    ]);

    Event::factory()->for($owner)->for($freePlan)->create([
        'name' => 'Free Event',
        'is_paid' => true,
        'download_all_enabled' => false,
        'moderation_tools_enabled' => false,
        'remove_app_branding' => false,
        'storage_limit_bytes' => $freePlan->storage_limit_bytes,
        'upload_limit' => $freePlan->upload_limit,
        'upload_window_days' => $freePlan->upload_window_days,
        'onboarding_completed_at' => now(),
    ]);

    $plusEvent = Event::factory()->for($owner)->for($plusPlan)->create([
        'name' => 'Plus Event',
        'is_paid' => false,
        'payment_due_at' => now()->addDay(),
        'download_all_enabled' => true,
        'moderation_tools_enabled' => false,
        'remove_app_branding' => false,
        'storage_limit_bytes' => $plusPlan->storage_limit_bytes,
        'upload_limit' => $plusPlan->upload_limit,
        'upload_window_days' => $plusPlan->upload_window_days,
        'onboarding_completed_at' => now(),
    ]);

    Event::factory()->for($owner)->for($proPlan)->create([
        'name' => 'Pro Event',
        'is_paid' => true,
        'download_all_enabled' => true,
        'moderation_tools_enabled' => true,
        'remove_app_branding' => true,
        'storage_limit_bytes' => $proPlan->storage_limit_bytes,
        'upload_limit' => $proPlan->upload_limit,
        'upload_window_days' => $proPlan->upload_window_days,
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('dashboardLinks.overview', route('dashboard'))
            ->where(
                'ownedEvents',
                fn ($events): bool => collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Free Event'
                        && $event['planDetails']['slug'] === 'free'
                        && $event['planDetails']['priceCents'] === 0
                        && $event['planDetails']['storageLimitBytes'] === 3221225472
                        && $event['planDetails']['uploadLimit'] === 100
                        && $event['planDetails']['downloadAllEnabled'] === false
                        && $event['planDetails']['moderationToolsEnabled'] === false
                        && $event['planDetails']['removeAppBranding'] === false,
                ) && collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Plus Event'
                        && $event['planDetails']['slug'] === 'plus'
                        && $event['planDetails']['priceCents'] === 4900
                        && $event['planDetails']['storageLimitBytes'] === 12884901888
                        && $event['planDetails']['uploadLimit'] === 500
                        && $event['planDetails']['downloadAllEnabled'] === true
                        && $event['planDetails']['moderationToolsEnabled'] === false
                        && $event['links']['billing'] === route('events.settings', ['event' => $plusEvent, 'tab' => 'billing']),
                ) && collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Pro Event'
                        && $event['planDetails']['slug'] === 'pro'
                        && $event['planDetails']['priceCents'] === 9900
                        && $event['planDetails']['storageLimitBytes'] === 32212254720
                        && $event['planDetails']['uploadLimit'] === 1000000
                        && $event['planDetails']['moderationToolsEnabled'] === true
                        && $event['planDetails']['removeAppBranding'] === true,
                ),
            )
        );
});

test('the removed account dashboard endpoint is not available to super admins', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get('/dashboard/account')
        ->assertNotFound();
});

test('owned events page renders all owned event workspaces', function () {
    $owner = User::factory()->create();
    Event::factory()->count(2)->for($owner)->create();

    $this->actingAs($owner)
        ->get(route('dashboard.events'))
        ->assertRedirect(route('dashboard').'#events');
});

test('business accounts open the account events page from the events shortcut', function () {
    $owner = User::factory()->business()->create();
    Event::factory()->count(2)->for($owner)->create();

    $this->actingAs($owner)
        ->get(route('dashboard.events'))
        ->assertRedirect(route('dashboard').'#events');
});

test('recent activity page links directly to the matching asset in media', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Activity Event',
    ]);

    $asset = EventAsset::factory()->for($event)->for($owner)->create([
        'kind' => 'photo',
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Lia',
            'message' => 'After party smiles',
        ],
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard.activity'))
        ->assertRedirect(route('dashboard').'#activity');
});

test('business accounts open the business activity section from the activity shortcut', function () {
    $owner = User::factory()->business()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Activity Event',
    ]);

    EventAsset::factory()->for($event)->for($owner)->create([
        'kind' => 'photo',
        'moderation_status' => 'approved',
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard.activity'))
        ->assertRedirect(route('dashboard').'#activity');
});

test('media page accepts a deep-linked asset from dashboard activity', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create();
    $asset = EventAsset::factory()->for($event)->for($owner)->create();

    $this->actingAs($owner)
        ->get(route('events.media', ['event' => $event->id, 'asset' => $asset->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Media')
            ->where('initialActiveAssetId', $asset->id)
        );
});
