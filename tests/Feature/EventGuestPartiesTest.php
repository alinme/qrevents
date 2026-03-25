<?php

use App\Models\Event;
use App\Models\EventGuestParty;
use App\Models\EventGuestPartyInvitationActivity;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('shows the guest parties page for an event owner', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'currency' => 'EUR',
    ]);

    EventGuestParty::factory()->for($event)->create([
        'name' => 'Familia Popescu',
        'invited_attendees_count' => 4,
        'attendance_status' => 'accepted',
        'confirmed_attendees_count' => 4,
        'actual_attendance_status' => 'present',
        'actual_attendees_count' => 4,
        'gift_type' => 'money',
        'gift_currency' => 'EUR',
        'gift_amount' => 300,
    ])->invitationActivities()->create([
        'event_id' => $event->id,
        'activity_type' => 'reminded',
        'delivery_channel' => 'whatsapp',
        'meta' => ['invitationStatus' => 'sent'],
    ]);

    $this->actingAs($owner)
        ->get(route('events.guests', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Guests')
            ->where('currentEvent.id', $event->id)
            ->where('eventLinks.guestReport', route('events.guests.report', $event))
            ->where('guestPartyStats.partyCount', 1)
            ->where('guestPartyStats.invitedAttendeesCount', 4)
            ->where('guestPartyStats.confirmedAttendeesCount', 4)
            ->where('guestPartyStats.actualAttendeesCount', 4)
            ->where('guestPartyStats.moneyGiftTotal', 300)
            ->has('guestParties', 1)
            ->where('guestParties.0.name', 'Familia Popescu')
            ->where('guestParties.0.reminderCount', 1)
            ->where('guestParties.0.invitationHistory.0.type', 'reminded')
        );
});

it('shows the printable guest report for an event owner', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'currency' => 'EUR',
    ]);

    EventGuestParty::factory()->for($event)->create([
        'name' => 'Familia Popescu',
        'phone' => '0722123456',
        'invited_attendees_count' => 4,
        'confirmed_attendees_count' => 4,
        'attendance_status' => 'accepted',
        'actual_attendance_status' => 'present',
        'actual_attendees_count' => 4,
        'actual_attendance_recorded_at' => now()->subMinutes(15),
        'invitation_status' => 'responded',
        'invitation_delivery_channel' => 'whatsapp',
        'invitation_open_count' => 3,
        'invitation_last_opened_at' => now()->subHour(),
        'invitation_last_opened_ip' => '82.77.18.9',
        'responded_at' => now()->subMinutes(30),
        'gift_type' => 'money',
        'gift_currency' => 'EUR',
        'gift_amount' => 450,
        'meal_preference' => 'halal',
        'response_notes' => 'Will arrive after the church moment.',
    ]);

    $this->actingAs($owner)
        ->get(route('events.guests.report', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/GuestReport')
            ->where('currentEvent.id', $event->id)
            ->where('guestReport.respondedPartyCount', 1)
            ->where('guestReport.openedPartyCount', 1)
            ->where('guestReport.giftRecordedPartyCount', 1)
            ->where('guestReport.presentPartyCount', 1)
            ->where('guestParties.0.actualAttendeesCount', 4)
            ->where('guestReport.moneyGiftTotals.0.currency', 'EUR')
            ->where('guestReport.moneyGiftTotals.0.amount', 450)
            ->where('guestReport.recentResponses.0.name', 'Familia Popescu')
            ->where('guestReport.recentInvitationOpens.0.invitationLastOpenedIp', '82.77.18.9')
            ->where('guestParties.0.name', 'Familia Popescu')
        );
});

it('allows an event owner to add a guest party with ledger details', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->post(route('events.guests.store', $event), [
            'name' => 'Familia Ionescu',
            'phone' => '0722123456',
            'invited_attendees_count' => 3,
            'confirmed_attendees_count' => 3,
            'attendance_status' => 'accepted',
            'actual_attendance_status' => 'present',
            'actual_attendees_count' => 3,
            'notes' => 'Close family friends',
            'invitation_status' => 'delivered_in_person',
            'invitation_delivery_channel' => 'in_person',
            'gift_type' => 'money',
            'gift_currency' => 'EUR',
            'gift_amount' => '400',
        ])
        ->assertRedirect();

    $party = $event->guestParties()->first();

    expect($party)->not->toBeNull()
        ->and($party?->name)->toBe('Familia Ionescu')
        ->and($party?->phone)->toBe('0722123456')
        ->and($party?->invited_attendees_count)->toBe(3)
        ->and($party?->confirmed_attendees_count)->toBe(3)
        ->and($party?->attendance_status)->toBe('accepted')
        ->and($party?->actual_attendance_status)->toBe('present')
        ->and($party?->actual_attendees_count)->toBe(3)
        ->and($party?->invitation_status)->toBe('delivered_in_person')
        ->and($party?->gift_type)->toBe('money')
        ->and($party?->gift_currency)->toBe('EUR')
        ->and((string) $party?->gift_amount)->toBe('400.00')
        ->and($party?->invitation_token)->not->toBeNull()
        ->and($party?->invitation_delivered_at)->not->toBeNull();
});

