// Schedule App V5 Service Worker
// Offline-first with proper cache handling

const CACHE_NAME = 'schedule-app-v5-1';
const STATIC_CACHE = 'schedule-app-v5-static-1';
const RUNTIME_CACHE = 'schedule-app-v5-runtime-1';

// App shell URLs to cache on install
const APP_SHELL = [
  '/my-fly-schedule-app/v5',
  '/my-fly-schedule-app/v5/manifest.webmanifest',
  '/my-fly-schedule-app/v5/icon.svg'
];

// Install event: cache app shell
self.addEventListener('install', (event) => {
  console.log('[V5 SW] Install event');
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('[V5 SW] Caching app shell');
        return cache.addAll(APP_SHELL);
      })
      .then(() => {
        console.log('[V5 SW] App shell cached');
        return self.skipWaiting();
      })
  );
});

// Activate event: clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[V5 SW] Activate event');
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== CACHE_NAME && cacheName !== STATIC_CACHE && cacheName !== RUNTIME_CACHE) {
              console.log('[V5 SW] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('[V5 SW] Claiming clients');
        return self.clients.claim();
      })
  );
});

// Fetch event: serve from cache when offline
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') return;

  // Skip API calls (let app handle them)
  if (url.pathname.startsWith('/api/')) return;

  // Skip external resources
  if (url.origin !== self.location.origin) return;

  // Handle app shell requests
  if (APP_SHELL.includes(url.pathname)) {
    event.respondWith(
      caches.match(request)
        .then((response) => {
          return response || fetch(request);
        })
    );
    return;
  }

  // Handle other requests with network-first strategy
  event.respondWith(
    fetch(request)
      .then((response) => {
        // Only cache successful responses
        if (response.ok && response.status === 200) {
          const responseClone = response.clone();
          caches.open(RUNTIME_CACHE)
            .then((cache) => {
              cache.put(request, responseClone);
            });
        }
        return response;
      })
      .catch(() => {
        // Network failed, try cache
        return caches.match(request);
      })
  );
});

// Message event: handle messages from app
self.addEventListener('message', (event) => {
  const { type, payload } = event.data;

  switch (type) {
    case 'SKIP_WAITING':
      self.skipWaiting();
      break;
    case 'GET_CACHE_SIZE':
      getCacheSize().then(size => {
        event.ports[0].postMessage({ type: 'CACHE_SIZE', size });
      });
      break;
    case 'CLEAR_CACHE':
      clearCache().then(() => {
        event.ports[0].postMessage({ type: 'CACHE_CLEARED' });
      });
      break;
    default:
      console.log('[V5 SW] Unknown message type:', type);
  }
});

// Helper functions
async function getCacheSize() {
  const cache = await caches.open(RUNTIME_CACHE);
  const requests = await cache.keys();
  let totalSize = 0;

  for (const request of requests) {
    const response = await cache.match(request);
    if (response) {
      const blob = await response.blob();
      totalSize += blob.size;
    }
  }

  return totalSize;
}

async function clearCache() {
  await caches.delete(RUNTIME_CACHE);
  console.log('[V5 SW] Runtime cache cleared');
}

// Periodic background sync (if supported)
self.addEventListener('periodicsync', (event) => {
  if (event.tag === 'sync-data') {
    event.waitUntil(syncData());
  }
});

// Background sync
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-data') {
    event.waitUntil(syncData());
  }
});

async function syncData() {
  try {
    // This would be implemented by the app
    console.log('[V5 SW] Background sync triggered');
  } catch (error) {
    console.error('[V5 SW] Background sync failed:', error);
  }
}

console.log('[V5 SW] Service worker loaded');
