<?php

use App\Models\Event;
use App\Models\Plan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia as Assert;

test('super admin can view another users event settings and manage billing', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $owner = User::factory()->create();
    $superAdmin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    $event = Event::factory()->for($owner)->create([
        'is_paid' => false,
        'payment_due_at' => now()->addDays(3),
        'billing_note' => 'Waiting for manual transfer.',
        'storage_limit_bytes' => 10737418240,
        'storage_used_bytes' => 2147483648,
    ]);

    $this->actingAs($superAdmin)
        ->get(route('events.settings', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Settings')
            ->where('currentEvent.billing.canManage', true)
            ->where('currentEvent.billing.isPaid', false)
            ->where('currentEvent.billing.note', 'Waiting for manual transfer.')
            ->where('currentEvent.billing.storage.usedBytes', 2147483648)
            ->where('currentEvent.billing.storage.freeBytes', 8589934592)
            ->where('eventLinks.billingUpdate', route('events.billing.update', $event))
        );
});

test('super admin can update event billing and switch plans', function () {
    CarbonImmutable::setTestNow('2026-03-14 10:30:00');
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $starterPlan = Plan::factory()->create([
        'name' => 'Starter',
        'currency' => 'EUR',
        'price_cents' => 2000,
        'storage_limit_bytes' => 10737418240,
        'upload_limit' => 300,
        'retention_days' => 30,
        'is_active' => true,
    ]);
    $businessPlan = Plan::factory()->create([
        'name' => 'Business',
        'currency' => 'EUR',
        'price_cents' => 5900,
        'storage_limit_bytes' => 21474836480,
        'upload_limit' => 800,
        'retention_days' => 60,
        'video_max_duration_seconds' => 45,
        'is_active' => true,
    ]);
    $owner = User::factory()->create();
    $superAdmin = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    $event = Event::factory()->for($owner)->create([
        'plan_id' => $starterPlan->id,
        'currency' => 'EUR',
        'event_date' => '2026-03-20',
        'upload_window_ends_at' => CarbonImmutable::parse('2026-03-23 23:59:59', 'Europe/Bucharest'),
        'payment_due_at' => CarbonImmutable::parse('2026-03-27 23:59:59', 'Europe/Bucharest'),
        'is_paid' => false,
        'paid_at' => null,
        'retention_ends_at' => null,
    ]);

    $this->actingAs($superAdmin)
        ->patch(route('events.billing.update', $event), [
            'plan_id' => $businessPlan->id,
            'is_paid' => true,
            'payment_due_at' => '2026-03-28',
            'paid_at' => '2026-03-14T10:30',
            'billing_note' => 'Paid via bank transfer.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Billing updated.');

    $event->refresh();

    expect($event->plan_id)->toBe($businessPlan->id)
        ->and($event->currency)->toBe('EUR')
        ->and($event->is_paid)->toBeTrue()
        ->and($event->payment_due_at?->toDateTimeString())->toBe('2026-03-28 23:59:59')
        ->and($event->paid_at?->format('Y-m-d H:i'))->toBe('2026-03-14 10:30')
        ->and($event->billing_note)->toBe('Paid via bank transfer.')
        ->and($event->retention_ends_at?->toDateTimeString())->toBe('2026-05-22 23:59:59')
        ->and($event->storage_limit_bytes)->toBe(21474836480)
        ->and($event->upload_limit)->toBe(800)
        ->and($event->video_max_duration_seconds)->toBe(45)
        ->and($event->status)->toBe(Event::STATUS_SCHEDULED);

    CarbonImmutable::setTestNow();
});

test('event owners cannot see admin billing notes', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'billing_note' => 'Internal admin follow-up.',
    ]);

    $this->actingAs($owner)
        ->get(route('events.settings', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Settings')
            ->where('currentEvent.billing.canManage', false)
            ->where('currentEvent.billing.note', null)
        );
});

test('paid events expose a paid billing state on the event workspace', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'is_paid' => true,
        'paid_at' => now()->subDay(),
        'retention_ends_at' => now()->addDays(30),
    ]);

    $this->actingAs($owner)
        ->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Home')
            ->where('currentEvent.billing.isPaid', true)
            ->where('currentEvent.billing.statusCode', 'paid')
            ->where('currentEvent.billing.statusLabel', 'Paid')
        );
});

test('event owners cannot update billing controls', function () {
    config(['app.super_admin_emails' => ['admin@example.com']]);

    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->patch(route('events.billing.update', $event), [
            'plan_id' => $event->plan_id,
            'is_paid' => true,
            'payment_due_at' => '2026-03-28',
            'paid_at' => '2026-03-14T10:30',
        ])
        ->assertForbidden();
});
