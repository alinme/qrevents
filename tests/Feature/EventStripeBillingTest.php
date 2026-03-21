<?php

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
            ->where('currentEvent.billing.checkoutLabel', 'Pay 49.00 EUR')
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
