<?php

use App\Models\Event;
use App\Models\EventGuestParty;
use App\Models\EventGuestPartyInvitationView;
use App\Models\User;
use App\Notifications\EventGuestInvitationResponseNotification;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

it('allows an event owner to update invitation settings', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'invitation_settings' => null,
    ]);

    $this->actingAs($owner)
        ->patch(route('events.guests.invitation-settings.update', $event), [
            'template' => 'floral',
            'headline' => 'Join us for a beautiful day',
            'message' => 'Please celebrate with us and let us know your answer.',
            'closing' => 'We cannot wait to see you there.',
            'contact_phone' => '0722123456',
            'public_rsvp_enabled' => true,
        ])
        ->assertRedirect();

    $event->refresh();

    expect($event->invitation_settings)->toMatchArray([
        'template' => 'floral',
        'headline' => 'Join us for a beautiful day',
        'message' => 'Please celebrate with us and let us know your answer.',
        'closing' => 'We cannot wait to see you there.',
        'contact_phone' => '0722123456',
        'public_rsvp_enabled' => true,
    ]);
});

it('shows a guest invitation page and records the open', function () {
    $event = Event::factory()->create([
        'branding' => [
            'display_language' => 'en',
        ],
    ]);
    $guestParty = EventGuestParty::factory()->for($event)->create([
        'name' => 'Familia Popescu',
        'invitation_status' => 'sent',
    ]);

    $this->get(route('events.guests.invitation.show', $guestParty->invitation_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('invitations/EventGuestInvite')
            ->where('eventName', $event->name)
            ->where('guestParty.name', 'Familia Popescu')
            ->where('isPublicInvite', false)
        );

    $guestParty->refresh();

    expect($guestParty->invitation_open_count)->toBe(1)
        ->and($guestParty->invitation_status)->toBe('opened')
        ->and(EventGuestPartyInvitationView::query()->where('event_guest_party_id', $guestParty->id)->count())->toBe(1);
});

it('stores an RSVP from a guest-specific invitation', function () {
    Notification::fake();

    $event = Event::factory()->create();
    $owner = $event->user;
    $guestParty = EventGuestParty::factory()->for($event)->create([
        'name' => 'Familia Ionescu',
        'invited_attendees_count' => 3,
    ]);

    $this->post(route('events.guests.invitation.respond', $guestParty->invitation_token), [
        'attendance_status' => 'accepted',
        'confirmed_attendees_count' => 2,
        'guest_names' => 'Marcel, Ana',
        'meal_preference' => 'vegetarian',
        'response_notes' => 'We will arrive after the ceremony.',
    ])->assertRedirect(route('events.guests.invitation.show', [
        'token' => $guestParty->invitation_token,
        'submitted' => 1,
    ]));

    $guestParty->refresh();

    expect($guestParty->attendance_status)->toBe('accepted')
        ->and($guestParty->confirmed_attendees_count)->toBe(2)
        ->and($guestParty->guest_names)->toBe('Marcel, Ana')
        ->and($guestParty->meal_preference)->toBe('vegetarian')
        ->and($guestParty->response_notes)->toBe('We will arrive after the ceremony.')
        ->and($guestParty->invitation_status)->toBe('responded')
        ->and($guestParty->responded_at)->not->toBeNull();

    Notification::assertSentTo($owner, EventGuestInvitationResponseNotification::class, function (
        EventGuestInvitationResponseNotification $notification,
        array $channels,
    ): bool {
        $summary = $notification->toArray(new \stdClass);

        return $channels === ['mail']
            && $summary['guestPartyName'] === 'Familia Ionescu'
            && $summary['changeType'] === 'accepted'
            && $summary['confirmedAttendeesCount'] === 2
            && $summary['pendingPartyCount'] === 0;
    });
});

it('caps a private invitation response to the original invited count', function () {
    $event = Event::factory()->create();
    $guestParty = EventGuestParty::factory()->for($event)->create([
        'name' => 'Familia Stoica',
        'invited_attendees_count' => 3,
    ]);

    $this->post(route('events.guests.invitation.respond', $guestParty->invitation_token), [
        'attendance_status' => 'accepted',
        'confirmed_attendees_count' => 9,
    ])->assertRedirect();

    $guestParty->refresh();

    expect($guestParty->confirmed_attendees_count)->toBe(3);
});

it('creates or updates a guest party from the public invitation page', function () {
    Notification::fake();

    $event = Event::factory()->create([
        'invitation_settings' => [
            'template' => 'classic',
            'public_rsvp_enabled' => true,
        ],
    ]);

    $this->post(route('events.guests.public-invitation.respond', $event->public_invitation_token), [
        'name' => 'Familia Georgescu',
        'phone' => '0711223344',
        'invited_attendees_count' => 4,
        'attendance_status' => 'accepted',
        'confirmed_attendees_count' => 4,
        'guest_names' => 'Mihai, Elena, Vlad, Ioana',
        'meal_preference' => 'halal',
        'response_notes' => 'Please keep us near the family table.',
    ])->assertRedirect(route('events.guests.public-invitation.show', [
        'token' => $event->public_invitation_token,
        'submitted' => 1,
    ]));

    $guestParty = $event->guestParties()->where('phone', '0711223344')->first();

    expect($guestParty)->not->toBeNull()
        ->and($guestParty?->name)->toBe('Familia Georgescu')
        ->and($guestParty?->invited_attendees_count)->toBe(4)
        ->and($guestParty?->attendance_status)->toBe('accepted')
        ->and($guestParty?->confirmed_attendees_count)->toBe(4)
        ->and($guestParty?->meal_preference)->toBe('halal')
        ->and($guestParty?->invitation_delivery_channel)->toBe('public_link')
        ->and($guestParty?->invitation_status)->toBe('responded');

    Notification::assertSentTo($event->user, EventGuestInvitationResponseNotification::class, function (
        EventGuestInvitationResponseNotification $notification,
    ): bool {
        $summary = $notification->toArray(new \stdClass);

        return $summary['guestPartyName'] === 'Familia Georgescu'
            && $summary['changeType'] === 'accepted'
            && $summary['mealPreference'] === 'halal';
    });
});
