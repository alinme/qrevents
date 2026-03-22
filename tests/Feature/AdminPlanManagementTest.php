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

    $freePlan = Plan::factory()->create([
        'name' => 'Free',
        'slug' => 'free',
        'currency' => 'EUR',
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

    $this->actingAs($admin)
        ->get(route('admin.plans'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Plans')
            ->where('adminLinks.plans', route('admin.plans'))
            ->where(
                'plans',
                fn ($plans): bool => collect($plans)->contains(
                    fn (array $plan): bool => $plan['name'] === 'Free'
                        && $plan['isDefault'] === true
                        && $plan['uploadWindowDays'] === 1
                        && $plan['customizationTier'] === 'basic',
                ),
            )
        );

    $this->actingAs($admin)
        ->post(route('admin.plans.store'), [
            'name' => 'Plus',
            'slug' => 'plus',
            'description' => 'Better storage and guest-facing controls for growing events.',
            'currency' => 'EUR',
            'price_cents' => 4900,
            'storage_limit_gb' => 12,
            'upload_limit' => 500,
            'retention_days' => 90,
            'grace_days' => 7,
            'upload_window_days' => 30,
            'customization_tier' => 'better',
            'video_max_duration_seconds' => 45,
            'photo_max_size_mb' => 25,
            'video_max_size_mb' => 500,
            'download_all_enabled' => true,
            'moderation_tools_enabled' => false,
            'remove_app_branding' => false,
            'is_active' => true,
            'is_default' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Plus package created.');

    $plusPlan = Plan::query()->where('slug', 'plus')->firstOrFail();

    expect($plusPlan->price_cents)->toBe(4900)
        ->and($plusPlan->storage_limit_bytes)->toBe(12884901888)
        ->and($plusPlan->upload_limit)->toBe(500)
        ->and($plusPlan->upload_window_days)->toBe(30)
        ->and($plusPlan->customization_tier)->toBe('better')
        ->and($plusPlan->download_all_enabled)->toBeTrue()
        ->and($plusPlan->moderation_tools_enabled)->toBeFalse()
        ->and($plusPlan->is_default)->toBeTrue();

    expect($freePlan->fresh()->is_default)->toBeFalse();

    $this->actingAs($admin)
        ->patch(route('admin.plans.update', $plusPlan), [
            'name' => 'Pro',
            'slug' => 'pro',
            'description' => 'Raised limits with moderation and white-label branding.',
            'currency' => 'EUR',
            'price_cents' => 9900,
            'storage_limit_gb' => 30,
            'upload_limit' => 1000000,
            'retention_days' => 365,
            'grace_days' => 14,
            'upload_window_days' => 90,
            'customization_tier' => 'advanced',
            'video_max_duration_seconds' => 60,
            'photo_max_size_mb' => 30,
            'video_max_size_mb' => 1024,
            'download_all_enabled' => true,
            'moderation_tools_enabled' => true,
            'remove_app_branding' => true,
            'is_active' => true,
            'is_default' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Pro package updated.');

    $plusPlan->refresh();

    expect($plusPlan->name)->toBe('Pro')
        ->and($plusPlan->slug)->toBe('pro')
        ->and($plusPlan->price_cents)->toBe(9900)
        ->and($plusPlan->storage_limit_bytes)->toBe(32212254720)
        ->and($plusPlan->upload_limit)->toBe(1000000)
        ->and($plusPlan->retention_days)->toBe(365)
        ->and($plusPlan->grace_days)->toBe(14)
        ->and($plusPlan->upload_window_days)->toBe(90)
        ->and($plusPlan->customization_tier)->toBe('advanced')
        ->and($plusPlan->download_all_enabled)->toBeTrue()
        ->and($plusPlan->moderation_tools_enabled)->toBeTrue()
        ->and($plusPlan->remove_app_branding)->toBeTrue()
        ->and($plusPlan->video_max_duration_seconds)->toBe(60)
        ->and($plusPlan->photo_max_size_bytes)->toBe(31457280)
        ->and($plusPlan->video_max_size_bytes)->toBe(1073741824)
        ->and($plusPlan->is_default)->toBeTrue();
});

test('pricing page renders active plans from the live catalog', function () {
    Plan::factory()->create([
        'name' => 'Plus',
        'slug' => 'plus',
        'currency' => 'EUR',
        'price_cents' => 4900,
        'upload_limit' => 500,
        'retention_days' => 90,
        'upload_window_days' => 30,
        'customization_tier' => 'better',
        'download_all_enabled' => true,
        'moderation_tools_enabled' => false,
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
            ->where('plans.0.name', 'Plus')
            ->where('plans.0.priceLabel', 'EUR 49.00')
            ->where('plans.0.uploadLabel', 'Up to 500 uploads')
            ->where('plans.0.activeWindowLabel', 'Active for 30 days from the event date')
            ->where('plans.0.isDefault', true)
        );
});

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
