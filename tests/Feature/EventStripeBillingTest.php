<?php

use App\Models\BusinessWalletPurchase;
use App\Models\Event;
use App\Models\Plan;
use App\Models\User;
use App\Support\StripeCheckoutGateway;
use Inertia\Testing\AssertableInertia as Assert;

test('event owners can see online checkout when stripe billing is configured', function () {
    config(['services.stripe.secret' => 'sk_test_123']);

    $owner = User::factory()->create();
    $plan = Plan::factory()->create([
        'name' => 'Business 49 EUR',
        'currency' => 'EUR',
        'price_cents' => 4900,
        'is_active' => true,
    ]);
    $event = Event::factory()->for($owner)->for($plan)->create([
        'is_paid' => false,
    ]);

    $this->actingAs($owner)
        ->get(route('events.settings', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Settings')
            ->where('currentEvent.billing.canManage', false)
            ->where('currentEvent.billing.canCheckout', true)
            ->where('currentEvent.billing.checkoutLabel', 'Choose plan')
            ->where('currentEvent.billing.checkoutPlanIds', [$plan->id])
            ->where('eventLinks.billingCheckout', route('events.billing.checkout', $event))
        );
});

test('event owners can start a stripe checkout session for unpaid events', function () {
    config(['services.stripe.secret' => 'sk_test_123']);

    $owner = User::factory()->create();
    $plan = Plan::factory()->create([
        'name' => 'Business 49 EUR',
        'currency' => 'EUR',
        'price_cents' => 4900,
        'is_active' => true,
    ]);
    $event = Event::factory()->for($owner)->for($plan)->create([
        'is_paid' => false,
    ]);

    $gateway = \Mockery::mock(StripeCheckoutGateway::class);
    $gateway->shouldReceive('isConfigured')->once()->andReturn(true);
    $gateway->shouldReceive('createCheckoutSession')
        ->once()
        ->with(\Mockery::on(function (array $payload) use ($event, $plan): bool {
            return $payload['mode'] === 'payment'
                && $payload['metadata']['event_id'] === (string) $event->id
                && $payload['metadata']['plan_id'] === (string) $plan->id
                && $payload['line_items'][0]['price_data']['unit_amount'] === 4900;
        }))
        ->andReturn([
            'id' => 'cs_test_123',
            'url' => 'https://checkout.stripe.test/session/cs_test_123',
        ]);
    app()->instance(StripeCheckoutGateway::class, $gateway);

    $this->actingAs($owner)
        ->post(route('events.billing.checkout', $event), [], [
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', 'https://checkout.stripe.test/session/cs_test_123');
});

test('event owners can start checkout for a higher plan from a free event', function () {
    config(['services.stripe.secret' => 'sk_test_123']);

    $owner = User::factory()->create();
    $freePlan = Plan::factory()->create([
        'name' => 'Free',
        'currency' => 'EUR',
        'price_cents' => 0,
        'is_active' => true,
    ]);
    $plusPlan = Plan::factory()->create([
        'name' => 'Plus',
        'currency' => 'EUR',
        'price_cents' => 2500,
        'is_active' => true,
    ]);
    $event = Event::factory()->for($owner)->for($freePlan)->create([
        'is_paid' => false,
    ]);

    $gateway = \Mockery::mock(StripeCheckoutGateway::class);
    $gateway->shouldReceive('isConfigured')->once()->andReturn(true);
    $gateway->shouldReceive('createCheckoutSession')
        ->once()
        ->with(\Mockery::on(function (array $payload) use ($plusPlan): bool {
            return $payload['metadata']['plan_id'] === (string) $plusPlan->id
                && $payload['line_items'][0]['price_data']['unit_amount'] === 2500;
        }))
        ->andReturn([
            'id' => 'cs_test_upgrade_from_free',
            'url' => 'https://checkout.stripe.test/session/cs_test_upgrade_from_free',
        ]);
    app()->instance(StripeCheckoutGateway::class, $gateway);

    $this->actingAs($owner)
        ->post(route('events.billing.checkout', $event), [
            'plan_id' => $plusPlan->id,
        ], [
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', 'https://checkout.stripe.test/session/cs_test_upgrade_from_free');
});

test('event owners upgrading a paid event only pay the difference', function () {
    config(['services.stripe.secret' => 'sk_test_123']);

    $owner = User::factory()->create();
    $plusPlan = Plan::factory()->create([
        'name' => 'Plus',
        'currency' => 'EUR',
        'price_cents' => 2500,
        'is_active' => true,
    ]);
    $proPlan = Plan::factory()->create([
        'name' => 'Pro',
        'currency' => 'EUR',
        'price_cents' => 5000,
        'is_active' => true,
    ]);
    $event = Event::factory()->for($owner)->for($plusPlan)->create([
        'is_paid' => true,
        'paid_at' => now()->subDay(),
    ]);

    $gateway = \Mockery::mock(StripeCheckoutGateway::class);
    $gateway->shouldReceive('isConfigured')->once()->andReturn(true);
    $gateway->shouldReceive('createCheckoutSession')
        ->once()
        ->with(\Mockery::on(function (array $payload) use ($event, $proPlan): bool {
            return $payload['metadata']['plan_id'] === (string) $proPlan->id
                && $payload['metadata']['billing_mode'] === 'upgrade'
                && $payload['metadata']['credited_amount_cents'] === '2500'
                && $payload['line_items'][0]['price_data']['unit_amount'] === 2500
                && $payload['line_items'][0]['price_data']['product_data']['name'] === "Pro upgrade - {$event->name}";
        }))
        ->andReturn([
            'id' => 'cs_test_plus_to_pro',
            'url' => 'https://checkout.stripe.test/session/cs_test_plus_to_pro',
        ]);
    app()->instance(StripeCheckoutGateway::class, $gateway);

    $this->actingAs($owner)
        ->post(route('events.billing.checkout', $event), [
            'plan_id' => $proPlan->id,
        ], [
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', 'https://checkout.stripe.test/session/cs_test_plus_to_pro');
});

test('stripe webhook marks the event paid after checkout completion', function () {
    $owner = User::factory()->create();
    $plan = Plan::factory()->create([
        'name' => 'Business 49 EUR',
        'currency' => 'EUR',
        'price_cents' => 4900,
        'retention_days' => 45,
        'is_active' => true,
    ]);
    $event = Event::factory()->for($owner)->for($plan)->create([
        'is_paid' => false,
        'paid_at' => null,
        'retention_ends_at' => null,
        'currency' => 'EUR',
    ]);

    $gateway = \Mockery::mock(StripeCheckoutGateway::class);
    $gateway->shouldReceive('parseWebhookEvent')
        ->once()
        ->with('{"id":"evt_test"}', 'sig_test_123')
        ->andReturn([
            'type' => 'checkout.session.completed',
            'sessionId' => 'cs_test_123',
            'paymentStatus' => 'paid',
            'paymentIntentId' => 'pi_test_123',
            'amountTotal' => 4900,
            'currency' => 'eur',
            'metadata' => [
                'event_id' => (string) $event->id,
                'plan_id' => (string) $plan->id,
                'owner_id' => (string) $owner->id,
            ],
        ]);
    app()->instance(StripeCheckoutGateway::class, $gateway);

    $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => 'sig_test_123',
        ],
        '{"id":"evt_test"}',
    )
        ->assertOk()
        ->assertJson(['received' => true]);

    $event->refresh();

    expect($event->is_paid)->toBeTrue()
        ->and($event->stripe_checkout_session_id)->toBe('cs_test_123')
        ->and($event->stripe_payment_intent_id)->toBe('pi_test_123')
        ->and($event->stripe_paid_amount_cents)->toBe(4900)
        ->and($event->stripe_paid_currency)->toBe('EUR')
        ->and($event->payment_due_at)->toBeNull()
        ->and($event->paid_at)->not->toBeNull()
        ->and($event->retention_ends_at)->not->toBeNull();
});

test('stripe webhook retries keep the original payment timestamp', function () {
    $owner = User::factory()->create();
    $plan = Plan::factory()->create([
        'name' => 'Business 49 EUR',
        'currency' => 'EUR',
        'price_cents' => 4900,
        'retention_days' => 45,
        'is_active' => true,
    ]);
    $event = Event::factory()->for($owner)->for($plan)->create([
        'is_paid' => false,
        'paid_at' => null,
        'payment_due_at' => now()->addDays(5),
        'retention_ends_at' => null,
        'currency' => 'EUR',
    ]);

    $gateway = \Mockery::mock(StripeCheckoutGateway::class);
    $gateway->shouldReceive('parseWebhookEvent')
        ->twice()
        ->andReturn([
            'type' => 'checkout.session.completed',
            'sessionId' => 'cs_test_456',
            'paymentStatus' => 'paid',
            'paymentIntentId' => 'pi_test_456',
            'amountTotal' => 4900,
            'currency' => 'eur',
            'metadata' => [
                'event_id' => (string) $event->id,
                'plan_id' => (string) $plan->id,
                'owner_id' => (string) $owner->id,
            ],
        ]);
    app()->instance(StripeCheckoutGateway::class, $gateway);

    $headers = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => 'sig_test_retry',
    ];

    $this->call('POST', route('stripe.webhook'), [], [], [], $headers, '{"id":"evt_retry_1"}')
        ->assertOk();

    $event->refresh();
    $firstPaidAt = $event->paid_at;

    expect($firstPaidAt)->not->toBeNull()
        ->and($event->payment_due_at)->toBeNull();

    $this->travel(10)->minutes();

    $this->call('POST', route('stripe.webhook'), [], [], [], $headers, '{"id":"evt_retry_2"}')
        ->assertOk();

    $event->refresh();

    expect($event->paid_at?->toIso8601String())->toBe($firstPaidAt?->toIso8601String())
        ->and($event->stripe_checkout_session_id)->toBe('cs_test_456')
        ->and($event->stripe_payment_intent_id)->toBe('pi_test_456');
});

test('stripe webhook parser preserves checkout session metadata', function () {
    config(['services.stripe.webhook_secret' => 'whsec_test_123']);

    $payload = json_encode([
        'id' => 'evt_test',
        'object' => 'event',
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test_789',
                'object' => 'checkout.session',
                'payment_status' => 'paid',
                'payment_intent' => 'pi_test_789',
                'amount_total' => 2000,
                'currency' => 'eur',
                'metadata' => [
                    'plan_id' => '1',
                    'event_id' => '1',
                    'owner_id' => '1',
                ],
            ],
        ],
    ], JSON_THROW_ON_ERROR);

    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test_123');

    $parsed = app(StripeCheckoutGateway::class)->parseWebhookEvent(
        $payload,
        "t={$timestamp},v1={$signature}",
    );

    expect($parsed)->toMatchArray([
        'type' => 'checkout.session.completed',
        'sessionId' => 'cs_test_789',
        'paymentStatus' => 'paid',
        'paymentIntentId' => 'pi_test_789',
        'amountTotal' => 2000,
        'currency' => 'eur',
        'metadata' => [
            'plan_id' => '1',
            'event_id' => '1',
            'owner_id' => '1',
        ],
    ]);
});

test('stripe webhook fulfills business wallet top-ups with locked purchase data', function () {
    $user = User::factory()->business()->create([
        'business_wallet_credits' => 0,
    ]);

    $purchase = BusinessWalletPurchase::query()->create([
        'user_id' => $user->id,
        'credits_purchased' => 100,
        'bonus_credits' => 25,
        'total_credits' => 125,
        'base_amount_cents' => 10000,
        'checkout_currency' => 'RON',
        'localized_amount_cents' => 49500,
        'locked_fx_rate' => 4.95,
        'status' => 'pending',
        'metadata' => [
            'pack' => 100,
        ],
    ]);

    $gateway = \Mockery::mock(StripeCheckoutGateway::class);
    $gateway->shouldReceive('parseWebhookEvent')
        ->once()
        ->with('{"id":"evt_wallet"}', 'sig_wallet_123')
        ->andReturn([
            'type' => 'checkout.session.completed',
            'sessionId' => 'cs_wallet_123',
            'paymentStatus' => 'paid',
            'paymentIntentId' => 'pi_wallet_123',
            'amountTotal' => 49500,
            'currency' => 'ron',
            'metadata' => [
                'scope' => 'business_wallet',
                'purchase_id' => (string) $purchase->id,
                'user_id' => (string) $user->id,
            ],
        ]);
    app()->instance(StripeCheckoutGateway::class, $gateway);

    $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => 'sig_wallet_123',
        ],
        '{"id":"evt_wallet"}',
    )->assertOk()->assertJson(['received' => true]);

    $purchase->refresh();

    expect($purchase->status)->toBe('paid')
        ->and($purchase->stripe_checkout_session_id)->toBe('cs_wallet_123')
        ->and($purchase->stripe_payment_intent_id)->toBe('pi_wallet_123')
        ->and($purchase->paid_at)->not->toBeNull()
        ->and($user->fresh()->business_wallet_credits)->toBe(125)
        ->and($user->businessWalletTransactions()->where('kind', 'top_up')->sum('credits'))->toBe(100)
        ->and($user->businessWalletTransactions()->where('kind', 'bonus')->sum('credits'))->toBe(25);
});
