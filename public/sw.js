const VERSION = 'squarhe-v5';
const STATIC_CACHE = `${VERSION}:static`;
const PAGE_CACHE = `${VERSION}:pages`;
const ASSET_CACHE = `${VERSION}:assets`;
const IMAGE_CACHE = `${VERSION}:images`;

const CORE_ASSETS = [
    '/',
    '/login',
    '/offline.html',
    '/favicon.ico',
    '/favicon.svg',
    '/apple-touch-icon.png',
    '/logo.png',
    '/manifest.json',
];

const APP_ROUTES = [
    '/dashboard',
    '/notifications',
    '/employees',
    '/documents',
    '/metrics',
    '/support',
    '/pay',
    '/pay/check/payslips',
    '/pay/payroll/close',
    '/leaves',
    '/employees/add',
    '/employees/import',
    '/employees/import/overtimes',
    '/employees/import/leaves',
    '/employees/import/remunerations',
    '/settings/profile',
    '/settings/appearance',
    '/settings/company/add',
    '/settings/company/update',
    '/settings/company/manage/admin',
    '/settings/company/setting',
    '/settings/company/checkout',
];

const NEVER_CACHE_PATHS = [
    '/api/',
    '/logout',
    '/login',
    '/register',
    '/forgot-password',
    '/reset-password',
    '/two-factor-challenge',
    '/user/confirm-password',
    '/settings/security',
];

const isHttpRequest = (request) => request.url.startsWith('http://') || request.url.startsWith('https://');
const isSameOrigin = (url) => url.origin === self.location.origin;
const isAssetRequest = (url, request) => request.destination === 'script'
    || request.destination === 'style'
    || request.destination === 'font'
    || url.pathname.startsWith('/build/')
    || url.pathname.endsWith('.css')
    || url.pathname.endsWith('.js')
    || url.pathname.endsWith('.woff2');
const isImageRequest = (request) => ['image', 'manifest'].includes(request.destination);
const shouldNeverCache = (url) => NEVER_CACHE_PATHS.some((path) => url.pathname.startsWith(path));

async function cacheCore() {
    const cache = await caches.open(STATIC_CACHE);

    await Promise.allSettled(CORE_ASSETS.map((url) => cache.add(new Request(url, { credentials: 'same-origin' }))));
}

async function cacheAppRoutes() {
    const cache = await caches.open(PAGE_CACHE);

    await Promise.allSettled(APP_ROUTES.map(async (url) => {
        const response = await fetch(new Request(url, { credentials: 'same-origin' }));

        if (response.ok && response.type === 'basic') {
            await cache.put(url, response);
        }
    }));
}

async function networkFirst(request, cacheName, fallbackUrl = '/offline.html') {
    const cache = await caches.open(cacheName);

    try {
        const response = await fetch(request);

        if (response.ok && response.type === 'basic') {
            await cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        return (await cache.match(request))
            || (await caches.match('/'))
            || (await caches.match(fallbackUrl))
            || Response.error();
    }
}

async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    const fetched = fetch(request).then((response) => {
        if (response.ok && (response.type === 'basic' || response.type === 'cors')) {
            cache.put(request, response.clone());
        }

        return response;
    }).catch(() => null);

    return cached || (await fetched) || Response.error();
}

self.addEventListener('install', (event) => {
    event.waitUntil(cacheCore());
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const expectedCaches = [STATIC_CACHE, PAGE_CACHE, ASSET_CACHE, IMAGE_CACHE];
        const keys = await caches.keys();

        await Promise.all(keys
            .filter((key) => key.startsWith('squarhe-') && !expectedCaches.includes(key))
            .map((key) => caches.delete(key)));

        await self.clients.claim();
    })());
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'CACHE_APP_SHELL') {
        event.waitUntil(cacheAppRoutes());
    }
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (!isHttpRequest(request) || request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (!isSameOrigin(url) || shouldNeverCache(url)) {
        return;
    }

    if (url.pathname.startsWith('/api/')) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(networkFirst(request, PAGE_CACHE));
        return;
    }

    if (isAssetRequest(url, request)) {
        event.respondWith(staleWhileRevalidate(request, ASSET_CACHE));
        return;
    }

    if (isImageRequest(request)) {
        event.respondWith(staleWhileRevalidate(request, IMAGE_CACHE));
    }
});
