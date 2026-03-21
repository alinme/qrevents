<?php

use App\Models\Event;
use App\Models\EventCollaborator;
use App\Models\User;
use App\Notifications\EventCollaboratorInviteNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

it('stores collaborator as invited and sends invite email', function () {
    Notification::fake();

    $owner = User::factory()->create([
        'email' => 'owner@example.com',
    ]);
    $collaboratorUser = User::factory()->create([
        'email' => 'collab@example.com',
    ]);
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($owner);

    $response = $this->from(route('events.settings', $event))
        ->post(route('events.collaborators.store', $event), [
            'email' => 'COLLAB@example.com',
            'role' => 'manager',
        ]);

    $response->assertRedirect(route('events.settings', $event));
    $response->assertSessionHas(
        'success',
        'Invite sent. Collaborator remains invited until accepting the email link.',
    );

    $this->assertDatabaseHas('event_collaborators', [
        'event_id' => $event->id,
        'email' => 'collab@example.com',
        'user_id' => $collaboratorUser->id,
        'role' => 'manager',
        'status' => 'invited',
        'accepted_at' => null,
    ]);

    Notification::assertSentOnDemand(EventCollaboratorInviteNotification::class);

    $this->get(route('events.settings', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Settings')
            ->where(
                'currentEvent.collaborators',
                fn ($rows) => collect($rows)->contains(fn ($row) => $row['email'] === 'collab@example.com'
                    && $row['role'] === 'manager'
                    && $row['status'] === 'invited'),
            )
        );
});

it('accepts collaborator invite from signed link and activates access', function () {
    Notification::fake();

    $owner = User::factory()->create([
        'email' => 'owner@example.com',
    ]);
    $invitee = User::factory()->create([
        'email' => 'invitee@example.com',
    ]);
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->from(route('events.settings', $event))
        ->post(route('events.collaborators.store', $event), [
            'email' => 'invitee@example.com',
            'role' => 'viewer',
        ])
        ->assertRedirect(route('events.settings', $event));

    $collaborator = EventCollaborator::query()
        ->where('event_id', $event->id)
        ->where('email', 'invitee@example.com')
        ->firstOrFail();
    $acceptUrl = URL::temporarySignedRoute(
        'events.collaborators.accept',
        now()->addDays((int) config('events.collaborator_invite_expires_days', 7)),
        ['collaborator' => $collaborator->id],
    );

    $this->actingAs($invitee)
        ->get($acceptUrl)
        ->assertRedirect(route('events.show', $event));

    $collaborator->refresh();

    expect($collaborator->status)->toBe('active')
        ->and($collaborator->user_id)->toBe($invitee->id)
        ->and($collaborator->accepted_at)->not->toBeNull();

    $this->actingAs($invitee)
        ->get(route('events.show', $event))
        ->assertOk();
});

it('blocks accepting invite with another account email', function () {
    Notification::fake();

    $owner = User::factory()->create([
        'email' => 'owner@example.com',
    ]);
    $invitee = User::factory()->create([
        'email' => 'invitee@example.com',
    ]);
    $otherUser = User::factory()->create([
        'email' => 'other@example.com',
    ]);
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->from(route('events.settings', $event))
        ->post(route('events.collaborators.store', $event), [
            'email' => 'invitee@example.com',
            'role' => 'manager',
        ])
        ->assertRedirect(route('events.settings', $event));

    $collaborator = EventCollaborator::query()
        ->where('event_id', $event->id)
        ->where('email', 'invitee@example.com')
        ->firstOrFail();
    $acceptUrl = URL::temporarySignedRoute(
        'events.collaborators.accept',
        now()->addDays((int) config('events.collaborator_invite_expires_days', 7)),
        ['collaborator' => $collaborator->id],
    );

    $this->actingAs($otherUser)
        ->get($acceptUrl)
        ->assertRedirect(route('dashboard'));

    $collaborator->refresh();
    expect($collaborator->status)->toBe('invited')
        ->and($collaborator->accepted_at)->toBeNull();
});

it('shows invitee onboarding and allows account creation for invited email', function () {
    Notification::fake();

    $owner = User::factory()->create([
        'email' => 'owner@example.com',
    ]);
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->from(route('events.settings', $event))
        ->post(route('events.collaborators.store', $event), [
            'email' => 'invitee@example.com',
            'role' => 'viewer',
        ])
        ->assertRedirect(route('events.settings', $event));

    auth()->logout();

    $collaborator = EventCollaborator::query()
        ->where('event_id', $event->id)
        ->where('email', 'invitee@example.com')
        ->firstOrFail();
    $acceptUrl = URL::temporarySignedRoute(
        'events.collaborators.accept',
        now()->addDays((int) config('events.collaborator_invite_expires_days', 7)),
        ['collaborator' => $collaborator->id],
    );
    $registerUrl = URL::temporarySignedRoute(
        'events.collaborators.complete-register',
        now()->addDays((int) config('events.collaborator_invite_expires_days', 7)),
        ['collaborator' => $collaborator->id],
    );

    $this->get($acceptUrl)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('invitations/AcceptCollaborator')
            ->where('email', 'invitee@example.com')
            ->where('hasAccount', false),
        );

    $this->post($registerUrl, [
        'name' => 'Invitee Name',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])
        ->assertRedirect(route('events.show', $event));

    $collaborator->refresh();
    expect($collaborator->status)->toBe('active')
        ->and($collaborator->user_id)->not->toBeNull();

    $createdInvitee = User::query()
        ->where('email', 'invitee@example.com')
        ->first();
    expect($createdInvitee)->not->toBeNull()
        ->and($createdInvitee?->name)->toBe('Invitee Name');
});

