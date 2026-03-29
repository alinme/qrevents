const CACHE_NAME = 'qrevents-static-v3';
const APP_SHELL = [
    '/',
    '/launch',
    '/manifest.webmanifest?v=20260329-6',
    '/apple-touch-icon.png?v=20260329-6',
    '/favicon.ico?v=20260329-6',
    '/favicon.svg?v=20260329-6',
    '/favicon-96x96.png?v=20260329-6',
    '/icons/16.png?v=20260329-6',
    '/icons/32.png?v=20260329-6',
    '/icons/192.png?v=20260329-6',
    '/icons/512.png?v=20260329-6',
    '/icons/launchericon-192x192.png?v=20260329-6',
    '/icons/launchericon-512x512.png?v=20260329-6',
    '/og_image.png?v=20260329-6',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(APP_SHELL))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key !== CACHE_NAME)
                        .map((key) => caches.delete(key)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const copy = response.clone();
                    void caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
                    return response;
                })
                .catch(async () => {
                    const cachedResponse = await caches.match(event.request);
                    return cachedResponse || caches.match('/');
                }),
        );

        return;
    }

    const shouldCache =
        url.pathname.startsWith('/build/')
        || url.pathname.startsWith('/storage/')
        || ['script', 'style', 'font', 'image'].includes(event.request.destination);

    if (!shouldCache) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then(async (cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            const response = await fetch(event.request);
            if (response.ok) {
                const copy = response.clone();
                void caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
            }

            return response;
        }),
    );
});
