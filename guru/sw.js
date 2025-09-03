
// Service Worker untuk Guru App

const CACHE_NAME = 'ava-guru-cache-v1';
const urlsToCache = [
    '/guru/',
    'dashboard.php',
    'jadwal_calendar.php',
    'scan.php',
    'stream.php',
    '../user/assets/images/icon-192x192.png',
    'https://cdn.tailwindcss.com',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap'
];

// Install event: opens a cache and adds main assets to it
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
    );
});

// Fetch event: serves assets from cache if available, otherwise fetches from network
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Cache hit - return response
                if (response) {
                    return response;
                }
                // Not in cache - fetch from network
                return fetch(event.request);
            }
        )
    );
});

// Activate event: cleans up old caches
self.addEventListener('activate', event => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Push event: handles incoming push notifications
self.addEventListener('push', event => {
    const data = event.data ? event.data.json() : {};
    console.log('Push notification received.', data);

    const title = data.title || 'AVA Guru Notification';
    const options = {
        body: data.body || 'Anda memiliki notifikasi baru.',
        icon: '../user/assets/images/icon-192x192.png',
        badge: '../user/assets/images/icon-192x192.png',
        data: data.url || '/guru/dashboard.php'
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

// Notification click event
self.addEventListener('notificationclick', event => {
    event.notification.close();
    
    event.waitUntil(
        clients.openWindow(event.notification.data)
    );
});
