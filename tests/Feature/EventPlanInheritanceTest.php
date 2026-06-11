<?php

use App\Models\Plan;
use App\Models\User;

test('new onboarding events inherit the current active default plan for their currency', function () {
    Plan::query()->delete();

    $defaultPlan = Plan::factory()->create([
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
        'is_default' => true,
    ]);
    Plan::factory()->create([
        'name' => 'Legacy',
        'slug' => 'legacy',
        'currency' => 'EUR',
        'price_cents' => 2000,
        'storage_limit_bytes' => 10737418240,
        'upload_limit' => 300,
        'is_active' => true,
        'is_default' => false,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.store'), [
            'plan_slug' => 'plus',
            'type' => 'wedding',
            'name' => 'Package Driven Event',
            'wedding_partner_one_first_name' => 'Alex',
            'wedding_partner_two_first_name' => 'Bianca',
            'wedding_family_name' => 'Popescu',
            'venue_address' => '20 Lake View Road, Bucharest',
            'attendee_estimate' => 180,
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
                    'start_time' => '18:30',
                    'address' => '20 Lake View Road, Bucharest',
                    'no_address' => false,
                ],
            ],
            'timezone' => 'Europe/Bucharest',
        ])
        ->assertRedirect();

    $event = $user->events()->latest('id')->firstOrFail();

    expect($event->plan_id)->toBe($defaultPlan->id)
        ->and($event->storage_limit_bytes)->toBe(12884901888)
        ->and($event->upload_limit)->toBe(500)
        ->and($event->upload_window_days)->toBe(30)
        ->and($event->customization_tier)->toBe('better')
        ->and($event->download_all_enabled)->toBeTrue()
        ->and($event->video_max_duration_seconds)->toBe(45)
        ->and($event->photo_max_size_bytes)->toBe(26214400)
        ->and($event->video_max_size_bytes)->toBe(524288000);
});
