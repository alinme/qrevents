<?php

use App\Models\Event;
use App\Models\EventAsset;
use App\Models\Plan;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);
});

test('regular users cannot access any admin page', function (string $routeName) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertForbidden();
})->with(['admin.overview', 'admin.events', 'admin.plans']);

test('regular users cannot create or update plans', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.plans.store'), [])
        ->assertForbidden();

    $this->actingAs($user)
        ->patch(route('admin.plans.update', $plan), [])
        ->assertForbidden();
});

test('guests are redirected to login from the admin area', function () {
    $this->get(route('admin.overview'))->assertRedirect(route('login'));
});

test('super admins see platform stats and recent events on the overview', function () {
    $admin = User::factory()->create(['email' => 'admin@example.com']);
    $owner = User::factory()->create(['email' => 'owner@example.com']);
    $plan = Plan::factory()->create([
        'name' => 'Business 49 EUR',
        'currency' => 'EUR',
        'price_cents' => 4900,
    ]);

    $liveEvent = Event::factory()->for($owner)->for($plan)->create([
        'name' => 'Garden Party',
        'status' => Event::STATUS_LIVE,
        'is_paid' => true,
        'paid_at' => now()->subDay(),
        'onboarding_completed_at' => now(),
        'storage_used_bytes' => 2048,
    ]);
    Event::factory()->for($owner)->for($plan)->create([
        'name' => 'Locked Wedding',
        'status' => Event::STATUS_LOCKED,
        'is_paid' => false,
        'onboarding_completed_at' => now(),
        'storage_used_bytes' => 1024,
    ]);

    EventAsset::factory()->count(2)->for($liveEvent)->for($owner)->create();

    $this->actingAs($admin)
        ->get(route('admin.overview'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Overview')
            ->where('sidebarLabel', 'Admin')
            ->has('adminNavigation', 6)
            ->where('summary.totalUsers', 2)
            ->where('summary.totalEvents', 2)
            ->where('summary.totalUploads', 2)
            ->where('summary.storageUsedBytes', 3072)
            ->where(
                'summary.eventsByStatus',
                fn ($statuses) => collect($statuses)
                    ->contains(fn (array $entry): bool => $entry['status'] === Event::STATUS_LOCKED && $entry['count'] === 1),
            )
            ->has('recentEvents', 2)
            ->where('recentEvents.0.name', 'Locked Wedding')
            ->where('recentEvents.0.ownerEmail', 'owner@example.com')
            ->where('recentEvents.0.isPaid', false)
            ->where('recentEvents.1.name', 'Garden Party')
            ->where('recentEvents.1.isPaid', true)
        );
});

test('super admins can browse and search the events table', function () {
    $admin = User::factory()->create(['email' => 'admin@example.com']);
    $owner = User::factory()->create(['email' => 'owner@example.com']);
    $otherOwner = User::factory()->create(['email' => 'other@example.com']);
    $plan = Plan::factory()->create();

    Event::factory()->for($owner)->for($plan)->create(['name' => 'Seaside Wedding']);
    Event::factory()->for($otherOwner)->for($plan)->create(['name' => 'City Birthday']);

    $this->actingAs($admin)
        ->get(route('admin.events'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Events')
            ->has('events', 2)
            ->where('pagination.total', 2)
        );

    $this->actingAs($admin)
        ->get(route('admin.events', ['search' => 'owner@example.com']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Events')
            ->has('events', 1)
            ->where('events.0.name', 'Seaside Wedding')
            ->where('events.0.ownerEmail', 'owner@example.com')
            ->where('filters.search', 'owner@example.com')
        );
});

test('super admins can create and update plans', function () {
    $admin = User::factory()->create(['email' => 'admin@example.com']);
    $freePlan = Plan::factory()->create([
        'currency' => 'EUR',
        'is_default' => true,
        'is_active' => true,
    ]);

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
            'is_active' => true,
            'is_default' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Plus package created.');

    $plusPlan = Plan::query()->where('slug', 'plus')->firstOrFail();

    expect($plusPlan->price_cents)->toBe(4900)
        ->and($plusPlan->storage_limit_bytes)->toBe(12884901888)
        ->and($plusPlan->upload_limit)->toBe(500)
        ->and($plusPlan->customization_tier)->toBe('better')
        ->and($plusPlan->download_all_enabled)->toBeTrue()
        ->and($plusPlan->is_default)->toBeTrue()
        ->and($freePlan->fresh()->is_default)->toBeFalse();

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
        ->and($plusPlan->retention_days)->toBe(365)
        ->and($plusPlan->customization_tier)->toBe('advanced')
        ->and($plusPlan->moderation_tools_enabled)->toBeTrue()
        ->and($plusPlan->remove_app_branding)->toBeTrue()
        ->and($plusPlan->photo_max_size_bytes)->toBe(31457280)
        ->and($plusPlan->video_max_size_bytes)->toBe(1073741824)
        ->and($plusPlan->is_default)->toBeTrue();
});

test('a default plan cannot be deactivated without clearing the default flag', function () {
    $admin = User::factory()->create(['email' => 'admin@example.com']);
    $plan = Plan::factory()->create([
        'currency' => 'EUR',
        'is_default' => true,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.plans.update', $plan), [
            'name' => $plan->name,
            'slug' => $plan->slug,
            'currency' => 'EUR',
            'price_cents' => 1000,
            'storage_limit_gb' => 5,
            'upload_limit' => 100,
            'retention_days' => 30,
            'grace_days' => 7,
            'upload_window_days' => 30,
            'customization_tier' => 'basic',
            'video_max_duration_seconds' => 30,
            'photo_max_size_mb' => 25,
            'video_max_size_mb' => 500,
            'is_active' => false,
            'is_default' => true,
        ])
        ->assertSessionHasErrors('is_active');
});

test('the account sidebar advertises the admin area only to super admins', function () {
    $admin = User::factory()->create(['email' => 'admin@example.com']);
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    Event::factory()->for($admin)->for($plan)->create();
    Event::factory()->for($user)->for($plan)->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where(
                'accountNavigation',
                fn ($items) => collect($items)->contains(
                    fn (array $item): bool => $item['href'] === route('admin.overview'),
                ),
            )
        );

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where(
                'accountNavigation',
                fn ($items) => ! collect($items)->contains(
                    fn (array $item): bool => $item['href'] === route('admin.overview'),
                ),
            )
        );
});
