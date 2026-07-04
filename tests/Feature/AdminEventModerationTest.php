<?php

use App\Models\AdminAuditLog;
use App\Models\Event;
use App\Models\Plan;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['app.super_admin_emails' => ['root@example.com']]);
});

function moderationAdmin(): User
{
    return User::factory()->create([
        'email' => 'root@example.com',
        'account_type' => User::ACCOUNT_TYPE_SUPER_ADMIN,
    ]);
}

test('regular users cannot moderate events', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $event = Event::factory()->for($user)->for($plan)->create();

    $this->actingAs($user)->post(route('admin.events.suspend', $event))->assertForbidden();
    $this->actingAs($user)->delete(route('admin.events.destroy', $event))->assertForbidden();
});

test('super admins can suspend an event', function () {
    $admin = moderationAdmin();
    $plan = Plan::factory()->create();
    $event = Event::factory()->for(User::factory())->for($plan)->create([
        'status' => Event::STATUS_LIVE,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.events.suspend', $event))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($event->fresh()->status)->toBe(Event::STATUS_LOCKED);
    expect(AdminAuditLog::query()->where('action', 'event.suspended')->count())->toBe(1);
});

test('extending a locked event pushes deadlines and restores access', function () {
    $admin = moderationAdmin();
    $plan = Plan::factory()->create();
    $retention = now()->addDays(2);
    $event = Event::factory()->for(User::factory())->for($plan)->create([
        'status' => Event::STATUS_LOCKED,
        'retention_ends_at' => $retention,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.events.extend', $event), ['days' => 30])
        ->assertRedirect()
        ->assertSessionHas('success');

    $event->refresh();
    expect($event->status)->toBe(Event::STATUS_GRACE);
    expect($event->retention_ends_at->toDateString())->toBe($retention->copy()->addDays(30)->toDateString());
});

test('extend validates the days value', function () {
    $admin = moderationAdmin();
    $plan = Plan::factory()->create();
    $event = Event::factory()->for(User::factory())->for($plan)->create();

    $this->actingAs($admin)
        ->patch(route('admin.events.extend', $event), ['days' => 0])
        ->assertSessionHasErrors('days');
});

test('super admins can delete an event', function () {
    $admin = moderationAdmin();
    $plan = Plan::factory()->create();
    $event = Event::factory()->for(User::factory())->for($plan)->create();

    $this->actingAs($admin)
        ->delete(route('admin.events.destroy', $event))
        ->assertRedirect(route('admin.events'))
        ->assertSessionHas('success');

    expect(Event::query()->withTrashed()->find($event->id))->toBeNull();
    expect(AdminAuditLog::query()->where('action', 'event.deleted')->count())->toBe(1);
});

test('super admins can view the audit log', function () {
    $admin = moderationAdmin();
    $plan = Plan::factory()->create();
    $event = Event::factory()->for(User::factory())->for($plan)->create();

    $this->actingAs($admin)->post(route('admin.events.suspend', $event));

    $this->actingAs($admin)
        ->get(route('admin.audit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/AuditLog')
            ->where('pagination.total', 1)
            ->where('entries.0.action', 'event.suspended')
        );
});
