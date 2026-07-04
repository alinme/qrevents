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
            ->has('topUpPacks')
        );
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
