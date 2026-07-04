<?php

use App\Http\Controllers\Admin\ImpersonationController;
use App\Models\AdminAuditLog;
use App\Models\Event;
use App\Models\Plan;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['app.super_admin_emails' => ['root@example.com']]);
});

function makeAdmin(): User
{
    return User::factory()->create([
        'email' => 'root@example.com',
        'account_type' => User::ACCOUNT_TYPE_SUPER_ADMIN,
    ]);
}

test('regular users cannot access admin user management', function (string $routeName) {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route($routeName))->assertForbidden();
})->with(['admin.users']);

test('super admins can list and search users', function () {
    $admin = makeAdmin();
    User::factory()->create(['name' => 'Alice Baker', 'email' => 'alice@example.com']);
    User::factory()->create(['name' => 'Bob Carter', 'email' => 'bob@example.com']);

    $this->actingAs($admin)
        ->get(route('admin.users'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Users')
            ->where('pagination.total', 3)
            ->has('adminNavigation', 5)
        );

    $this->actingAs($admin)
        ->get(route('admin.users', ['search' => 'alice@example.com']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('users', 1)
            ->where('users.0.email', 'alice@example.com')
        );
});

test('super admins can filter users by account type', function () {
    $admin = makeAdmin();
    User::factory()->create(['account_type' => User::ACCOUNT_TYPE_BUSINESS]);
    User::factory()->create(['account_type' => User::ACCOUNT_TYPE_USER]);

    $this->actingAs($admin)
        ->get(route('admin.users', ['type' => 'business']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('users', 1)
            ->where('users.0.accountType', 'business')
        );
});

test('super admins can change a user account type and it is audited', function () {
    $admin = makeAdmin();
    $user = User::factory()->create(['account_type' => User::ACCOUNT_TYPE_USER]);

    $this->actingAs($admin)
        ->patch(route('admin.users.account-type', $user), ['account_type' => 'business'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->account_type)->toBe('business');
    expect(AdminAuditLog::query()->where('action', 'user.account_type.updated')->count())->toBe(1);
});

test('super admins cannot change their own account type', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->patch(route('admin.users.account-type', $admin), ['account_type' => 'user'])
        ->assertForbidden();

    expect($admin->fresh()->account_type)->toBe(User::ACCOUNT_TYPE_SUPER_ADMIN);
});

test('an invalid account type is rejected', function () {
    $admin = makeAdmin();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.account-type', $user), ['account_type' => 'wizard'])
        ->assertSessionHasErrors('account_type');
});

test('super admins can mark a user as verified', function () {
    $admin = makeAdmin();
    $user = User::factory()->unverified()->create();

    $this->actingAs($admin)
        ->post(route('admin.users.verify', $user))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('super admins can delete a user and their events', function () {
    $admin = makeAdmin();
    $plan = Plan::factory()->create();
    $user = User::factory()->create();
    $event = Event::factory()->for($user)->for($plan)->create();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $user))
        ->assertRedirect(route('admin.users'))
        ->assertSessionHas('success');

    expect(User::query()->find($user->id))->toBeNull();
    expect(Event::query()->withTrashed()->find($event->id))->toBeNull();
    expect(AdminAuditLog::query()->where('action', 'user.deleted')->count())->toBe(1);
});

test('super admins cannot delete themselves', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin))
        ->assertSessionHas('error');

    expect(User::query()->find($admin->id))->not->toBeNull();
});

test('super admins can impersonate a regular user and return', function () {
    $admin = makeAdmin();
    $user = User::factory()->create(['email' => 'guest@example.com']);

    $this->actingAs($admin)
        ->post(route('admin.impersonate.start', $user))
        ->assertRedirect(route('dashboard'));

    expect(session(ImpersonationController::SESSION_KEY))->toBe($admin->id);
    $this->assertAuthenticatedAs($user->fresh());
    expect(AdminAuditLog::query()->where('action', 'user.impersonation.started')->count())->toBe(1);

    $this->post(route('impersonate.stop'))->assertRedirect();

    $this->assertAuthenticatedAs($admin->fresh());
    expect(session(ImpersonationController::SESSION_KEY))->toBeNull();
});

test('super admins cannot be impersonated', function () {
    $admin = makeAdmin();
    $otherAdmin = User::factory()->create(['account_type' => User::ACCOUNT_TYPE_SUPER_ADMIN]);

    $this->actingAs($admin)
        ->post(route('admin.impersonate.start', $otherAdmin))
        ->assertForbidden();
});

test('regular users cannot impersonate anyone', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.impersonate.start', $target))
        ->assertForbidden();
});
