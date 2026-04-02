const CACHE_NAME = 'my-schedule-app-v3-1.0.0';
const urlsToCache = [
  '/my-fly-schedule-app/v3',
  '/my-fly-schedule-app/v3/manifest.webmanifest',
  '/my-fly-schedule-app/v3/icon.svg'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
  console.log('[SW V3] Installing service worker...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[SW V3] Caching app shell');
        return cache.addAll(urlsToCache);
      })
      .catch((error) => {
        console.error('[SW V3] Failed to cache app shell:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[SW V3] Activating service worker...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME && cacheName.startsWith('my-schedule-app-')) {
            console.log('[SW V3] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Cache hit - return response
        if (response) {
          console.log('[SW V3] Serving from cache:', event.request.url);
          return response;
        }

        // Not in cache - fetch from network
        return fetch(event.request)
          .then((response) => {
            // Check if we received a valid response
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // Clone the response for caching
            const responseToCache = response.clone();

            // Cache the new resource
            caches.open(CACHE_NAME)
              .then((cache) => {
                console.log('[SW V3] Caching new resource:', event.request.url);
                cache.put(event.request, responseToCache);
              })
              .catch((error) => {
                console.error('[SW V3] Failed to cache resource:', error);
              });

            return response;
          })
          .catch((error) => {
            console.error('[SW V3] Network request failed:', error);
            
            // Return a custom offline page for HTML requests
            if (event.request.headers.get('accept').includes('text/html')) {
              return caches.match('/my-fly-schedule-app/v3');
            }
          });
      })
  );
});

// Background sync for offline data
self.addEventListener('sync', (event) => {
  if (event.tag === 'schedule-sync') {
    console.log('[SW V3] Background sync triggered');
    event.waitUntil(doBackgroundSync());
  }
});

// Handle push notifications
self.addEventListener('push', (event) => {
  if (event.data) {
    const data = event.data.json();
    const options = {
      body: data.body || 'New schedule update available',
      icon: '/my-fly-schedule-app/v3/icon.svg',
      badge: '/my-fly-schedule-app/v3/icon.svg',
      vibrate: [100, 50, 100],
      data: {
        url: '/my-fly-schedule-app/v3'
      }
    };

    event.waitUntil(
      self.registration.showNotification(data.title || 'Schedule App V3', options)
    );
  }
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  
  event.waitUntil(
    clients.openWindow(event.notification.data.url || '/my-fly-schedule-app/v3')
  );
});

// Background sync function
async function doBackgroundSync() {
  try {
    // Get all stored offline data
    const offlineData = await getOfflineData();
    
    // Sync with server
    for (const data of offlineData) {
      try {
        await syncDataToServer(data);
        await removeOfflineData(data.id);
      } catch (error) {
        console.error('[SW V3] Failed to sync data:', error);
      }
    }
    
    console.log('[SW V3] Background sync completed');
  } catch (error) {
    console.error('[SW V3] Background sync failed:', error);
  }
}

// Helper functions for offline data management
async function getOfflineData() {
  // This would integrate with IndexedDB or localStorage
  return [];
}

async function syncDataToServer(data) {
  // This would sync data to your server
  return Promise.resolve();
}

async function removeOfflineData(id) {
  // This would remove synced data from local storage
  return Promise.resolve();
}
