<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('manifest.webmanifest', false)
        ->assertSee('apple-mobile-web-app-capable', false)
        ->assertSee('favicon-96x96.png', false)
        ->assertSee('/icons/32.png', false)
        ->assertSee('og_image.png', false)
        ->assertSee('EventSmart', false);
});

test('serves the updated web app manifest', function () {
    $manifest = file_get_contents(public_path('manifest.webmanifest'));

    expect($manifest)
        ->toContain('EventSmart')
        ->toContain('launchericon-512x512.png')
        ->toContain('launchericon-192x192.png')
        ->toContain('Collect guest photos, videos, and wishes');
});
