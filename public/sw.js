const CACHE_NAME = 'squarhe-v4';
const APP_SHELL = [
    '/',
    '/login',
    '/dashboard',
    '/employees',
    '/build/assets/app-BoTqgwSd.css',
    '/build/assets/app-Iqm5ExNU.js',
    '/favicon.ico',
];

self.addEventListener('install', (event) => {
    console.log('[SW] Installing and caching shell');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(APP_SHELL);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    console.log('[SW] Activating and cleaning old caches');
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;
    if (event.request.url.includes('/api/')) return;

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(event.request).catch(() => {
                if (event.request.mode === 'navigate') {
                    console.log('[SW] Network failed, serving App Shell for:', event.request.url);
                    return caches.match('/');
                }
                return null;
            });
        })
    );
});
