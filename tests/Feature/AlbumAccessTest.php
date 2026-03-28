<?php

use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the album access page', function () {
    $this->get(route('events.album.access.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/AlbumAccess')
            ->where('segmentCount', 4)
            ->where('submitUrl', route('events.album.access.resolve'))
            ->where('entryShortcutUrl', 'https://is.gd/evsmrt')
            ->where('defaultTarget', 'album')
        );
});

it('redirects to the album for a matching code', function () {
    $event = Event::factory()->create([
        'share_token' => 'Ab12Cd34Ef56Gh78Ij90Kl12Mn34Op56',
        'album_access_code' => 'AB12',
    ]);

    $this->post(route('events.album.access.resolve'), [
        'code' => 'ab12',
        'target' => 'album',
    ])->assertRedirect(route('events.album', $event->publicAlbumCode()));
});

it('redirects to the wall for a matching code when wall is selected', function () {
    $event = Event::factory()->create([
        'album_access_code' => 'WX91',
    ]);

    $this->post(route('events.album.access.resolve'), [
        'code' => 'wx91',
        'target' => 'wall',
    ])->assertRedirect(route('events.wall', $event->publicAlbumCode()));
});

it('returns an error when the code does not match an album', function () {
    $this->from(route('events.album.access.show'))
        ->post(route('events.album.access.resolve'), [
            'code' => 'ZZ99',
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
            'code' => 'Codes use 4 letters or numbers. Fill all 4 boxes and we will open it.',
        ]);
});

it('opens the public album when the short access code is used in the album route', function () {
    $event = Event::factory()->create([
        'album_access_code' => 'Q7R2',
    ]);

    $this->get(route('events.album', $event->publicAlbumCode()))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Album')
            ->where('albumAccessCode', 'Q7R2')
            ->where('links.albumEntryShortcut', 'https://is.gd/evsmrt')
        );
});
