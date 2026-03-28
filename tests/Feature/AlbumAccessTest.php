<?php

use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the album access page', function () {
    $this->get(route('events.album.access.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/AlbumAccess')
            ->where('segmentCount', 8)
            ->where('segmentLength', 4)
            ->where('submitUrl', route('events.album.access.resolve'))
        );
});

it('redirects to the album for a matching code', function () {
    $event = Event::factory()->create([
        'share_token' => 'Ab12Cd34Ef56Gh78Ij90Kl12Mn34Op56',
    ]);

    $this->post(route('events.album.access.resolve'), [
        'code' => 'ab12-cd34 ef56-gh78 ij90-kl12 mn34-op56',
    ])->assertRedirect(route('events.album', $event->share_token));
});

it('returns an error when the code does not match an album', function () {
    $this->from(route('events.album.access.show'))
        ->post(route('events.album.access.resolve'), [
            'code' => 'AA11BB22CC33DD44EE55FF66GG77HH88',
        ])
        ->assertRedirect(route('events.album.access.show'))
        ->assertSessionHasErrors([
            'code' => 'We could not find an album for that code. Check the letters and numbers, then try again.',
        ]);
});

it('validates the album code length', function () {
    $this->from(route('events.album.access.show'))
        ->post(route('events.album.access.resolve'), [
            'code' => 'ABC123',
        ])
        ->assertRedirect(route('events.album.access.show'))
        ->assertSessionHasErrors([
            'code' => 'Album codes use 32 letters and numbers. Keep going until every block is filled.',
        ]);
});
