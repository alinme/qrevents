<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('non-business users cannot access the business dashboard', function () {
    $user = User::factory()->create(['account_type' => User::ACCOUNT_TYPE_USER]);

    $this->actingAs($user)->get(route('dashboard.business'))->assertForbidden();
});

test('business users without onboarding are redirected to onboarding', function () {
    $user = User::factory()->create([
        'account_type' => User::ACCOUNT_TYPE_BUSINESS,
        'business_onboarded_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard.business'))
        ->assertRedirect(route('dashboard.business.onboarding'));
});

test('onboarded business users see the dashboard with a wallet', function () {
    $user = User::factory()->create([
        'account_type' => User::ACCOUNT_TYPE_BUSINESS,
        'business_onboarded_at' => now(),
        'business_profile' => ['company_name' => 'Acme', 'billing_email' => 'acme@example.com'],
        'business_wallet_credits' => 40,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard.business'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('business/Dashboard')
            ->where('wallet.credits', 40)
            ->has('topUp.presets')
            ->has('topUp.tiers')
            ->where('topUp.min', 100)
        );
});

test('wallet top-up quotes apply the volume discount tiers', function () {
    $manager = app(App\Support\BusinessWalletManager::class);

    // €100 → 50% off (€0.50/cr) → 200 credits (100 face + 100 bonus)
    $q100 = $manager->quoteTopUp(100);
    expect($q100['total_credits'])->toBe(200)
        ->and($q100['discount_percent'])->toBe(50)
        ->and($q100['credits_purchased'])->toBe(100)
        ->and($q100['bonus_credits'])->toBe(100);

    // €300 → 55% off (€0.45/cr) → 666 credits
    $q300 = $manager->quoteTopUp(300);
    expect($q300['discount_percent'])->toBe(55)->and($q300['total_credits'])->toBe(666);

    // €3000 → 65% off (€0.35/cr) → 8571 credits
    $q3000 = $manager->quoteTopUp(3000);
    expect($q3000['discount_percent'])->toBe(65)->and($q3000['total_credits'])->toBe(8571);
});

test('wallet top-up rejects amounts below the minimum or off the step', function () {
    $manager = app(App\Support\BusinessWalletManager::class);

    expect(fn () => $manager->quoteTopUp(50))->toThrow(RuntimeException::class);
    expect(fn () => $manager->quoteTopUp(125))->toThrow(RuntimeException::class);
    expect($manager->quoteTopUp(150)['discount_percent'])->toBe(50);
});

test('business onboarding saves the profile and marks it complete', function () {
    $user = User::factory()->create([
        'account_type' => User::ACCOUNT_TYPE_BUSINESS,
        'business_onboarded_at' => null,
    ]);

    $this->actingAs($user)
        ->post(route('dashboard.business.onboarding.store'), [
            'company_name' => 'Acme Ltd',
            'brand_name' => 'Acme',
            'billing_email' => 'billing@acme.com',
        ])
        ->assertRedirect(route('dashboard.business'));

    $fresh = $user->fresh();
    expect($fresh->business_onboarded_at)->not->toBeNull();
    expect($fresh->business_profile['company_name'])->toBe('Acme Ltd');
});

test('the business nav link appears only for business accounts', function () {
    $business = User::factory()->create([
        'account_type' => User::ACCOUNT_TYPE_BUSINESS,
        'business_onboarded_at' => now(),
        'business_profile' => ['company_name' => 'Acme', 'billing_email' => 'a@b.com'],
    ]);

    $this->actingAs($business)
        ->get(route('dashboard.business'))
        ->assertInertia(fn (Assert $page) => $page
            ->where(
                'accountNavigation',
                fn ($items) => collect($items)->contains(
                    fn (array $item): bool => $item['href'] === route('dashboard.business'),
                ),
            )
        );
});
