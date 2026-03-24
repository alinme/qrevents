const CACHE_NAME = 'qrevents-static-v1';
const APP_SHELL = [
    '/',
    '/launch',
    '/manifest.webmanifest',
    '/apple-touch-icon.png',
    '/favicon.ico',
    '/favicon.svg',
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
