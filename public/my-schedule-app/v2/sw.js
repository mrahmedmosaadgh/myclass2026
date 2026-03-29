const CACHE_NAME = 'fly-schedule-v2-1.0.0';
const APP_SHELL = [
  '/my-fly-schedule-app/v2',
  '/my-fly-schedule-app/v2/manifest.webmanifest',
  '/my-fly-schedule-app/v2/notification1.mp3'
];

// Install event: cache app shell
self.addEventListener('install', (event) => {
  console.log('[SW] Installing service worker');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[SW] Caching app shell');
        return cache.addAll(APP_SHELL);
      })
      .then(() => {
        console.log('[SW] Skip waiting on install');
        return self.skipWaiting();
      })
  );
});

// Activate event: clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating service worker');
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== CACHE_NAME) {
              console.log('[SW] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('[SW] Claiming clients');
        return self.clients.claim();
      })
  );
});

// Fetch event: serve from cache when offline
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Only handle same-origin requests for the schedule app
  if (!url.pathname.startsWith('/my-fly-schedule-app/v2')) {
    return;
  }

  // Skip non-GET requests and API calls
  if (request.method !== 'GET' || url.pathname.includes('/api/')) {
    return;
  }

  event.respondWith(
    caches.match(request)
      .then((response) => {
        // Return cached version if available
        if (response) {
          console.log('[SW] Serving from cache:', request.url);
          return response;
        }

        // Otherwise fetch from network
        console.log('[SW] Fetching from network:', request.url);
        return fetch(request)
          .then((response) => {
            // Don't cache non-successful responses
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // Clone the response since it can only be consumed once
            const responseToCache = response.clone();

            // Cache the fetched resource for future use
            caches.open(CACHE_NAME)
              .then((cache) => {
                console.log('[SW] Caching new resource:', request.url);
                cache.put(request, responseToCache);
              });

            return response;
          })
          .catch(() => {
            // If network fails and it's a navigation request, serve the app shell
            if (request.mode === 'navigate') {
              console.log('[SW] Network failed, serving app shell');
              return caches.match('/my-fly-schedule-app/v2');
            }
          });
      })
  );
});

// Handle background sync (optional for future features)
self.addEventListener('sync', (event) => {
  console.log('[SW] Background sync event:', event.tag);
  // Future: sync offline changes, notifications, etc.
});

// Handle push notifications (optional for future features)
self.addEventListener('push', (event) => {
  console.log('[SW] Push event received');
  // Future: handle push notifications for schedule reminders
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
  console.log('[SW] Notification click:', event.notification.tag);
  event.notification.close();

  // Open the app to the relevant view
  event.waitUntil(
    clients.openWindow('/my-fly-schedule-app/v2')
  );
});
