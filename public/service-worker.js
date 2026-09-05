const CACHE_NAME = 'debt-manager-v2';
const OFFLINE_ASSETS = ['/manifest.json', '/icon-192-v2.png', '/icon-512-v2.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(OFFLINE_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

// Network-first: always try the live server, fall back to cache only if offline.
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});
