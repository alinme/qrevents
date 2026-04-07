<?php

use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('shows standalone preview pages for event owners', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->get(route('events.print-pack.preview', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/PrintPackPreview')
            ->where('currentEvent.id', $event->id)
            ->where('eventLinks.printPack', route('events.print-pack', $event))
            ->where('eventLinks.albumQrDataUrl', fn (string $value) => str_starts_with($value, 'data:image/svg+xml'))
        );

    $this->actingAs($owner)
        ->get(route('events.invite-studio.preview', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/InviteStudioPreview')
            ->where('currentEvent.id', $event->id)
            ->where('eventLinks.inviteStudio', route('events.invite-studio', $event))
            ->where('invitationPreview.branding.logoUrl', null)
        );
});

it('redirects guests away from standalone studio preview pages', function () {
    $event = Event::factory()->create();

    $this->get(route('events.print-pack.preview', $event))
        ->assertRedirect(route('login'));

    $this->get(route('events.invite-studio.preview', $event))
        ->assertRedirect(route('login'));
});
