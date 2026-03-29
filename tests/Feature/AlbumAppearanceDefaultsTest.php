<?php

use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('uses a seeded default album background when branding does not provide one', function () {
    $user = User::factory()->create();
    $event = Event::factory()->for($user)->create([
        'album_access_code' => 'ALV1',
        'share_token' => 'Ab12Cd34Ef56Gh78Ij90Kl12Mn34Op56',
        'branding' => null,
    ]);

    $backgrounds = [
        'alvin-bg-md.jpg',
        'beatriz-bg-md.jpg',
        'drew-bg-md.jpg',
        'jeremy-bg-md.jpg',
        'nathan-bg-md.jpg',
        'sandy-bg-md.jpg',
    ];
    $hash = (int) sprintf('%u', crc32($event->album_access_code));
    $expectedBackgroundUrl = asset(
        'images/album/'.$backgrounds[$hash % count($backgrounds)],
    );

    $this->get(route('events.album', $event->publicAlbumCode()))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Album')
            ->where('appearance.albumBackgroundImageUrl', $expectedBackgroundUrl)
        );

    $this->actingAs($user);

    $this->get(route('events.settings', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Settings')
            ->where('currentEvent.settings.albumBackgroundImageUrl', $expectedBackgroundUrl)
        );
});
