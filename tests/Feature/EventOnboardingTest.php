<?php

use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia as Assert;

it('shows dashboard onboarding continuation when onboarding is in progress', function () {
    $user = User::factory()->create();
    $event = Event::factory()->for($user)->create([
        'onboarding_step' => 'photos',
        'onboarding_completed_at' => null,
    ]);

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('continueSetupEvent.name', $event->name)
            ->where('continueSetupEvent.primaryAction.url', route('onboarding.photos', $event))
        );
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

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('onboarding.store'), [
        'type' => 'wedding',
        'name' => 'Dan and Rachel Wedding',
        'wedding_partner_one_first_name' => 'Dan',
        'wedding_partner_two_first_name' => 'Rachel',
        'wedding_family_name' => 'Ionescu',
        'venue_address' => '12 Garden Lane, Bucharest, Romania',
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
            ],
            [
                'key' => 'reception',
                'label' => 'Reception',
                'date' => '2026-05-15',
                'start_time' => '18:30',
            ],
        ],
        'timezone' => 'Europe/Bucharest',
    ]);

    $event = Event::query()->firstOrFail();

    $response->assertRedirect(route('onboarding.creating', $event));

    expect($event->upload_window_starts_at?->toDateTimeString())->toBe('2026-05-13 00:00:00')
        ->and($event->upload_window_ends_at?->toDateTimeString())->toBe('2026-05-17 23:59:59')
        ->and($event->grace_ends_at?->toDateTimeString())->toBe('2026-05-21 23:59:59')
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
            ],
            [
                'key' => 'reception',
                'label' => 'Reception',
                'date' => '2026-05-15',
                'start_time' => '18:30',
            ],
        ])
        ->and($event->branding)->toMatchArray([
            'event_naming' => [
                'partner_one_first_name' => 'Dan',
                'partner_two_first_name' => 'Rachel',
                'family_name' => 'Ionescu',
            ],
        ])
        ->and($event->storage_limit_bytes)->toBe(10737418240)
        ->and($event->upload_limit)->toBe(300)
        ->and($event->video_max_duration_seconds)->toBe(30)
        ->and($event->onboarding_step)->toBe('creating')
        ->and($user->fresh()->account_type)->toBe(User::ACCOUNT_TYPE_USER);

    CarbonImmutable::setTestNow();
});

it('promotes multi-event owners to business after creating another event', function () {
    $user = User::factory()->create();
    Event::factory()->for($user)->create([
        'name' => 'Existing Event',
    ]);

    $this->actingAs($user);

    $this->post(route('onboarding.store'), [
        'type' => 'wedding',
        'name' => 'Second Event',
        'wedding_partner_one_first_name' => 'Alex',
        'wedding_partner_two_first_name' => 'Bianca',
        'wedding_family_name' => 'Popa',
        'venue_address' => '12 Garden Lane, Bucharest, Romania',
        'attendee_estimate' => 140,
        'event_dates' => [
            [
                'label' => 'Main day',
                'date' => now()->addMonth()->toDateString(),
            ],
        ],
        'timezone' => 'Europe/Bucharest',
    ])->assertRedirect();

    expect($user->fresh()->account_type)->toBe(User::ACCOUNT_TYPE_BUSINESS);
});

it('blocks event dates too far in the future', function () {
    CarbonImmutable::setTestNow('2026-03-10 12:00:00');

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->from(route('onboarding.create'))->post(route('onboarding.store'), [
        'type' => 'wedding',
        'name' => 'Future Event',
        'wedding_partner_one_first_name' => 'Dan',
        'wedding_partner_two_first_name' => 'Rachel',
        'wedding_family_name' => 'Ionescu',
        'venue_address' => 'Future Hall, Bucharest',
        'attendee_estimate' => 80,
        'event_dates' => [
            [
                'label' => 'Main day',
                'date' => '2030-12-31',
            ],
        ],
        'timezone' => 'Europe/Bucharest',
    ]);

    $response->assertRedirect(route('onboarding.create'));
    $response->assertSessionHasErrors(['event_dates.0.date']);

    CarbonImmutable::setTestNow();
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
