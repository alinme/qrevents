<?php

use App\Models\BusinessWalletTransaction;
use App\Models\Event;
use App\Models\Plan;
use App\Models\User;

function businessOwner(int $credits = 50): User
{
    return User::factory()->create([
        'account_type' => User::ACCOUNT_TYPE_BUSINESS,
        'business_onboarded_at' => now(),
        'business_profile' => ['company_name' => 'Acme', 'billing_email' => 'a@b.com'],
        'business_wallet_credits' => $credits,
    ]);
}

function businessPlan(int $cost = 25): Plan
{
    return Plan::factory()->create([
        'is_active' => true,
        'business_enabled' => true,
        'business_credit_cost' => $cost,
    ]);
}

test('a business owner can pay for an event with wallet credits', function () {
    $user = businessOwner(50);
    $freePlan = Plan::factory()->create(['price_cents' => 0]);
    $plan = businessPlan(25);
    $event = Event::factory()->for($user)->for($freePlan)->create([
        'is_paid' => false,
        'event_date' => now()->addMonth(),
    ]);

    $this->actingAs($user)
        ->post(route('events.billing.pay-credits', $event), ['plan_id' => $plan->id])
        ->assertRedirect()
        ->assertSessionHas('success');

    $event->refresh();
    expect($event->is_paid)->toBeTrue();
    expect($event->plan_id)->toBe($plan->id);
    expect($user->fresh()->business_wallet_credits)->toBe(25);
    expect(
        BusinessWalletTransaction::query()
            ->where('event_id', $event->id)
            ->where('kind', 'event_debit')
            ->count()
    )->toBe(1);
});

test('a non-owner cannot pay for an event with credits', function () {
    $owner = businessOwner();
    $other = businessOwner();
    $plan = businessPlan(25);
    $freePlan = Plan::factory()->create(['price_cents' => 0]);
    $event = Event::factory()->for($owner)->for($freePlan)->create(['is_paid' => false]);

    $this->actingAs($other)
        ->post(route('events.billing.pay-credits', $event), ['plan_id' => $plan->id])
        ->assertForbidden();
});

test('cannot pay when credits are insufficient', function () {
    $user = businessOwner(10);
    $plan = businessPlan(25);
    $freePlan = Plan::factory()->create(['price_cents' => 0]);
    $event = Event::factory()->for($user)->for($freePlan)->create([
        'is_paid' => false,
        'event_date' => now()->addMonth(),
    ]);

    $this->actingAs($user)
        ->post(route('events.billing.pay-credits', $event), ['plan_id' => $plan->id])
        ->assertSessionHas('error');

    expect($event->fresh()->is_paid)->toBeFalse();
    expect($user->fresh()->business_wallet_credits)->toBe(10);
});

test('a non-business plan cannot be paid with credits', function () {
    $user = businessOwner(50);
    $nonBusinessPlan = Plan::factory()->create([
        'is_active' => true,
        'business_enabled' => false,
        'business_credit_cost' => null,
    ]);
    $freePlan = Plan::factory()->create(['price_cents' => 0]);
    $event = Event::factory()->for($user)->for($freePlan)->create(['is_paid' => false]);

    $this->actingAs($user)
        ->post(route('events.billing.pay-credits', $event), ['plan_id' => $nonBusinessPlan->id])
        ->assertSessionHas('error');

    expect($event->fresh()->is_paid)->toBeFalse();
});
