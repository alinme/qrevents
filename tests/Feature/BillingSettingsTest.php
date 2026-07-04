<?php

use App\Models\Event;
use App\Models\Plan;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('users can view their billing history', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Plus', 'currency' => 'EUR', 'price_cents' => 4900]);
    Event::factory()->for($user)->for($plan)->create([
        'name' => 'Wedding',
        'is_paid' => true,
        'paid_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('billing.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Billing')
            ->has('events', 1)
            ->where('events.0.name', 'Wedding')
            ->where('events.0.isPaid', true)
            ->where('events.0.statusLabel', 'Paid')
        );
});

test('guests cannot view billing history', function () {
    $this->get(route('billing.show'))->assertRedirect(route('login'));
});
