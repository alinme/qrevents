<?php

use App\Models\BusinessWalletPurchase;
use App\Models\ExchangeRate;
use App\Models\User;
use App\Support\StripeCheckoutGateway;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('users can switch to business, finish onboarding, and reach the business dashboard', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('dashboard.business.activate'))
        ->assertRedirect(route('dashboard.business.onboarding'));

    expect($user->fresh()->account_type)->toBe(User::ACCOUNT_TYPE_BUSINESS)
        ->and($user->fresh()->business_onboarded_at)->toBeNull();

    $this->actingAs($user)
        ->get(route('dashboard.business.onboarding'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('business/Onboarding')
            ->where('profile.billingEmail', $user->email)
            ->where('cancelUrl', route('dashboard.business.onboarding.cancel'))
        );

    $this->actingAs($user)
        ->post(route('dashboard.business.onboarding.store'), [
            'company_name' => 'Studio Events SRL',
            'brand_name' => 'Studio Events',
            'billing_email' => 'billing@studio-events.test',
            'phone' => '+40 721 000 111',
            'website' => 'studio-events.test',
            'primary_color' => '#171411',
            'accent_color' => '#D97706',
            'logo_file' => UploadedFile::fake()->image('logo.png'),
        ])
        ->assertRedirect(route('dashboard.business'));

    $user->refresh();

    expect($user->business_onboarded_at)->not->toBeNull()
        ->and($user->business_profile)->toMatchArray([
            'company_name' => 'Studio Events SRL',
            'brand_name' => 'Studio Events',
            'billing_email' => 'billing@studio-events.test',
            'phone' => '+40 721 000 111',
            'website' => 'https://studio-events.test',
            'primary_color' => '#171411',
            'accent_color' => '#D97706',
        ])
        ->and($user->canAccessBusinessDashboard())->toBeTrue();

    Storage::disk('public')->assertExists((string) ($user->business_profile['logo_path'] ?? ''));
});

test('users can cancel business onboarding before saving their business profile', function () {
    $user = User::factory()->create([
        'account_type' => User::ACCOUNT_TYPE_BUSINESS,
        'business_onboarded_at' => null,
    ]);

    $this->actingAs($user)
        ->post(route('dashboard.business.onboarding.cancel'))
        ->assertRedirect(route('dashboard.account'))
        ->assertSessionHas('success', 'Business upgrade cancelled.');

    $user->refresh();

    expect($user->account_type)->toBe(User::ACCOUNT_TYPE_USER)
        ->and($user->business_onboarded_at)->toBeNull();
});

test('business wallet checkout locks the latest fx rate at session creation', function () {
    config(['services.stripe.secret' => 'sk_test_123']);

    ExchangeRate::query()->create([
        'base_currency' => 'EUR',
        'quote_currency' => 'RON',
        'rate' => 4.95,
        'fetched_at' => now(),
    ]);

    $user = User::factory()->business()->create([
        'business_wallet_credits' => 0,
    ]);

    $gateway = \Mockery::mock(StripeCheckoutGateway::class);
    $gateway->shouldReceive('isConfigured')->once()->andReturn(true);
    $gateway->shouldReceive('createCheckoutSession')
        ->once()
        ->with(\Mockery::on(function (array $payload) use ($user): bool {
            return $payload['mode'] === 'payment'
                && $payload['metadata']['scope'] === 'business_wallet'
                && $payload['metadata']['user_id'] === (string) $user->id
                && $payload['line_items'][0]['price_data']['currency'] === 'ron'
                && $payload['line_items'][0]['price_data']['unit_amount'] === 49500;
        }))
        ->andReturn([
            'id' => 'cs_business_wallet_123',
            'url' => 'https://checkout.stripe.test/session/cs_business_wallet_123',
        ]);
    app()->instance(StripeCheckoutGateway::class, $gateway);

    $this->actingAs($user)
        ->post(route('dashboard.business.wallet.checkout'), [
            'credits' => 100,
            'currency' => 'RON',
        ], [
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', 'https://checkout.stripe.test/session/cs_business_wallet_123');

    $purchase = BusinessWalletPurchase::query()->latest('id')->first();

    expect($purchase)->not->toBeNull()
        ->and($purchase?->user_id)->toBe($user->id)
        ->and($purchase?->credits_purchased)->toBe(100)
        ->and($purchase?->bonus_credits)->toBe(25)
        ->and($purchase?->total_credits)->toBe(125)
        ->and($purchase?->base_amount_cents)->toBe(10000)
        ->and($purchase?->checkout_currency)->toBe('RON')
        ->and((int) $purchase?->localized_amount_cents)->toBe(49500)
        ->and((float) $purchase?->locked_fx_rate)->toBe(4.95)
        ->and($purchase?->stripe_checkout_session_id)->toBe('cs_business_wallet_123');
});

test('business page exposes localized top-up prices and the right entry points', function () {
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

    $user = User::factory()->business()->create([
        'business_onboarded_at' => now(),
        'business_wallet_credits' => 125,
    ]);

    $this->actingAs($user)
        ->get(route('businesses'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Businesses')
            ->where('supportedCheckoutCurrencies', ['EUR', 'RON', 'GBP'])
            ->where('businessPacks.2.credits', 100)
            ->where('businessPacks.2.total_credits', 125)
            ->where('businessPacks.2.priceLabels.EUR', 'EUR 100.00')
            ->where('businessPacks.2.priceLabels.RON', 'RON 495.00')
            ->where('businessPacks.2.priceLabels.GBP', 'GBP 86.00')
            ->where('topUpUrl', route('dashboard.business.wallet.checkout'))
            ->where('dashboardUrl', route('dashboard.business'))
        );
});
