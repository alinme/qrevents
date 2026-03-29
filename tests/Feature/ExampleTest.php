<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('manifest.webmanifest?v=20260329-6', false)
        ->assertSee('apple-mobile-web-app-capable', false)
        ->assertSee('favicon-96x96.png?v=20260329-6', false)
        ->assertSee('/icons/32.png?v=20260329-6', false)
        ->assertSee('og_image.png?v=20260329-6', false)
        ->assertSee('EventSmart', false);
});

test('serves the updated web app manifest', function () {
    $manifest = file_get_contents(public_path('manifest.webmanifest'));

    expect($manifest)
        ->toContain('EventSmart')
        ->toContain('launchericon-512x512.png?v=20260329-6')
        ->toContain('launchericon-192x192.png?v=20260329-6')
        ->toContain('Collect guest photos, videos, and wishes');
});

test('serves the branded site web manifest', function () {
    $manifest = file_get_contents(public_path('site.webmanifest'));

    expect($manifest)
        ->toContain('EventSmart')
        ->toContain('/icons/192.png?v=20260329-6')
        ->toContain('/icons/512.png?v=20260329-6')
        ->not->toContain('MyWebSite');
});
