const CACHE_NAME = 'artsci-field-v8';
const ASSETS = [
    '/Artsci Logo REAL 1.webp',
    '/manifest.json'
];

// Install Event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// Activate Event
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event - Network First with Cache Fallback
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET' || !event.request.url.startsWith(self.location.origin)) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(response => {
                const responseClone = response.clone();
                caches.open(CACHE_NAME).then(cache => {
                    cache.put(event.request, responseClone);
                });
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});

// Push Event - Handle Web Push Notifications
self.addEventListener('push', event => {
    if (!event.data) return;

    let payload = {};
    try {
        payload = event.data.json();
    } catch (e) {
        payload = {
            title: 'ARTSCI Notification',
            body: event.data.text()
        };
    }

    const options = {
        body: payload.body,
        icon: payload.icon || '/logo.png',
        badge: '/logo.png',
        vibrate: [100, 50, 100],
        data: {
            url: payload.url || '/field/dashboard'
        }
    };

    event.waitUntil(
        self.registration.showNotification(payload.title, options)
    );
});

// Notification Click Event
self.addEventListener('notificationclick', event => {
    event.notification.close();

    const targetUrl = event.notification.data.url;
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
            for (const client of clientList) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
