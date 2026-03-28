<?php

use App\Models\Event;
use App\Models\Plan;
use App\Models\User;
use Carbon\CarbonImmutable;

it('redirects guests to registration before onboarding begins', function () {
    $this->get(route('onboarding.create'))
        ->assertRedirect(route('register'));
});

it('sends single-event owners back into onboarding when onboarding is in progress', function () {
    $user = User::factory()->create();
    $event = Event::factory()->for($user)->create([
        'onboarding_step' => 'photos',
        'onboarding_completed_at' => null,
    ]);

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertRedirect(route('onboarding.photos', $event));
});

it('redirects completed users away from onboarding unless restart is requested', function () {
    $user = User::factory()->create();
    Event::factory()->for($user)->create([
        'onboarding_step' => 'completed',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('onboarding.create'));

    $response->assertRedirect(route('dashboard'));
});

it('allows completed users to restart onboarding using restart query', function () {
    $user = User::factory()->create();
    Event::factory()->for($user)->create([
        'onboarding_step' => 'completed',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('onboarding.create', ['restart' => 1]));

    $response->assertOk();
});

it('creates an event from onboarding and calculates event windows', function () {
    CarbonImmutable::setTestNow('2026-03-10 12:00:00');

    $plusPlan = Plan::factory()->create([
        'name' => 'Plus',
        'slug' => 'plus',
        'currency' => 'EUR',
        'price_cents' => 4900,
        'storage_limit_bytes' => 12884901888,
        'upload_limit' => 500,
        'retention_days' => 90,
        'grace_days' => 7,
        'upload_window_days' => 30,
        'customization_tier' => 'better',
        'download_all_enabled' => true,
        'moderation_tools_enabled' => false,
        'remove_app_branding' => false,
        'video_max_duration_seconds' => 45,
        'photo_max_size_bytes' => 26214400,
        'video_max_size_bytes' => 524288000,
        'is_active' => true,
        'is_default' => false,
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('onboarding.store'), [
        'plan_slug' => 'plus',
        'type' => 'wedding',
        'name' => 'Dan and Rachel Wedding',
        'wedding_partner_one_first_name' => 'Dan',
        'wedding_partner_two_first_name' => 'Rachel',
        'wedding_family_name' => 'Ionescu',
        'attendee_estimate' => 140,
        'event_dates' => [
            [
                'label' => 'Civil day',
                'date' => '2026-05-14',
            ],
            [
                'label' => 'Reception day',
                'date' => '2026-05-15',
            ],
        ],
        'sub_events' => [
            [
                'key' => 'civil-ceremony',
                'label' => 'Civil union',
                'date' => '2026-05-14',
                'start_time' => '14:00',
                'address' => '12 Garden Lane, Bucharest, Romania',
                'no_address' => false,
            ],
            [
                'key' => 'reception',
                'label' => 'Reception',
                'date' => '2026-05-15',
                'start_time' => '18:30',
                'address' => 'Sunset Ballroom, Bucharest, Romania',
                'no_address' => false,
            ],
        ],
        'timezone' => 'Europe/Bucharest',
    ]);

    $event = Event::query()->firstOrFail();

    $response->assertRedirect(route('onboarding.creating', $event));

    expect($event->plan_id)->toBe($plusPlan->id)
        ->and($event->upload_window_starts_at?->toDateTimeString())->toBe('2026-05-14 00:00:00')
        ->and($event->upload_window_ends_at?->toDateTimeString())->toBe('2026-06-12 23:59:59')
        ->and($event->grace_ends_at?->toDateTimeString())->toBe('2026-06-19 23:59:59')
        ->and($event->payment_due_at?->toDateTimeString())->toBe('2026-05-14 00:00:00')
        ->and($event->venue_address)->toBe('12 Garden Lane, Bucharest, Romania')
        ->and($event->attendee_estimate)->toBe(140)
        ->and($event->event_dates)->toBe([
            [
                'label' => 'Civil day',
                'date' => '2026-05-14',
            ],
            [
                'label' => 'Reception day',
                'date' => '2026-05-15',
            ],
        ])
        ->and($event->sub_events)->toBe([
            [
                'key' => 'civil-ceremony',
                'label' => 'Civil union',
                'date' => '2026-05-14',
                'start_time' => '14:00',
                'address' => '12 Garden Lane, Bucharest, Romania',
                'no_address' => false,
            ],
            [
                'key' => 'reception',
                'label' => 'Reception',
                'date' => '2026-05-15',
                'start_time' => '18:30',
                'address' => 'Sunset Ballroom, Bucharest, Romania',
                'no_address' => false,
            ],
        ])
        ->and($event->branding)->toMatchArray([
            'event_naming' => [
                'partner_one_first_name' => 'Dan',
                'partner_two_first_name' => 'Rachel',
                'family_name' => 'Ionescu',
            ],
        ])
        ->and($event->storage_limit_bytes)->toBe(12884901888)
        ->and($event->upload_limit)->toBe(500)
        ->and($event->upload_window_days)->toBe(30)
        ->and($event->customization_tier)->toBe('better')
        ->and($event->download_all_enabled)->toBeTrue()
        ->and($event->moderation_tools_enabled)->toBeFalse()
        ->and($event->video_max_duration_seconds)->toBe(45)
        ->and($event->is_paid)->toBeFalse()
        ->and($event->onboarding_step)->toBe('creating')
        ->and($user->fresh()->account_type)->toBe(User::ACCOUNT_TYPE_USER);

    CarbonImmutable::setTestNow();
});

it('keeps multi-event owners as regular users after creating another event', function () {
    Plan::factory()->create([
        'name' => 'Free',
        'slug' => 'free',
        'price_cents' => 0,
        'upload_limit' => 100,
        'retention_days' => 7,
        'grace_days' => 0,
        'upload_window_days' => 1,
        'is_active' => true,
        'is_default' => true,
    ]);

    $user = User::factory()->create();
    Event::factory()->for($user)->create([
        'name' => 'Existing Event',
    ]);

    $this->actingAs($user);

    $this->post(route('onboarding.store'), [
        'plan_slug' => 'free',
        'type' => 'wedding',
        'name' => 'Second Event',
        'wedding_partner_one_first_name' => 'Alex',
        'wedding_partner_two_first_name' => 'Bianca',
        'wedding_family_name' => 'Popa',
        'attendee_estimate' => 140,
        'event_dates' => [
            [
                'label' => 'Main day',
                'date' => now()->addMonth()->toDateString(),
            ],
        ],
        'sub_events' => [
            [
                'key' => 'reception',
                'label' => 'Reception',
                'date' => now()->addMonth()->toDateString(),
                'start_time' => '18:00',
                'address' => '12 Garden Lane, Bucharest, Romania',
                'no_address' => false,
            ],
        ],
        'timezone' => 'Europe/Bucharest',
    ])->assertRedirect();

    expect($user->fresh()->account_type)->toBe(User::ACCOUNT_TYPE_USER);
});

it('redirects business accounts into business onboarding instead of consumer onboarding', function () {
    $user = User::factory()->create([
        'account_type' => User::ACCOUNT_TYPE_BUSINESS,
        'business_onboarded_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('onboarding.create'))
        ->assertRedirect(route('dashboard.business.onboarding'));
});

it('lets onboarded business accounts create paid events from wallet credits', function () {
    $plusPlan = Plan::factory()->create([
        'name' => 'Plus',
        'slug' => 'plus',
        'currency' => 'EUR',
        'price_cents' => 4900,
        'business_enabled' => true,
        'business_credit_cost' => 25,
        'storage_limit_bytes' => 12884901888,
        'upload_limit' => 500,
        'retention_days' => 90,
        'grace_days' => 7,
        'upload_window_days' => 30,
        'customization_tier' => 'better',
        'download_all_enabled' => true,
        'moderation_tools_enabled' => false,
        'remove_app_branding' => false,
        'video_max_duration_seconds' => 45,
        'photo_max_size_bytes' => 26214400,
        'video_max_size_bytes' => 524288000,
        'is_active' => true,
        'is_default' => true,
    ]);

    $user = User::factory()->business()->create([
        'business_wallet_credits' => 100,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard.business.events.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('onboarding/Create')
            ->where('businessMode', true)
            ->where('businessWalletCredits', 100)
            ->where('businessTopUpUrl', route('dashboard.business.wallet.history'))
            ->where('sidebarLabel', 'Business')
            ->where('dashboardLinks.business', route('dashboard.business'))
            ->where('dashboardLinks.createBusiness', route('dashboard.business.events.create'))
            ->where('accountNavigation.1.title', 'Create')
            ->where('accountNavigation.1.href', route('dashboard.business.events.create'))
        );

    $response = $this->actingAs($user)->post(route('dashboard.business.events.store'), [
        'plan_slug' => 'plus',
        'type' => 'wedding',
        'name' => 'Studio Wedding',
        'wedding_partner_one_first_name' => 'Ana',
        'wedding_partner_two_first_name' => 'Mihai',
        'wedding_family_name' => 'Ionescu',
        'attendee_estimate' => 140,
        'event_dates' => [
            [
                'label' => 'Main day',
                'date' => now()->addMonth()->toDateString(),
            ],
        ],
        'sub_events' => [
            [
                'key' => 'reception',
                'label' => 'Reception',
                'date' => now()->addMonth()->toDateString(),
                'start_time' => '18:00',
                'address' => '12 Garden Lane, Bucharest, Romania',
                'no_address' => false,
            ],
        ],
        'timezone' => 'Europe/Bucharest',
    ]);

    $event = Event::query()->firstOrFail();

    $response->assertRedirect(route('onboarding.creating', $event));

    expect($event->plan_id)->toBe($plusPlan->id)
        ->and($event->is_paid)->toBeTrue()
        ->and($event->payment_due_at)->toBeNull()
        ->and($event->paid_at)->not->toBeNull()
        ->and($user->fresh()->business_wallet_credits)->toBe(75);
});

it('shows a validation error when a business wallet cannot afford the selected plan', function () {
    Plan::factory()->create([
        'name' => 'Plus',
        'slug' => 'plus',
        'currency' => 'EUR',
        'price_cents' => 4900,
        'business_enabled' => true,
        'business_credit_cost' => 25,
        'storage_limit_bytes' => 12884901888,
        'upload_limit' => 500,
        'retention_days' => 90,
        'grace_days' => 7,
        'upload_window_days' => 30,
        'customization_tier' => 'better',
        'download_all_enabled' => true,
        'moderation_tools_enabled' => false,
        'remove_app_branding' => false,
        'video_max_duration_seconds' => 45,
        'photo_max_size_bytes' => 26214400,
        'video_max_size_bytes' => 524288000,
        'is_active' => true,
        'is_default' => true,
    ]);

    $user = User::factory()->business()->create([
        'business_wallet_credits' => 10,
    ]);

    $this->actingAs($user)
        ->from(route('dashboard.business.events.create'))
        ->post(route('dashboard.business.events.store'), [
            'plan_slug' => 'plus',
            'type' => 'wedding',
            'name' => 'Studio Wedding',
            'wedding_partner_one_first_name' => 'Ana',
            'wedding_partner_two_first_name' => 'Mihai',
            'wedding_family_name' => 'Ionescu',
            'attendee_estimate' => 140,
            'event_dates' => [
                [
                    'label' => 'Main day',
                    'date' => now()->addMonth()->toDateString(),
                ],
            ],
            'sub_events' => [
                [
                    'key' => 'reception',
                    'label' => 'Reception',
                    'date' => now()->addMonth()->toDateString(),
                    'start_time' => '18:00',
                    'address' => '12 Garden Lane, Bucharest, Romania',
                    'no_address' => false,
                ],
            ],
            'timezone' => 'Europe/Bucharest',
        ])
        ->assertRedirect(route('dashboard.business.events.create'))
        ->assertSessionHasErrors([
            'plan_slug' => 'This Plus event needs 25 credits. Your wallet has 10, so top up 15 more first.',
        ]);

    expect(Event::query()->count())->toBe(0)
        ->and($user->fresh()->business_wallet_credits)->toBe(10);
});

it('blocks business accounts from creating free plan events', function () {
    Plan::factory()->create([
        'name' => 'Free',
        'slug' => 'free',
        'price_cents' => 0,
        'upload_limit' => 100,
        'retention_days' => 7,
        'grace_days' => 0,
        'upload_window_days' => 1,
        'business_enabled' => false,
        'is_active' => true,
        'is_default' => false,
    ]);

    Plan::factory()->create([
        'name' => 'Plus',
        'slug' => 'plus',
        'price_cents' => 4900,
        'business_enabled' => true,
        'business_credit_cost' => 25,
        'is_active' => true,
        'is_default' => true,
    ]);

    $user = User::factory()->business()->create([
        'business_wallet_credits' => 100,
    ]);

    $this->actingAs($user)
        ->post(route('dashboard.business.events.store'), [
            'plan_slug' => 'free',
            'type' => 'wedding',
            'name' => 'Blocked Event',
            'wedding_partner_one_first_name' => 'Alex',
            'wedding_partner_two_first_name' => 'Bianca',
            'wedding_family_name' => 'Popa',
            'attendee_estimate' => 140,
            'event_dates' => [
                [
                    'label' => 'Main day',
                    'date' => now()->addMonth()->toDateString(),
                ],
            ],
            'sub_events' => [
                [
                    'key' => 'reception',
                    'label' => 'Reception',
                    'date' => now()->addMonth()->toDateString(),
                    'start_time' => '18:00',
                    'address' => '12 Garden Lane, Bucharest, Romania',
                    'no_address' => false,
                ],
            ],
            'timezone' => 'Europe/Bucharest',
        ])
        ->assertStatus(422);

    expect(Event::query()->count())->toBe(0)
        ->and($user->fresh()->business_wallet_credits)->toBe(100);
});

it('blocks event dates too far in the future', function () {
    CarbonImmutable::setTestNow('2026-03-10 12:00:00');

    Plan::factory()->create([
        'name' => 'Free',
        'slug' => 'free',
        'price_cents' => 0,
        'upload_limit' => 100,
        'retention_days' => 7,
        'grace_days' => 0,
        'upload_window_days' => 1,
        'is_active' => true,
        'is_default' => true,
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->from(route('onboarding.create'))->post(route('onboarding.store'), [
        'plan_slug' => 'free',
        'type' => 'wedding',
        'name' => 'Future Event',
        'wedding_partner_one_first_name' => 'Dan',
        'wedding_partner_two_first_name' => 'Rachel',
        'wedding_family_name' => 'Ionescu',
        'attendee_estimate' => 80,
        'event_dates' => [
            [
                'label' => 'Main day',
                'date' => '2030-12-31',
            ],
        ],
        'sub_events' => [
            [
                'key' => 'reception',
                'label' => 'Reception',
                'date' => '2030-12-31',
                'start_time' => '18:00',
                'address' => 'Future Hall, Bucharest',
                'no_address' => false,
            ],
        ],
        'timezone' => 'Europe/Bucharest',
    ]);

    $response->assertRedirect(route('onboarding.create'));
    $response->assertSessionHasErrors(['event_dates.0.date']);

    CarbonImmutable::setTestNow();
});

it('creates an event for the signed-in owner without collecting account details inside onboarding', function () {
    Plan::factory()->create([
        'name' => 'Free',
        'slug' => 'free',
        'price_cents' => 0,
        'storage_limit_bytes' => 3221225472,
        'upload_limit' => 100,
        'retention_days' => 7,
        'grace_days' => 0,
        'upload_window_days' => 1,
        'customization_tier' => 'basic',
        'download_all_enabled' => false,
        'moderation_tools_enabled' => false,
        'remove_app_branding' => false,
        'video_max_duration_seconds' => 30,
        'photo_max_size_bytes' => 15728640,
        'video_max_size_bytes' => 314572800,
        'is_active' => true,
        'is_default' => true,
    ]);

    $owner = User::factory()->create([
        'email' => 'mara@example.com',
    ]);
    $this->actingAs($owner);

    $response = $this->post(route('onboarding.store'), [
        'plan_slug' => 'free',
        'type' => 'wedding',
        'name' => 'Mara and Luca Wedding',
        'wedding_partner_one_first_name' => 'Mara',
        'wedding_partner_two_first_name' => 'Luca',
        'wedding_family_name' => 'Popescu',
        'attendee_estimate' => 120,
        'event_dates' => [
            [
                'label' => 'Main day',
                'date' => now()->addMonth()->toDateString(),
            ],
        ],
        'sub_events' => [
            [
                'key' => 'reception',
                'label' => 'Reception',
                'date' => now()->addMonth()->toDateString(),
                'start_time' => '17:00',
                'address' => 'Strada Lalelelor 12, Cluj-Napoca',
                'no_address' => false,
            ],
        ],
        'timezone' => 'Europe/Bucharest',
    ]);

    $event = Event::query()->firstOrFail();

    $response->assertRedirect(route('onboarding.creating', $event));
    $this->assertAuthenticatedAs($owner);

    expect($event->user_id)->toBe($owner->id)
        ->and($event->is_paid)->toBeTrue()
        ->and($event->payment_due_at)->toBeNull()
        ->and($event->upload_window_days)->toBe(1)
        ->and($event->customization_tier)->toBe('basic');
});

it('allows a selected moment to skip the address when marked accordingly', function () {
    Plan::factory()->create([
        'name' => 'Free',
        'slug' => 'free',
        'price_cents' => 0,
        'upload_limit' => 100,
        'retention_days' => 7,
        'grace_days' => 0,
        'upload_window_days' => 1,
        'is_active' => true,
        'is_default' => true,
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('onboarding.store'), [
        'plan_slug' => 'free',
        'type' => 'party',
        'name' => 'Launch Party',
        'attendee_estimate' => 90,
        'event_dates' => [
            [
                'label' => 'Main day',
                'date' => now()->addMonth()->toDateString(),
            ],
        ],
        'sub_events' => [
            [
                'key' => 'main-party',
                'label' => 'Main party',
                'date' => now()->addMonth()->toDateString(),
                'start_time' => '20:00',
                'address' => '',
                'no_address' => true,
            ],
        ],
        'timezone' => 'Europe/Bucharest',
    ]);

    $event = Event::query()->firstOrFail();

    $response->assertRedirect(route('onboarding.creating', $event));

    expect($event->venue_address)->toBeNull()
        ->and($event->sub_events)->toBe([
            [
                'key' => 'main-party',
                'label' => 'Main party',
                'date' => now()->addMonth()->toDateString(),
                'start_time' => '20:00',
                'address' => null,
                'no_address' => true,
            ],
        ]);
});

it('requires at least two relevant moments for baptisms', function () {
    Plan::factory()->create([
        'name' => 'Free',
        'slug' => 'free',
        'price_cents' => 0,
        'upload_limit' => 100,
        'retention_days' => 7,
        'grace_days' => 0,
        'upload_window_days' => 1,
        'is_active' => true,
        'is_default' => true,
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->from(route('onboarding.create'))->post(route('onboarding.store'), [
        'plan_slug' => 'free',
        'type' => 'baptism',
        'name' => 'Matei Baptism',
        'attendee_estimate' => 80,
        'event_dates' => [
            [
                'label' => 'Main day',
                'date' => now()->addMonth()->toDateString(),
            ],
        ],
        'sub_events' => [
            [
                'key' => 'church-ceremony',
                'label' => 'Church ceremony',
                'date' => now()->addMonth()->toDateString(),
                'start_time' => '11:00',
                'address' => 'Saint George Church, Bucharest',
                'no_address' => false,
            ],
        ],
        'timezone' => 'Europe/Bucharest',
    ]);

    $response->assertRedirect(route('onboarding.create'));
    $response->assertSessionHasErrors(['sub_events']);
});

it('marks onboarding complete when ready screen is opened', function () {
    $user = User::factory()->create();
    $event = Event::factory()->for($user)->create([
        'onboarding_step' => 'photos',
        'onboarding_completed_at' => null,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('onboarding.ready', $event));

    $response->assertOk();

    $event->refresh();

    expect($event->onboarding_step)->toBe('completed')
        ->and($event->onboarding_completed_at)->not->toBeNull();
});
