<?php

use App\Models\Plan;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('regular users cannot access the admin plans page', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.plans'))
        ->assertForbidden();
});

test('super admins can review create and update plans', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    $starterPlan = Plan::factory()->create([
        'name' => 'Starter 20 EUR',
        'slug' => 'starter-20-eur',
        'currency' => 'EUR',
        'price_cents' => 2000,
        'storage_limit_bytes' => 10737418240,
        'upload_limit' => 300,
        'retention_days' => 30,
        'grace_days' => 7,
        'video_max_duration_seconds' => 30,
        'photo_max_size_bytes' => 26214400,
        'video_max_size_bytes' => 524288000,
        'is_active' => true,
        'is_default' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.plans'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Plans')
            ->where('adminLinks.plans', route('admin.plans'))
            ->where(
                'plans',
                fn ($plans): bool => collect($plans)->contains(
                    fn (array $plan): bool => $plan['name'] === 'Starter 20 EUR'
                        && $plan['isDefault'] === true
                        && $plan['storageLimitBytes'] === 10737418240,
                ),
            )
        );

    $this->actingAs($admin)
        ->post(route('admin.plans.store'), [
            'name' => 'Business 49 EUR',
            'slug' => 'business-49-eur',
            'description' => 'Better storage and upload room for active studios.',
            'currency' => 'EUR',
            'price_cents' => 4900,
            'storage_limit_gb' => 20,
            'upload_limit' => 800,
            'retention_days' => 45,
            'grace_days' => 10,
            'video_max_duration_seconds' => 45,
            'photo_max_size_mb' => 25,
            'video_max_size_mb' => 750,
            'is_active' => true,
            'is_default' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Business 49 EUR package created.');

    $businessPlan = Plan::query()->where('slug', 'business-49-eur')->firstOrFail();

    expect($businessPlan->price_cents)->toBe(4900)
        ->and($businessPlan->storage_limit_bytes)->toBe(21474836480)
        ->and($businessPlan->upload_limit)->toBe(800)
        ->and($businessPlan->is_default)->toBeTrue();

    expect($starterPlan->fresh()->is_default)->toBeFalse();

    $this->actingAs($admin)
        ->patch(route('admin.plans.update', $businessPlan), [
            'name' => 'Business 59 EUR',
            'slug' => 'business-59-eur',
            'description' => 'Raised limits for premium planners.',
            'currency' => 'EUR',
            'price_cents' => 5900,
            'storage_limit_gb' => 25,
            'upload_limit' => 1000,
            'retention_days' => 60,
            'grace_days' => 14,
            'video_max_duration_seconds' => 60,
            'photo_max_size_mb' => 30,
            'video_max_size_mb' => 1024,
            'is_active' => true,
            'is_default' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Business 59 EUR package updated.');

    $businessPlan->refresh();

    expect($businessPlan->name)->toBe('Business 59 EUR')
        ->and($businessPlan->slug)->toBe('business-59-eur')
        ->and($businessPlan->price_cents)->toBe(5900)
        ->and($businessPlan->storage_limit_bytes)->toBe(26843545600)
        ->and($businessPlan->upload_limit)->toBe(1000)
        ->and($businessPlan->retention_days)->toBe(60)
        ->and($businessPlan->grace_days)->toBe(14)
        ->and($businessPlan->video_max_duration_seconds)->toBe(60)
        ->and($businessPlan->photo_max_size_bytes)->toBe(31457280)
        ->and($businessPlan->video_max_size_bytes)->toBe(1073741824)
        ->and($businessPlan->is_default)->toBeTrue();
});

test('pricing page renders active plans from the live catalog', function () {
    Plan::factory()->create([
        'name' => 'Starter 20 EUR',
        'slug' => 'starter-20-eur',
        'currency' => 'EUR',
        'price_cents' => 2000,
        'storage_limit_bytes' => 10737418240,
        'upload_limit' => 300,
        'retention_days' => 30,
        'is_default' => true,
        'is_active' => true,
    ]);
    Plan::factory()->create([
        'name' => 'Hidden Legacy Plan',
        'slug' => 'hidden-legacy-plan',
        'currency' => 'EUR',
        'price_cents' => 1500,
        'is_active' => false,
        'is_default' => false,
    ]);

    $this->get(route('pricing'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pricing')
            ->has('plans', 1)
            ->where('plans.0.name', 'Starter 20 EUR')
            ->where('plans.0.priceLabel', 'EUR 20.00')
            ->where('plans.0.storageLabel', '10 GB storage')
            ->where('plans.0.isDefault', true)
        );
});

test('new onboarding events inherit the current active default plan for their currency', function () {
    Plan::query()->delete();

    $defaultPlan = Plan::factory()->create([
        'name' => 'Event 39 EUR',
        'slug' => 'event-39-eur',
        'currency' => 'EUR',
        'price_cents' => 3900,
        'storage_limit_bytes' => 21474836480,
        'upload_limit' => 600,
        'retention_days' => 45,
        'grace_days' => 10,
        'video_max_duration_seconds' => 45,
        'photo_max_size_bytes' => 31457280,
        'video_max_size_bytes' => 786432000,
        'is_active' => true,
        'is_default' => true,
    ]);
    Plan::factory()->create([
        'name' => 'Legacy 20 EUR',
        'slug' => 'legacy-20-eur',
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
            'type' => 'wedding',
            'name' => 'Package Driven Event',
            'venue_address' => '20 Lake View Road, Bucharest',
            'attendee_estimate' => 180,
            'event_dates' => [
                [
                    'label' => 'Main day',
                    'date' => now()->addMonth()->toDateString(),
                ],
            ],
            'timezone' => 'Europe/Bucharest',
        ])
        ->assertRedirect();

    $event = $user->events()->latest('id')->firstOrFail();

    expect($event->plan_id)->toBe($defaultPlan->id)
        ->and($event->storage_limit_bytes)->toBe(21474836480)
        ->and($event->upload_limit)->toBe(600)
        ->and($event->video_max_duration_seconds)->toBe(45)
        ->and($event->photo_max_size_bytes)->toBe(31457280)
        ->and($event->video_max_size_bytes)->toBe(786432000);
});
