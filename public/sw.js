const CACHE_NAME = 'expense-tracker-v1';
const STATIC_CACHE = 'expense-tracker-static-v1';
const API_CACHE = 'expense-tracker-api-v1';

// Assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/dashboard',
    '/transactions',
    '/categories',
    '/reports',
    '/manifest.json',
    // Add your CSS and JS files here after build
    // '/build/assets/app.css',
    // '/build/assets/app.js',
    '/build/assets/_plugin-vue_export-helper-DlAUqK2U.js',
    '/build/assets/app-BEB-IAw6.js',
    '/build/assets/app-S0fs8JZp.css',
    '/build/assets/AppLayout-DXcAuxfa.css',
    '/build/assets/AppLayout-UsbH1mzv.js',
    '/build/assets/ApplicationLogo-D2GELMLC.js',
    '/build/assets/ConfirmPassword-Bg_n-gCp.js',
    '/build/assets/Create-C0VW4fuj.js',
    '/build/assets/Dashboard-C_LFIcwD.js',
    '/build/assets/DeleteUserForm-BKsSjeiP.js',
    '/build/assets/Edit-Br1csTPz.js',
    '/build/assets/ForgotPassword-Dt7AWuiL.js',
    '/build/assets/GuestLayout-B9INcOc8.js',
    '/build/assets/Index-C1FCKw2j.js',
    '/build/assets/Index-CJJ97JQU.js',
    '/build/assets/Index-DKD6Jahz.js',
    '/build/assets/Login-CfXZWAE_.js',
    '/build/assets/PrimaryButton-DL-hkk-T.js',
    '/build/assets/Register-CzL8CqnC.js',
    '/build/assets/ResetPassword-DaXu-bI8.js',
    '/build/assets/TextInput-CRG-N4OB.js',
    '/build/assets/UpdatePasswordForm-DJOLmWvp.js',
    '/build/assets/UpdateProfileInformationForm-qXpkxCII.js',
    '/build/assets/VerifyEmail-D9UQal_i.js',
    '/build/assets/Welcome-DzlYw_gV.js'
];

// API endpoints to cache
const API_ENDPOINTS = [
    '/dashboard/data',
    '/api/categories/grouped',
    '/transactions/recent'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker...');

    event.waitUntil(
        Promise.all([
            // Cache static assets
            caches.open(STATIC_CACHE).then((cache) => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS.filter(url => url.startsWith('/build/')));
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
                        if (cacheName !== STATIC_CACHE && cacheName !== API_CACHE && cacheName !== CACHE_NAME) {
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

    // Handle different types of requests
    if (isAPIRequest(request)) {
        event.respondWith(handleAPIRequest(request));
    } else if (isStaticAsset(request)) {
        event.respondWith(handleStaticAsset(request));
    } else if (isPageRequest(request)) {
        event.respondWith(handlePageRequest(request));
    }
});

// Check if request is for API
function isAPIRequest(request) {
    const url = new URL(request.url);
    return url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/dashboard/data') ||
        url.pathname.startsWith('/transactions/recent');
}

// Check if request is for static asset
function isStaticAsset(request) {
    const url = new URL(request.url);
    return url.pathname.startsWith('/build/') ||
        url.pathname.includes('.css') ||
        url.pathname.includes('.js') ||
        url.pathname.includes('.png') ||
        url.pathname.includes('.jpg') ||
        url.pathname.includes('.svg') ||
        url.pathname.includes('.ico');
}

// Check if request is for a page
function isPageRequest(request) {
    const url = new URL(request.url);
    return request.headers.get('accept')?.includes('text/html') ||
        url.pathname === '/' ||
        url.pathname.startsWith('/dashboard') ||
        url.pathname.startsWith('/transactions') ||
        url.pathname.startsWith('/categories') ||
        url.pathname.startsWith('/reports');
}

// Handle API requests - Network First with Cache Fallback
async function handleAPIRequest(request) {
    const cache = await caches.open(API_CACHE);

    try {
        // Try network first
        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            // Cache successful responses
            cache.put(request, networkResponse.clone());

            // Store data in IndexedDB for offline sync
            if (request.url.includes('/api/')) {
                await storeOfflineData(request, networkResponse.clone());
            }
        }

        return networkResponse;
    } catch (error) {
        console.log('[SW] Network failed for API request, trying cache...', error);

        // Try cache fallback
        const cachedResponse = await cache.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }

        // Return offline response for specific endpoints
        return createOfflineResponse(request);
    }
}

// Handle static assets - Cache First with Network Fallback
async function handleStaticAsset(request) {
    const cache = await caches.open(STATIC_CACHE);

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
        return new Response('Asset not available offline', {status: 503});
    }
}

// Handle page requests - Network First with Cache Fallback
async function handlePageRequest(request) {
    try {
        // Try network first
        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            // Cache the page
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        console.log('[SW] Network failed for page request, trying cache...', error);

        // Try cache fallback
        const cache = await caches.open(CACHE_NAME);
        const cachedResponse = await cache.match(request);

        if (cachedResponse) {
            return cachedResponse;
        }

        // Return offline page
        return createOfflinePage();
    }
}

