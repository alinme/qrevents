<?php

use App\Jobs\GenerateEventMediaExport;
use App\Models\Event;
use App\Models\EventAsset;
use App\Models\EventCollaborator;
use App\Models\EventGuest;
use App\Models\ExchangeRate;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('super admins are redirected from the dashboard landing page to admin overview', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.overview'));

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
        ->assertRedirect(route('dashboard.account'));
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
        ->assertRedirect(route('dashboard.account'));

    $this->actingAs($owner)
        ->get(route('dashboard.account'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.user.accountType', User::ACCOUNT_TYPE_USER)
            ->where('dashboardLinks.business', null)
            ->where('summary.ownedEventCount', 3)
        );
});

test('owned event accounts can still open the account dashboard explicitly', function () {
    $owner = User::factory()->business()->create();

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
        ->get(route('dashboard.account'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.user.accountType', User::ACCOUNT_TYPE_BUSINESS)
            ->where('auth.user.capabilities.accessBusinessDashboard', true)
            ->where('dashboardLinks.overview', route('dashboard.account'))
            ->where('dashboardLinks.business', route('dashboard.business'))
            ->where('businessOverview.hasOwnedEvents', true)
            ->where('businessOverview.activeEventCount', 2)
            ->where('businessOverview.liveEventCount', 1)
            ->where('businessOverview.unpaidEventCount', 2)
            ->where('businessOverview.overdueEventCount', 1)
            ->where('businessOverview.readyExportCount', 0)
            ->where('businessOverview.totalAllocatedStorageBytes', 20401094656)
            ->where('businessOverview.totalUsedStorageBytes', 6442450944)
            ->where('businessOverview.totalFreeStorageBytes', 13958643712)
            ->where('businessOverview.storageUsagePercent', 32)
            ->where(
                'businessAttentionEvents',
                fn ($events): bool => collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Locked Expo'
                        && $event['attentionLabel'] === 'Resolve billing'
                        && $event['billingLabel'] === 'Payment overdue',
                ) && collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Retail Tour'
                        && $event['attentionLabel'] === 'Review invoice'
                        && $event['billingLabel'] === 'Payment due soon',
                ),
            )
        );
});

test('business accounts land on the business home from the main dashboard route', function () {
    $owner = User::factory()->business()->create();

    Event::factory()->for($owner)->create([
        'name' => 'Business Main Event',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertRedirect(route('dashboard.business'));
});

test('normal owned-event users are sent to the account dashboard from the dashboard landing page', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Spring Wedding',
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertRedirect(route('dashboard.account'));
});

test('single-event owners in onboarding are sent to the account dashboard from the dashboard landing page', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Spring Wedding',
        'onboarding_step' => 'photos',
        'onboarding_completed_at' => null,
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertRedirect(route('dashboard.account'));
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
        );
});

test('business owners return from an event workspace to the business events dashboard', function () {
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
            ->where('eventLinks.accountDashboard', route('dashboard.business.events.index'))
            ->where('backNavigation.href', route('dashboard.business.events.index'))
        );
});

test('normal users cannot access the dedicated business dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.business'))
        ->assertForbidden();
});

