const CACHE_NAME = 'reportes-v1.0.0';
const STATIC_CACHE = 'static-v1.0.0';
const DYNAMIC_CACHE = 'dynamic-v1.0.0';

// Assets to cache on install
const STATIC_ASSETS = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/images/logo.png',
    '/offline.html',
    'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
    'https://cdn.tailwindcss.com',
    'https://cdn.jsdelivr.net/npm/chart.js'
];

// Routes that should work offline
const OFFLINE_ROUTES = [
    '/dashboard',
    '/projects',
    '/time-logs',
    '/profile'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    console.log('Service Worker: Installing...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('Service Worker: Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('Service Worker: Static assets cached');
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('Service Worker: Error caching static assets', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    console.log('Service Worker: Activating...');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
                            console.log('Service Worker: Deleting old cache', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker: Activated');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip external requests (except CDN assets)
    if (url.origin !== location.origin && !STATIC_ASSETS.includes(request.url)) {
        return;
    }

    // Handle API requests
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(handleApiRequest(request));
        return;
    }

    // Handle static assets
    if (isStaticAsset(request.url)) {
        event.respondWith(handleStaticAsset(request));
        return;
    }

    // Handle page requests
    if (request.headers.get('accept').includes('text/html')) {
        event.respondWith(handlePageRequest(request));
        return;
    }

    // Default: network first, then cache
    event.respondWith(
        fetch(request)
            .then(response => {
                // Cache successful responses
                if (response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(DYNAMIC_CACHE)
                        .then(cache => cache.put(request, responseClone));
                }
                return response;
            })
            .catch(() => {
                return caches.match(request);
            })
    );
});

// Handle static assets (cache first)
function handleStaticAsset(request) {
    return caches.match(request)
        .then(cachedResponse => {
            if (cachedResponse) {
                return cachedResponse;
            }
            
            return fetch(request)
                .then(response => {
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(STATIC_CACHE)
                            .then(cache => cache.put(request, responseClone));
                    }
                    return response;
                })
                .catch(() => {
                    // Return a fallback for critical assets
                    if (request.url.includes('.css')) {
                        return new Response('/* Offline CSS fallback */', {
                            headers: { 'Content-Type': 'text/css' }
                        });
                    }
                    if (request.url.includes('.js')) {
                        return new Response('// Offline JS fallback', {
                            headers: { 'Content-Type': 'application/javascript' }
                        });
                    }
                });
        });
}

// Handle page requests (network first, then cache, then offline page)
function handlePageRequest(request) {
    return fetch(request)
        .then(response => {
            if (response.status === 200) {
                const responseClone = response.clone();
                caches.open(DYNAMIC_CACHE)
                    .then(cache => cache.put(request, responseClone));
            }
            return response;
        })
        .catch(() => {
            return caches.match(request)
                .then(cachedResponse => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    
                    // Return offline page for supported routes
                    const url = new URL(request.url);
                    if (OFFLINE_ROUTES.some(route => url.pathname.startsWith(route))) {
                        return caches.match('/offline.html');
                    }
                    
                    // Return a basic offline response
                    return new Response(`
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Sin Conexión - Sistema de Reportes</title>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1">
                            <style>
                                body { 
                                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                                    display: flex; 
                                    align-items: center; 
                                    justify-content: center; 
                                    min-height: 100vh; 
                                    margin: 0; 
                                    background: #f3f4f6;
                                }
                                .container { 
                                    text-align: center; 
                                    padding: 2rem;
                                    background: white;
                                    border-radius: 8px;
                                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                                    max-width: 400px;
                                }
                                .icon { 
                                    width: 64px; 
                                    height: 64px; 
                                    margin: 0 auto 1rem; 
                                    background: #ef4444;
                                    border-radius: 50%;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                }
                                h1 { 
                                    color: #1f2937; 
                                    margin-bottom: 0.5rem; 
                                }
                                p { 
                                    color: #6b7280; 
                                    margin-bottom: 1.5rem; 
                                }
                                button {
                                    background: #3b82f6;
                                    color: white;
                                    border: none;
                                    padding: 0.75rem 1.5rem;
                                    border-radius: 6px;
                                    cursor: pointer;
                                    font-size: 0.875rem;
                                    font-weight: 500;
                                }
                                button:hover {
                                    background: #2563eb;
                                }
                            </style>
                        </head>
                        <body>
                            <div class="container">
                                <div class="icon">
                                    <svg width="32" height="32" fill="white" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </div>
                                <h1>Sin Conexión</h1>
                                <p>No hay conexión a internet. Algunas funciones pueden no estar disponibles.</p>
                                <button onclick="window.location.reload()">Reintentar</button>
                            </div>
                        </body>
                        </html>
                    `, {
                        headers: { 'Content-Type': 'text/html' }
                    });
                });
        });
}

// Handle API requests (cache for GET, queue for POST/PUT/DELETE)
function handleApiRequest(request) {
    if (request.method === 'GET') {
        return fetch(request)
            .then(response => {
                if (response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(DYNAMIC_CACHE)
                        .then(cache => cache.put(request, responseClone));
                }
                return response;
            })
            .catch(() => {
                return caches.match(request)
                    .then(cachedResponse => {
                        if (cachedResponse) {
                            // Add offline indicator to cached API responses
                            return cachedResponse.json()
                                .then(data => {
                                    data._offline = true;
                                    return new Response(JSON.stringify(data), {
                                        headers: { 'Content-Type': 'application/json' }
                                    });
                                });
                        }
                        
                        return new Response(JSON.stringify({
                            error: 'No hay conexión a internet',
                            offline: true
                        }), {
                            status: 503,
                            headers: { 'Content-Type': 'application/json' }
                        });
                    });
            });
    }

    // For non-GET requests, try to queue them for later
    return fetch(request)
        .catch(() => {
            // Queue the request for when connection is restored
            queueRequest(request);
            
            return new Response(JSON.stringify({
                message: 'Solicitud guardada. Se procesará cuando se restaure la conexión.',
                queued: true
            }), {
                status: 202,
                headers: { 'Content-Type': 'application/json' }
            });
        });
}

// Queue requests for later processing
function queueRequest(request) {
    // This would typically use IndexedDB for persistence
    console.log('Queueing request for later:', request.url);
}

// Check if URL is a static asset
function isStaticAsset(url) {
    return url.includes('.css') || 
           url.includes('.js') || 
           url.includes('.png') || 
           url.includes('.jpg') || 
           url.includes('.svg') || 
           url.includes('.woff') || 
           url.includes('.woff2') ||
           STATIC_ASSETS.includes(url);
}

// Background sync for queued requests
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(processQueuedRequests());
    }
});

function processQueuedRequests() {
    // Process queued requests when connection is restored
    console.log('Processing queued requests...');
    return Promise.resolve();
}

// Push notifications
self.addEventListener('push', event => {
    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body,
            icon: '/images/icon-192x192.png',
            badge: '/images/badge-72x72.png',
            vibrate: [100, 50, 100],
            data: {
                dateOfArrival: Date.now(),
                primaryKey: data.primaryKey || 1
            },
            actions: data.actions || []
        };

        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

// Notification click handler
self.addEventListener('notificationclick', event => {
    event.notification.close();

    if (event.action === 'view') {
        event.waitUntil(
            clients.openWindow(event.notification.data.url || '/')
        );
    }
});