// Store API data for offline sync
async function storeOfflineData(request, response) {
    try {
        const data = await response.json();
        const url = new URL(request.url);

        // Open IndexedDB
        const db = await openDB();
        const transaction = db.transaction(['api_cache'], 'readwrite');
        const store = transaction.objectStore('api_cache');

        await store.put({
            url: url.pathname,
            data: data,
            timestamp: Date.now()
        });

        console.log('[SW] Stored API data for offline use:', url.pathname);
    } catch (error) {
        console.log('[SW] Failed to store offline data:', error);
    }
}

// Create offline response for API requests
function createOfflineResponse(request) {
    const url = new URL(request.url);

    // Return appropriate offline responses based on endpoint
    if (url.pathname.includes('/dashboard/data')) {
        return new Response(JSON.stringify({
            currentMonth: {
                income: 0,
                expense: 0,
                balance: 0,
                month: new Date().toLocaleString('default', {month: 'long'}),
                year: new Date().getFullYear()
            }
        }), {
            status: 200,
            headers: {'Content-Type': 'application/json'}
        });
    }

    if (url.pathname.includes('/categories')) {
        return new Response(JSON.stringify({income: [], expense: []}), {
            status: 200,
            headers: {'Content-Type': 'application/json'}
        });
    }

    return new Response(JSON.stringify({message: 'Offline mode'}), {
        status: 503,
        headers: {'Content-Type': 'application/json'}
    });
}

// Create offline page response
function createOfflinePage() {
    const offlineHTML = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>ExpenseTracker - Offline</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          display: flex;
          align-items: center;
          justify-content: center;
          min-height: 100vh;
          margin: 0;
          background-color: #f4f0bb;
          color: #43291f;
        }
        .offline-container {
          text-align: center;
          padding: 2rem;
          max-width: 400px;
        }
        .logo {
          width: 64px;
          height: 64px;
          background-color: #226f54;
          border-radius: 12px;
          margin: 0 auto 1rem;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-size: 24px;
        }
        h1 { color: #226f54; margin-bottom: 1rem; }
        p { margin-bottom: 1.5rem; color: #666; }
        .retry-button {
          background-color: #226f54;
          color: white;
          border: none;
          padding: 12px 24px;
          border-radius: 8px;
          cursor: pointer;
          font-size: 16px;
        }
        .retry-button:hover {
          background-color: #1a5440;
        }
      </style>
    </head>
    <body>
      <div class="offline-container">
        <div class="logo">💰</div>
        <h1>You're Offline</h1>
        <p>ExpenseTracker is currently offline. Some features may be limited until you reconnect to the internet.</p>
        <button class="retry-button" onclick="window.location.reload()">
          Try Again
        </button>
      </div>
    </body>
    </html>
  `;

    return new Response(offlineHTML, {
        status: 200,
        headers: {'Content-Type': 'text/html'}
    });
}

// Open IndexedDB for offline storage
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('ExpenseTrackerDB', 1);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;

            // Create object stores
            if (!db.objectStoreNames.contains('api_cache')) {
                db.createObjectStore('api_cache', {keyPath: 'url'});
            }

            if (!db.objectStoreNames.contains('offline_transactions')) {
                const store = db.createObjectStore('offline_transactions', {keyPath: 'id', autoIncrement: true});
                store.createIndex('timestamp', 'timestamp');
            }

            if (!db.objectStoreNames.contains('offline_categories')) {
                db.createObjectStore('offline_categories', {keyPath: 'id', autoIncrement: true});
            }
        };
    });
}

// Listen for messages from the main thread
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'SYNC_DATA') {
        // Handle background sync when online
        event.waitUntil(syncOfflineData());
    }
});

// Sync offline data when back online
async function syncOfflineData() {
    try {
        const db = await openDB();
        const transaction = db.transaction(['offline_transactions'], 'readonly');
        const store = transaction.objectStore('offline_transactions');
        const offlineTransactions = await store.getAll();

        console.log('[SW] Found offline transactions to sync:', offlineTransactions.length);

        // Send each offline transaction to server
        for (const transaction of offlineTransactions) {
            try {
                const response = await fetch('/transactions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(transaction.data)
                });

                if (response.ok) {
                    // Remove from offline storage after successful sync
                    const deleteTransaction = db.transaction(['offline_transactions'], 'readwrite');
                    const deleteStore = deleteTransaction.objectStore('offline_transactions');
                    await deleteStore.delete(transaction.id);

                    console.log('[SW] Synced offline transaction:', transaction.id);
                }
            } catch (error) {
                console.log('[SW] Failed to sync transaction:', transaction.id, error);
            }
        }

        // Notify main thread about sync completion
        const clients = await self.clients.matchAll();
        clients.forEach(client => {
            client.postMessage({type: 'SYNC_COMPLETE'});
        });

    } catch (error) {
        console.log('[SW] Failed to sync offline data:', error);
    }
}
