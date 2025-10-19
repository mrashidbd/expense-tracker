const CACHE_NAME = 'expense-tracker-pwa-v1';

// Essential PWA assets to cache
const ESSENTIAL_ASSETS = [
    '/manifest.json',
    '/sw.js'
];

// Install event - cache essential PWA assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker...');

    event.waitUntil(
        Promise.all([
            // Cache essential PWA assets
            caches.open(CACHE_NAME).then((cache) => {
                console.log('[SW] Caching essential PWA assets');
                return cache.addAll(ESSENTIAL_ASSETS);
            }),
            // Skip waiting to activate immediately
            self.skipWaiting()
        ])
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker...');

    event.waitUntil(
        Promise.all([
            // Clean up old caches
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('[SW] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            }),
            // Take control of all pages
            self.clients.claim()
        ])
    );
});

// Fetch event - handle requests with cache strategies
self.addEventListener('fetch', (event) => {
    const {request} = event;
    const url = new URL(request.url);

    // Skip non-GET requests and external requests
    if (request.method !== 'GET' || !url.origin.includes(self.location.origin)) {
        return;
    }

    // Handle static asset requests only
    if (isStaticAsset(request)) {
        event.respondWith(handleStaticAsset(request));
    }
});

// Check if request is for static asset
function isStaticAsset(request) {
    const url = new URL(request.url);
    return url.pathname.startsWith('/build/') ||
        url.pathname.includes('.css') ||
        url.pathname.includes('.js') ||
        url.pathname.includes('.png') ||
        url.pathname.includes('.jpg') ||
        url.pathname.includes('.jpeg') ||
        url.pathname.includes('.gif') ||
        url.pathname.includes('.webp') ||
        url.pathname.includes('.svg') ||
        url.pathname.includes('.ico') ||
        url.pathname.includes('.woff') ||
        url.pathname.includes('.woff2') ||
        url.pathname.includes('.ttf') ||
        url.pathname.includes('.eot');
}

// Handle static assets - Cache First with Network Fallback
async function handleStaticAsset(request) {
    const cache = await caches.open(CACHE_NAME);

    // Try cache first
    const cachedResponse = await cache.match(request);
    if (cachedResponse) {
        return cachedResponse;
    }

    // Try network and cache
    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        console.log('[SW] Failed to fetch static asset:', request.url);
        // Return a basic response or placeholder
        return new Response('Asset not available', {status: 503});
    }
}

// Listen for messages from the main thread
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});