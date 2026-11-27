const CACHE_NAME = 'farmacia-sjb-static-v1';
const SCOPE_URL = new URL(self.registration.scope);
const PRECACHE_URLS = [
    'offline.html',
    'manifest.json',
    'pwa/icon-192.png',
    'pwa/icon-512.png',
].map((path) => new URL(path, SCOPE_URL).href);

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('farmacia-sjb-static-') && key !== CACHE_NAME)
                    .map((key) => caches.delete(key)),
            ))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const requestUrl = new URL(request.url);

    if (request.method !== 'GET' || requestUrl.origin !== SCOPE_URL.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(async () => {
                const fallback = await caches.match(new URL('offline.html', SCOPE_URL).href);

                return fallback || new Response('Sin conexión.', {
                    status: 503,
                    headers: { 'Content-Type': 'text/plain; charset=utf-8' },
                });
            }),
        );

        return;
    }

    const relativePath = requestUrl.pathname.startsWith(SCOPE_URL.pathname)
        ? requestUrl.pathname.slice(SCOPE_URL.pathname.length)
        : '';
    const isPublicAsset = ['css/', 'js/', 'pwa/'].some((prefix) => relativePath.startsWith(prefix));

    if (!isPublicAsset) {
        return;
    }

    event.respondWith(
        caches.match(request).then(async (cached) => {
            if (cached) {
                return cached;
            }

            const response = await fetch(request);

            if (response.ok && response.type === 'basic') {
                const cache = await caches.open(CACHE_NAME);
                await cache.put(request, response.clone());
            }

            return response;
        }),
    );
});
