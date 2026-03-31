<?php

use App\Providers\AppServiceProvider;

it('allows the vite hot file when the request host matches the dev server host', function (): void {
    expect(AppServiceProvider::shouldUseViteHotFileForRequest('127.0.0.1', 'http://127.0.0.1:5173'))
        ->toBeTrue()
        ->and(AppServiceProvider::shouldUseViteHotFileForRequest('localhost', 'http://localhost:5173'))
        ->toBeTrue()
        ->and(AppServiceProvider::shouldUseViteHotFileForRequest('127.0.0.1', 'http://localhost:5173'))
        ->toBeTrue()
        ->and(AppServiceProvider::shouldUseViteHotFileForRequest('qrevents.test', 'https://qrevents.test:5173'))
        ->toBeTrue();
});

it('disables the vite hot file when the dev server host does not match the request host', function (): void {
    expect(AppServiceProvider::shouldUseViteHotFileForRequest('127.0.0.1', 'https://qrevents.test:5173'))
        ->toBeFalse()
        ->and(AppServiceProvider::shouldUseViteHotFileForRequest('qrevents.test', 'http://localhost:5173'))
        ->toBeFalse()
        ->and(AppServiceProvider::shouldUseViteHotFileForRequest('example.com', 'https://example.com:5173'))
        ->toBeFalse();
});

it('falls back safely when the hot file does not contain a dev server url', function (): void {
    expect(AppServiceProvider::shouldUseViteHotFileForRequest('127.0.0.1', ''))
        ->toBeTrue()
        ->and(AppServiceProvider::shouldUseViteHotFileForRequest('qrevents.test', '/tmp/vite.hot'))
        ->toBeTrue()
        ->and(AppServiceProvider::shouldUseViteHotFileForRequest(null, 'http://127.0.0.1:5173'))
        ->toBeFalse();
});
