<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('manifest.webmanifest', false)
        ->assertSee('apple-mobile-web-app-capable', false);
});
