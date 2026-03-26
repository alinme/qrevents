<?php

use App\Models\Event;
use App\Models\EventGuestParty;
use App\Models\EventGuestPartyInvitationActivity;
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
            'template' => 'canva_cream',
            'headline' => 'Join us for a beautiful day',
            'message' => 'Please celebrate with us and let us know your answer.',
            'closing' => 'We cannot wait to see you there.',
            'contact_phone' => '0722123456',
            'public_rsvp_enabled' => true,
        ])
        ->assertRedirect();

    $event->refresh();

    expect($event->invitation_settings)->toMatchArray([
        'template' => 'canva_cream',
        'headline' => 'Join us for a beautiful day',
        'message' => 'Please celebrate with us and let us know your answer.',
        'closing' => 'We cannot wait to see you there.',
        'contact_phone' => '0722123456',
        'public_rsvp_enabled' => true,
    ]);
});

it('shows a guest invitation page and records the open', function () {
    $event = Event::factory()->create([
        'type' => 'wedding',
        'invitation_settings' => [
            'template' => 'canva_cream',
        ],
        'branding' => [
            'display_language' => 'en',
            'wedding_details' => [
                'partner_one_name' => 'Jessica',
                'partner_two_name' => 'Simon',
                'family_name' => 'Miller',
                'show_family_name' => true,
                'bride_parents' => 'Mary and John Miller',
                'groom_parents' => 'Laura and David Webb',
                'godparents' => 'Bianca and Stefan',
            ],
        ],
        'sub_events' => [
            [
                'key' => 'civil-ceremony',
                'label' => 'Civil union',
                'date' => '2026-06-10',
                'start_time' => '13:00',
                'address' => 'City Hall, Bucharest',
                'no_address' => false,
            ],
            [
                'key' => 'reception',
                'label' => 'Reception',
                'date' => '2026-06-10',
                'start_time' => '18:00',
                'address' => 'Sun Garden Resort, Bucharest',
                'no_address' => false,
            ],
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
            ->where('invitation.template', 'canva_cream')
            ->where('invitation.headline', $event->name)
            ->where('eventDetails.venueAddress', 'Sun Garden Resort, Bucharest')
            ->where('eventDetails.weddingDetails.partnerOneName', 'Jessica')
            ->where('eventDetails.weddingDetails.familyName', 'Miller')
            ->where('eventDetails.weddingDetails.showFamilyName', true)
            ->where('eventDetails.moments.0.mapsUrl', 'https://www.google.com/maps/search/?api=1&query=City+Hall%2C+Bucharest')
            ->where('eventDetails.moments.1.mapsUrl', 'https://www.google.com/maps/search/?api=1&query=Sun+Garden+Resort%2C+Bucharest')
        );

    $guestParty->refresh();

    expect($guestParty->invitation_open_count)->toBe(1)
        ->and($guestParty->invitation_status)->toBe('opened')
        ->and(EventGuestPartyInvitationView::query()->where('event_guest_party_id', $guestParty->id)->count())->toBe(1)
        ->and(EventGuestPartyInvitationActivity::query()->where('event_guest_party_id', $guestParty->id)->where('activity_type', 'opened')->count())->toBe(1);
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
        ->and($guestParty->responded_at)->not->toBeNull()
        ->and(EventGuestPartyInvitationActivity::query()->where('event_guest_party_id', $guestParty->id)->where('activity_type', 'responded')->count())->toBe(1);

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
        ->and($guestParty?->invitation_status)->toBe('responded')
        ->and(EventGuestPartyInvitationActivity::query()->where('event_guest_party_id', $guestParty?->id)->where('activity_type', 'responded')->count())->toBe(1);

    Notification::assertSentTo($event->user, EventGuestInvitationResponseNotification::class, function (
        EventGuestInvitationResponseNotification $notification,
    ): bool {
        $summary = $notification->toArray(new \stdClass);

        return $summary['guestPartyName'] === 'Familia Georgescu'
            && $summary['changeType'] === 'accepted'
            && $summary['mealPreference'] === 'halal';
    });
});

it('allows an owner to preview the public invitation even when public rsvp is disabled', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'invitation_settings' => [
            'template' => 'canva_cream',
            'public_rsvp_enabled' => false,
        ],
    ]);

    $this->actingAs($owner)
        ->get(route('events.guests.public-invitation.show', $event->public_invitation_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('invitations/EventGuestInvite')
            ->where('eventName', $event->name)
            ->where('isPublicInvite', true)
            ->where('invitation.template', 'canva_cream')
        );
});