it('shows password login form when invited email already has an account', function () {
    Notification::fake();

    $owner = User::factory()->create([
        'email' => 'owner@example.com',
    ]);
    $invitee = User::factory()->create([
        'email' => 'invitee@example.com',
        'password' => bcrypt('password'),
    ]);
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->from(route('events.settings', $event))
        ->post(route('events.collaborators.store', $event), [
            'email' => 'invitee@example.com',
            'role' => 'viewer',
        ])
        ->assertRedirect(route('events.settings', $event));

    auth()->logout();

    $collaborator = EventCollaborator::query()
        ->where('event_id', $event->id)
        ->where('email', 'invitee@example.com')
        ->firstOrFail();
    $acceptUrl = URL::temporarySignedRoute(
        'events.collaborators.accept',
        now()->addDays((int) config('events.collaborator_invite_expires_days', 7)),
        ['collaborator' => $collaborator->id],
    );
    $loginUrl = URL::temporarySignedRoute(
        'events.collaborators.complete-login',
        now()->addDays((int) config('events.collaborator_invite_expires_days', 7)),
        ['collaborator' => $collaborator->id],
    );

    $this->get($acceptUrl)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('invitations/AcceptCollaborator')
            ->where('email', 'invitee@example.com')
            ->where('hasAccount', true),
        );

    $this->post($loginUrl, [
        'password' => 'password',
    ])->assertRedirect(route('events.show', $event));

    $collaborator->refresh();
    expect($collaborator->status)->toBe('active')
        ->and($collaborator->user_id)->toBe($invitee->id);
});

it('does not allow invited collaborators to access event dashboard before accepting', function () {
    $owner = User::factory()->create();
    $invitee = User::factory()->create([
        'email' => 'invitee@example.com',
    ]);
    $event = Event::factory()->for($owner)->create();
    EventCollaborator::query()->create([
        'event_id' => $event->id,
        'email' => 'invitee@example.com',
        'user_id' => $invitee->id,
        'role' => 'viewer',
        'status' => 'invited',
        'invited_by_user_id' => $owner->id,
        'invited_at' => now(),
    ]);

    $this->actingAs($invitee)
        ->get(route('events.show', $event))
        ->assertForbidden();
});

it('blocks inviting the event owner as collaborator', function () {
    $owner = User::factory()->create([
        'email' => 'owner@example.com',
    ]);
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($owner);

    $response = $this->from(route('events.settings', $event))
        ->post(route('events.collaborators.store', $event), [
            'email' => 'OWNER@example.com',
            'role' => 'manager',
        ]);

    $response->assertRedirect(route('events.settings', $event));
    $response->assertSessionHasErrors('email');

    $this->assertDatabaseMissing('event_collaborators', [
        'event_id' => $event->id,
        'email' => 'owner@example.com',
    ]);
});

it('forbids inviting collaborators on another users event', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($otherUser);

    $response = $this->post(route('events.collaborators.store', $event), [
        'email' => 'collab@example.com',
        'role' => 'manager',
    ]);

    $response->assertForbidden();
});
