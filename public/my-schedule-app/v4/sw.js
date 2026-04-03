// Service Worker for Schedule App V4
const CACHE_NAME = 'schedule-app-v4-v1';
const urlsToCache = [
  '/my-fly-schedule-app/v4/',
  '/my-fly-schedule-app/v4/manifest.webmanifest',
  '/my-fly-schedule-app/v4/icon.svg'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
  console.log('[SW V4] Install event triggered');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[SW V4] Caching app shell');
        return cache.addAll(urlsToCache);
      })
      .catch((error) => {
        console.error('[SW V4] Failed to cache app shell:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[SW V4] Activate event triggered');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME && cacheName.startsWith('schedule-app-')) {
            console.log('[SW V4] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  // Only handle GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // Handle API routes differently (network-first for API calls)
  if (event.request.url.includes('/api/v4/')) {
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          // Cache successful API responses
          if (response.ok) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseClone);
            });
          }
          return response;
        })
        .catch(() => {
          // Try to serve from cache if network fails
          return caches.match(event.request);
        })
    );
    return;
  }

  // For other requests, use cache-first strategy
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Return cached version if available
        if (response) {
          return response;
        }

        // Otherwise, fetch from network
        return fetch(event.request)
          .then((response) => {
            // Don't cache non-successful responses
            if (!response.ok) {
              return response;
            }

            // Clone the response since it can only be consumed once
            const responseClone = response.clone();
            
            // Cache the fetched resource
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseClone);
            });

            return response;
          })
          .catch((error) => {
            console.error('[SW V4] Fetch failed:', error);
            
            // Return a custom offline page for HTML requests
            if (event.request.headers.get('accept').includes('text/html')) {
              return new Response(
                `
                <!DOCTYPE html>
                <html>
                <head>
                  <title>Offline - Schedule App V4</title>
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
                      background: #f8fafc;
                      color: #334155;
                    }
                    .offline-container {
                      text-align: center;
                      padding: 2rem;
                      max-width: 400px;
                    }
                    .offline-icon {
                      font-size: 4rem;
                      margin-bottom: 1rem;
                    }
                    h1 {
                      margin: 0 0 1rem 0;
                      font-size: 1.5rem;
                    }
                    p {
                      margin: 0 0 2rem 0;
                      line-height: 1.6;
                    }
                    .retry-btn {
                      background: #3b82f6;
                      color: white;
                      border: none;
                      padding: 0.75rem 1.5rem;
                      border-radius: 8px;
                      font-size: 1rem;
                      cursor: pointer;
                      transition: background 0.2s;
                    }
                    .retry-btn:hover {
                      background: #2563eb;
                    }
                  </style>
                </head>
                <body>
                  <div class="offline-container">
                    <div class="offline-icon">📴</div>
                    <h1>You're Offline</h1>
                    <p>Schedule App V4 is working offline. Your data is saved locally and will sync when you're back online.</p>
                    <button class="retry-btn" onclick="window.location.reload()">Try Again</button>
                  </div>
                </body>
                </html>
                `,
                {
                  status: 200,
                  statusText: 'OK',
                  headers: { 'Content-Type': 'text/html' }
                }
              );
            }
          });
      })
  );
});

// Background sync for offline data
self.addEventListener('sync', (event) => {
  if (event.tag === 'background-sync-v4') {
    console.log('[SW V4] Background sync triggered');
    event.waitUntil(doBackgroundSync());
  }
});

// Background sync function
async function doBackgroundSync() {
  try {
    // Get all pending sync data from IndexedDB or localStorage
    // This would need to be implemented based on your data storage strategy
    console.log('[SW V4] Performing background sync');
    
    // Example: Sync queued data to server
    // const pendingData = await getPendingSyncData();
    // for (const data of pendingData) {
    //   await syncToServer(data);
    // }
    
    console.log('[SW V4] Background sync completed');
  } catch (error) {
    console.error('[SW V4] Background sync failed:', error);
  }
}

// Push notification handling
self.addEventListener('push', (event) => {
  console.log('[SW V4] Push event received');
  
  const options = {
    body: 'Your schedule data has been synced.',
    icon: '/my-fly-schedule-app/v4/icon.svg',
    badge: '/my-fly-schedule-app/v4/icon.svg',
    vibrate: [200, 100, 200],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Open App',
        icon: '/my-fly-schedule-app/v4/icon.svg'
      },
      {
        action: 'close',
        title: 'Close',
        icon: '/my-fly-schedule-app/v4/icon.svg'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('Schedule App V4', options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', (event) => {
  console.log('[SW V4] Notification click received');
  
  event.notification.close();

  if (event.action === 'explore') {
    // Open the app
    event.waitUntil(
      clients.openWindow('/my-fly-schedule-app/v4')
    );
  }
});

// Message handling for communication with the app
self.addEventListener('message', (event) => {
  console.log('[SW V4] Message received:', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'GET_VERSION') {
    event.ports[0].postMessage({ version: '4.0.0' });
  }
});

console.log('[SW V4] Service Worker loaded successfully');