test('single-event owners still cannot access the dedicated business dashboard', function () {
    $owner = User::factory()->create();
    Event::factory()->for($owner)->create([
        'name' => 'Only Event',
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard.business'))
        ->assertForbidden();
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
        ->get(route('dashboard.account'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('summary.ownedEventCount', 2)
            ->where('summary.collaboratorEventCount', 0)
            ->where('summary.pendingSetupCount', 1)
            ->where('summary.totalUploadCount', 2)
            ->where('summary.pendingModerationCount', 1)
            ->where('summary.readyExportCount', 1)
            ->where('dashboardLinks.ownedEvents', route('dashboard.account').'#events')
            ->where('dashboardLinks.recentActivity', route('dashboard.account').'#activity')
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
        ->assertRedirect(route('dashboard.account'));
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
        ->get(route('dashboard.account'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('dashboardLinks.overview', route('dashboard.account'))
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

test('super admins can still open their account dashboard explicitly', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    $event = Event::factory()->for($admin)->create([
        'name' => 'Admin Hosted Event',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard.account'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.user.accountType', User::ACCOUNT_TYPE_SUPER_ADMIN)
            ->where('auth.user.capabilities.accessAdmin', true)
            ->where('auth.user.capabilities.accessBusinessDashboard', true)
            ->where('dashboardLinks.overview', route('dashboard.account'))
            ->where('ownedEvents.0.name', 'Admin Hosted Event')
            ->where('ownedEvents.0.links.dashboard', route('events.show', $event))
        );
});

test('business accounts can open the dedicated business dashboard', function () {
    $owner = User::factory()->business()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Business Studio Launch',
        'status' => Event::STATUS_LIVE,
        'is_paid' => false,
        'payment_due_at' => now()->subDay(),
        'storage_limit_bytes' => 10737418240,
        'storage_used_bytes' => 2147483648,
        'media_export_status' => 'ready',
    ]);

    EventAsset::factory()->for($event)->for($owner)->create([
        'moderation_status' => 'processing',
    ]);
    Event::factory()->for($owner)->create([
        'name' => 'Second Portfolio Event',
        'status' => Event::STATUS_SCHEDULED,
        'is_paid' => true,
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard.business'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard/BusinessOverview')
            ->where('sidebarLabel', 'Business')
            ->where('dashboardLinks.overview', route('dashboard.account'))
            ->where('dashboardLinks.business', route('dashboard.business'))
            ->where('businessNavigation.0.title', 'Business')
            ->where('businessNavigation.1.title', 'Billing')
            ->where('businessNavigation.2.title', 'Events')
            ->where('businessNavigation.2.href', route('dashboard.business.events.index'))
            ->where('businessOverview.hasOwnedEvents', true)
            ->where('businessOverview.overdueEventCount', 1)
            ->where('businessOverview.readyExportCount', 1)
            ->where('businessAttentionEvents.0.name', 'Business Studio Launch')
            ->where(
                'ownedEvents',
                fn ($events): bool => collect($events)->contains(
                    fn (array $event): bool => $event['name'] === 'Business Studio Launch',
                ),
            )
        );
});

test('business accounts can open a dedicated wallet history page', function () {
    ExchangeRate::query()->create([
        'base_currency' => 'EUR',
        'quote_currency' => 'RON',
        'rate' => 4.95,
        'fetched_at' => now(),
    ]);
    ExchangeRate::query()->create([
        'base_currency' => 'EUR',
        'quote_currency' => 'GBP',
        'rate' => 0.86,
        'fetched_at' => now(),
    ]);

    $owner = User::factory()->business()->create([
        'business_wallet_credits' => 80,
        'business_wallet_currency' => 'EUR',
    ]);
    $event = Event::factory()->for($owner)->create([
        'name' => 'Wallet Launch Event',
    ]);

    $owner->businessWalletTransactions()->create([
        'kind' => 'top_up',
        'credits' => 100,
        'description' => 'Business wallet top-up',
        'metadata' => [
            'checkout_currency' => 'EUR',
            'localized_amount_cents' => 10000,
        ],
    ]);
    $owner->businessWalletTransactions()->create([
        'kind' => 'bonus',
        'credits' => 25,
        'description' => 'Business top-up bonus credits',
    ]);
    $owner->businessWalletTransactions()->create([
        'event_id' => $event->id,
        'kind' => 'event_debit',
        'credits' => -45,
        'description' => 'Created Plus event: Wallet Launch Event',
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard.business.wallet.history'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard/BusinessWalletHistory')
            ->where('sidebarLabel', 'Business')
            ->where('businessNavigation.0.title', 'Business')
            ->where('businessNavigation.1.title', 'Billing')
            ->where('businessNavigation.2.title', 'Events')
            ->where('businessNavigation.2.href', route('dashboard.business.events.index'))
            ->where('dashboardLinks.business', route('dashboard.business'))
            ->where('businessTopUp.submitUrl', route('dashboard.business.wallet.checkout'))
            ->where('businessTopUp.defaultCredits', 100)
            ->where('businessTopUp.defaultCurrency', 'EUR')
            ->where('businessTopUp.supportedCheckoutCurrencies', ['EUR', 'RON', 'GBP'])
            ->where('businessTopUp.packs.2.credits', 100)
            ->where('businessTopUp.packs.2.total_credits', 125)
            ->where('businessTopUp.packs.2.priceLabels.RON', 'RON 495.00')
            ->where('walletSummary.currentBalance', 80)
            ->where('walletSummary.currency', 'EUR')
            ->where('walletSummary.totalTransactions', 3)
            ->where('walletSummary.creditsAdded', 125)
            ->where('walletSummary.creditsUsed', 45)
            ->where('walletTransactionsPagination.total', 3)
            ->where('walletTransactions.0.kind', 'event_debit')
            ->where('walletTransactions.0.credits', -45)
            ->where('walletTransactions.0.eventName', 'Wallet Launch Event')
            ->where('walletTransactions.0.eventUrl', route('events.show', $event))
            ->where('walletTransactions.2.moneyLabel', 'EUR 100.00')
        );
});

test('business dashboard filters owned workspaces and attention items by query parameters', function () {
    $owner = User::factory()->business()->create();

    $overdueEvent = Event::factory()->for($owner)->create([
        'name' => 'Overdue Summit',
        'status' => Event::STATUS_LIVE,
        'is_paid' => false,
        'payment_due_at' => now()->subDay(),
        'media_export_status' => 'failed',
    ]);
    Event::factory()->for($owner)->create([
        'name' => 'Ready Expo',
        'status' => Event::STATUS_SCHEDULED,
        'is_paid' => true,
        'payment_due_at' => now()->addDays(5),
        'media_export_status' => 'ready',
    ]);
    Event::factory()->for($owner)->create([
        'name' => 'Unpaid Retreat',
        'status' => Event::STATUS_DRAFT,
        'is_paid' => false,
        'payment_due_at' => now()->addDays(2),
        'media_export_status' => 'idle',
    ]);

    EventAsset::factory()->for($overdueEvent)->for($owner)->create([
        'moderation_status' => 'processing',
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard.business', [
            'status' => 'overdue',
            'search' => 'Overdue',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard/BusinessOverview')
            ->where('filters.status', 'overdue')
            ->where('filters.search', 'Overdue')
            ->where('filters.hasActiveFilters', true)
            ->where('filters.ownedEventCount', 1)
            ->where('filters.attentionCount', 1)
            ->where('businessAttentionSummary.visibleCount', 1)
            ->where('ownedEventsPagination.total', 1)
            ->where('ownedEvents.0.name', 'Overdue Summit')
            ->where('ownedEvents.0.links.billing', route('events.settings', ['event' => $overdueEvent, 'tab' => 'billing']))
            ->where('businessAttentionEvents.0.name', 'Overdue Summit')
            ->where('businessAttentionEvents.0.links.billing', route('events.settings', ['event' => $overdueEvent, 'tab' => 'billing']))
        );
});

test('business dashboard paginates owned workspaces', function () {
    $owner = User::factory()->business()->create();

    foreach (range(1, 7) as $index) {
        Event::factory()->for($owner)->create([
            'name' => "Portfolio Event {$index}",
            'event_date' => now()->addDays($index),
        ]);
    }

    $this->actingAs($owner)
        ->get(route('dashboard.business', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard/BusinessOverview')
            ->where('ownedEventsPagination.currentPage', 2)
            ->where('ownedEventsPagination.lastPage', 2)
            ->where('ownedEventsPagination.total', 7)
            ->has('ownedEvents', 1)
            ->where('ownedEvents.0.name', 'Portfolio Event 1')
        );
});

test('business dashboard pagination preserves selected workspace ids', function () {
    $owner = User::factory()->business()->create();

    $selectedEvent = null;

    foreach (range(1, 7) as $index) {
        $event = Event::factory()->for($owner)->create([
            'name' => "Selection Event {$index}",
            'event_date' => now()->addDays($index),
        ]);

        if ($index === 7) {
            $selectedEvent = $event;
        }
    }

    expect($selectedEvent)->not->toBeNull();

    $this->actingAs($owner)
        ->get(route('dashboard.business', [
            'page' => 1,
            'event_ids' => [$selectedEvent->id],
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard/BusinessOverview')
            ->where(
                'ownedEventsPagination.nextPageUrl',
                fn (?string $url): bool => is_string($url)
                    && str_contains($url, 'page=2')
                    && str_contains($url, 'event_ids'),
            )
        );
});

test('business dashboard pagination preserves all-filtered selection scope', function () {
    $owner = User::factory()->business()->create();

    foreach (range(1, 7) as $index) {
        Event::factory()->for($owner)->create([
            'name' => "Filtered Selection Event {$index}",
            'event_date' => now()->addDays($index),
        ]);
    }

    $this->actingAs($owner)
        ->get(route('dashboard.business', [
            'page' => 1,
            'status' => 'all',
            'selection_scope' => 'all_filtered',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard/BusinessOverview')
            ->where('filters.selectionScope', 'all_filtered')
            ->where(
                'ownedEventsPagination.nextPageUrl',
                fn (?string $url): bool => is_string($url)
                    && str_contains($url, 'page=2')
                    && str_contains($url, 'selection_scope=all_filtered'),
            )
        );
});

test('business dashboard can start exports for the current filtered set', function () {
    Queue::fake();

    $owner = User::factory()->business()->create();

    $exportableEvent = Event::factory()->for($owner)->create([
        'name' => 'Export Queue One',
        'media_export_status' => null,
    ]);
    $alreadyRunningEvent = Event::factory()->for($owner)->create([
        'name' => 'Export Queue Two',
        'media_export_status' => 'processing',
    ]);
    $noApprovedMediaEvent = Event::factory()->for($owner)->create([
        'name' => 'Export Queue Three',
        'media_export_status' => null,
    ]);

    EventAsset::factory()->for($exportableEvent)->for($owner)->create([
        'moderation_status' => 'approved',
    ]);
    EventAsset::factory()->for($alreadyRunningEvent)->for($owner)->create([
        'moderation_status' => 'approved',
    ]);
    EventAsset::factory()->for($noApprovedMediaEvent)->for($owner)->create([
        'moderation_status' => 'processing',
    ]);

    $this->actingAs($owner)
        ->post(route('dashboard.business.exports.start'), [
            'search' => 'Export Queue',
            'status' => 'all',
        ])
        ->assertRedirect(route('dashboard.business', ['search' => 'Export Queue']))
        ->assertSessionHas('success');

    Queue::assertPushed(GenerateEventMediaExport::class, 1);
    expect($exportableEvent->fresh()->media_export_status)->toBe('pending');
    expect($alreadyRunningEvent->fresh()->media_export_status)->toBe('processing');
    expect($noApprovedMediaEvent->fresh()->media_export_status)->toBeNull();
});

test('business dashboard can export the current filtered billing queue as csv', function () {
    $owner = User::factory()->business()->create();

    $overdueEvent = Event::factory()->for($owner)->create([
        'name' => 'Billing Queue Event',
        'status' => Event::STATUS_LOCKED,
        'is_paid' => false,
        'payment_due_at' => now()->subDay(),
    ]);
    Event::factory()->for($owner)->create([
        'name' => 'Paid Event',
        'status' => Event::STATUS_LIVE,
        'is_paid' => true,
        'payment_due_at' => now()->addDay(),
    ]);

    EventAsset::factory()->for($overdueEvent)->for($owner)->create([
        'moderation_status' => 'approved',
    ]);

    $response = $this->actingAs($owner)
        ->get(route('dashboard.business.billing-queue', [
            'status' => 'overdue',
            'search' => 'Billing Queue',
        ]));

    $response
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    expect($content)->toContain('Billing Queue Event');
    expect($content)->toContain(route('events.settings', ['event' => $overdueEvent, 'tab' => 'billing']));
    expect($content)->not->toContain('Paid Event');
});

test('business dashboard bulk actions can target a selected subset of filtered workspaces', function () {
    Queue::fake();

    $owner = User::factory()->business()->create();

    $selectedEvent = Event::factory()->for($owner)->create([
        'name' => 'Selected Billing Event',
        'status' => Event::STATUS_LIVE,
        'is_paid' => false,
        'payment_due_at' => now()->subDay(),
        'media_export_status' => null,
    ]);
    $otherEvent = Event::factory()->for($owner)->create([
        'name' => 'Other Billing Event',
        'status' => Event::STATUS_LIVE,
        'is_paid' => false,
        'payment_due_at' => now()->subDay(),
        'media_export_status' => null,
    ]);

    EventAsset::factory()->for($selectedEvent)->for($owner)->create([
        'moderation_status' => 'approved',
    ]);
    EventAsset::factory()->for($otherEvent)->for($owner)->create([
        'moderation_status' => 'approved',
    ]);

    $this->actingAs($owner)
        ->post(route('dashboard.business.exports.start'), [
            'status' => 'overdue',
            'event_ids' => [$selectedEvent->id],
        ])
        ->assertRedirect(route('dashboard.business', ['status' => 'overdue']))
        ->assertSessionHas('success');

    Queue::assertPushed(GenerateEventMediaExport::class, 1);
    expect($selectedEvent->fresh()->media_export_status)->toBe('pending');
    expect($otherEvent->fresh()->media_export_status)->toBeNull();

    $response = $this->actingAs($owner)
        ->get(route('dashboard.business.billing-queue', [
            'status' => 'overdue',
            'event_ids' => [$selectedEvent->id],
        ]));

    $content = $response->streamedContent();

    expect($content)->toContain('Selected Billing Event');
    expect($content)->not->toContain('Other Billing Event');
});

test('business dashboard bulk actions can target all filtered workspaces explicitly', function () {
    Queue::fake();

    $owner = User::factory()->business()->create();

    $firstEvent = Event::factory()->for($owner)->create([
        'name' => 'All Filtered Event One',
        'status' => Event::STATUS_LIVE,
        'is_paid' => false,
        'payment_due_at' => now()->subDay(),
        'media_export_status' => null,
    ]);
    $secondEvent = Event::factory()->for($owner)->create([
        'name' => 'All Filtered Event Two',
        'status' => Event::STATUS_LIVE,
        'is_paid' => false,
        'payment_due_at' => now()->subDay(),
        'media_export_status' => null,
    ]);

    EventAsset::factory()->for($firstEvent)->for($owner)->create([
        'moderation_status' => 'approved',
    ]);
    EventAsset::factory()->for($secondEvent)->for($owner)->create([
        'moderation_status' => 'approved',
    ]);

    $this->actingAs($owner)
        ->post(route('dashboard.business.exports.start'), [
            'status' => 'overdue',
            'selection_scope' => 'all_filtered',
            'event_ids' => [$firstEvent->id],
        ])
        ->assertRedirect(route('dashboard.business', [
            'status' => 'overdue',
            'selection_scope' => 'all_filtered',
        ]))
        ->assertSessionHas('success');

    Queue::assertPushed(GenerateEventMediaExport::class, 2);
    expect($firstEvent->fresh()->media_export_status)->toBe('pending');
    expect($secondEvent->fresh()->media_export_status)->toBe('pending');

    $response = $this->actingAs($owner)
        ->get(route('dashboard.business.billing-queue', [
            'status' => 'overdue',
            'selection_scope' => 'all_filtered',
            'event_ids' => [$firstEvent->id],
        ]));

    $content = $response->streamedContent();

    expect($content)->toContain('All Filtered Event One');
    expect($content)->toContain('All Filtered Event Two');
});

test('super admins can open the dedicated business dashboard explicitly', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    Event::factory()->for($admin)->create([
        'name' => 'Admin Business Event',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard.business'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard/BusinessOverview')
            ->where('auth.user.accountType', User::ACCOUNT_TYPE_SUPER_ADMIN)
            ->where('dashboardLinks.business', route('dashboard.business'))
        );
});

test('owned events page renders all owned event workspaces', function () {
    $owner = User::factory()->create();
    Event::factory()->count(2)->for($owner)->create();

    $this->actingAs($owner)
        ->get(route('dashboard.events'))
        ->assertRedirect(route('dashboard.account').'#events');
});

test('business accounts open the account events page from the events shortcut', function () {
    $owner = User::factory()->business()->create();
    Event::factory()->count(2)->for($owner)->create();

    $this->actingAs($owner)
        ->get(route('dashboard.events'))
        ->assertRedirect(route('dashboard.business.events.index'));
});

test('business accounts can open a dedicated business events page', function () {
    $owner = User::factory()->business()->create();
    Event::factory()->count(2)->for($owner)->create([
        'name' => 'Business Workspace',
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard.business.events.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard/BusinessEvents')
            ->where('sidebarLabel', 'Business')
            ->where('businessNavigation.2.href', route('dashboard.business.events.index'))
            ->where('dashboardLinks.ownedEvents', route('dashboard.business.events.index'))
            ->where('ownedEventsPagination.total', 2)
        );
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
        ->assertRedirect(route('dashboard.account').'#activity');
});

test('business accounts open the account activity page from the activity shortcut', function () {
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
        ->assertRedirect(route('dashboard.account').'#activity');
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