it('imports guest parties from messy pasted text and skips duplicates', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create();

    EventGuestParty::factory()->for($event)->create([
        'name' => 'Familia Popescu',
        'phone' => '0765223445',
    ]);

    $this->actingAs($owner)
        ->post(route('events.guests.import', $event), [
            'import_text' => implode("\n", [
                'Familia Popescu - 0765223445',
                'Ion Vasile, 07126326123',
                'James Webb - 4 guests - 0744556677',
            ]),
        ])
        ->assertRedirect();

    expect($event->guestParties()->count())->toBe(3);

    $ion = $event->guestParties()->where('name', 'Ion Vasile')->first();
    $james = $event->guestParties()->where('name', 'James Webb')->first();

    expect($ion)->not->toBeNull()
        ->and($ion?->phone)->toBe('07126326123')
        ->and($ion?->invited_attendees_count)->toBe(1)
        ->and($james)->not->toBeNull()
        ->and($james?->phone)->toBe('0744556677')
        ->and($james?->invited_attendees_count)->toBe(4);
});

it('exports the guest ledger as csv for the event owner', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'name' => 'Benjamin & Rebecca Wedding',
    ]);

    EventGuestParty::factory()->for($event)->create([
        'name' => 'Familia Popescu',
        'phone' => '0722123456',
        'invited_attendees_count' => 4,
        'confirmed_attendees_count' => 4,
        'actual_attendance_status' => 'present',
        'actual_attendees_count' => 4,
        'attendance_status' => 'accepted',
        'guest_names' => 'Marcel, Ana, Vlad, Ioana',
        'meal_preference' => 'halal',
        'response_notes' => 'Arriving after church.',
        'gift_type' => 'money',
        'gift_currency' => 'EUR',
        'gift_amount' => 450,
    ]);

    $response = $this->actingAs($owner)
        ->get(route('events.guests.export', $event));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();

    expect($response->headers->get('content-disposition'))->toContain('guest-ledger-benjamin-rebecca-wedding-')
        ->and($content)->toContain('Family / Name')
        ->and($content)->toContain('Familia Popescu')
        ->and($content)->toContain('0722123456')
        ->and($content)->toContain('Marcel, Ana, Vlad, Ioana')
        ->and($content)->toContain('present')
        ->and($content)->toContain('450.00');
});

it('allows an event owner to bulk mark invitations as delivered or sent', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create();

    $firstParty = EventGuestParty::factory()->for($event)->create([
        'name' => 'Familia Popescu',
        'invitation_status' => 'draft',
    ]);

    $secondParty = EventGuestParty::factory()->for($event)->create([
        'name' => 'Familia Ionescu',
        'invitation_status' => 'draft',
    ]);

    $this->actingAs($owner)
        ->post(route('events.guests.invitations.bulk-update', $event), [
            'guest_party_ids' => [$firstParty->id, $secondParty->id],
            'action' => 'mark_delivered_in_person',
        ])
        ->assertRedirect();

    $firstParty->refresh();
    $secondParty->refresh();

    expect($firstParty->invitation_status)->toBe('delivered_in_person')
        ->and($firstParty->invitation_delivery_channel)->toBe('in_person')
        ->and($firstParty->invitation_delivered_at)->not->toBeNull()
        ->and($secondParty->invitation_status)->toBe('delivered_in_person')
        ->and($secondParty->invitation_delivery_channel)->toBe('in_person')
        ->and(EventGuestPartyInvitationActivity::query()->where('event_guest_party_id', $firstParty->id)->where('activity_type', 'delivered_in_person')->exists())->toBeTrue()
        ->and(EventGuestPartyInvitationActivity::query()->where('event_guest_party_id', $secondParty->id)->where('activity_type', 'delivered_in_person')->exists())->toBeTrue();
});

it('allows an event owner to mark pending invitations as reminded', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create();

    $guestParty = EventGuestParty::factory()->for($event)->create([
        'name' => 'Familia Marinescu',
        'attendance_status' => 'pending',
        'invitation_status' => 'sent',
        'invitation_delivery_channel' => 'whatsapp',
        'invitation_delivered_at' => now()->subDay(),
    ]);

    $this->actingAs($owner)
        ->post(route('events.guests.invitations.bulk-update', $event), [
            'guest_party_ids' => [$guestParty->id],
            'action' => 'mark_reminded_online',
            'delivery_channel' => 'whatsapp',
        ])
        ->assertRedirect();

    $guestParty->refresh();

    expect($guestParty->invitation_status)->toBe('sent')
        ->and($guestParty->invitation_delivered_at)->not->toBeNull()
        ->and(EventGuestPartyInvitationActivity::query()->where('event_guest_party_id', $guestParty->id)->where('activity_type', 'reminded')->exists())->toBeTrue();
});

it('forbids another user from managing guest parties', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($otherUser)
        ->get(route('events.guests', $event))
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->post(route('events.guests.store', $event), [
            'name' => 'Familia Popescu',
            'invited_attendees_count' => 2,
            'attendance_status' => 'pending',
            'invitation_status' => 'draft',
        ])
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->get(route('events.guests.report', $event))
        ->assertForbidden();
});
