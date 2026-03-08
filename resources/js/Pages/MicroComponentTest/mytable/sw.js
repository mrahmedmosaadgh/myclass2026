/**
 * Service Worker for Schedule Timeline PWA
 * Handles caching, offline functionality, and push notifications
 */

const CACHE_NAME = 'schedule-timeline-v1';
const STATIC_CACHE = 'static-v1';
const DYNAMIC_CACHE = 'dynamic-v1';

// Assets to cache immediately
const STATIC_ASSETS = [
  '/',
  '/index.html',
  '/main.js',
  '/App.vue',
  '/ScheduleTimeline.vue',
  '/TimelineHeader.vue',
  '/TimelineRow.vue',
  '/TimelineBar.vue',
  '/TimeIndicator.vue',
  '/manifest.json',
  '/offline.html'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...');
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('[Service Worker] Caching static assets');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => {
        console.log('[Service Worker] Installation complete, skipping waiting');
        return self.skipWaiting();
      })
      .catch((error) => {
        console.error('[Service Worker] Cache installation failed:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating...');
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE && cacheName !== CACHE_NAME) {
              console.log('[Service Worker] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('[Service Worker] Activation complete, claiming clients');
        return self.clients.claim();
      })
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }

  // Skip chrome-extension and other non-http(s) requests
  if (!url.protocol.startsWith('http')) {
    return;
  }

  event.respondWith(
    caches.match(request)
      .then((cachedResponse) => {
        if (cachedResponse) {
          console.log('[Service Worker] Serving from cache:', request.url);
          
          // Return cached response but update cache in background (stale-while-revalidate)
          event.waitUntil(updateCache(request));
          
          return cachedResponse;
        }

        // Not in cache - fetch from network
        return fetchAndCache(request);
      })
      .catch((error) => {
        console.error('[Service Worker] Fetch failed:', error);
        
        // If it's a navigation request, serve offline page
        if (request.mode === 'navigate') {
          return caches.match('/offline.html');
        }
        
        // Return a generic error response
        return new Response('Offline - Content not available', {
          status: 503,
          statusText: 'Service Unavailable'
        });
      })
  );
});

// Fetch from network and cache the response
async function fetchAndCache(request) {
  const response = await fetch(request);
  
  // Only cache successful responses
  if (response.ok) {
    const cache = await caches.open(DYNAMIC_CACHE);
    cache.put(request, response.clone());
  }
  
  return response;
}

// Update cache in background
async function updateCache(request) {
  try {
    const response = await fetch(request);
    
    if (response.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      await cache.put(request, response);
    }
  } catch (error) {
    console.log('[Service Worker] Background cache update failed:', error);
  }
}

// Handle messages from main thread
self.addEventListener('message', (event) => {
  console.log('[Service Worker] Message received:', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CLIENTS_CLAIM') {
    self.clients.claim();
  }
  
  if (event.data && event.data.type === 'CACHE_URLS') {
    event.waitUntil(
      caches.open(DYNAMIC_CACHE)
        .then((cache) => cache.addAll(event.data.urls))
    );
  }
});

// Push notification handler
self.addEventListener('push', (event) => {
  console.log('[Service Worker] Push received:', event);
  
  const options = {
    body: event.data ? event.data.text() : 'New schedule update!',
    icon: './icons/icon-96x96.png',
    badge: './icons/icon-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'view_schedule',
        title: 'View Schedule',
        icon: './icons/icon-72x72.png'
      },
      {
        action: 'close',
        title: 'Close',
        icon: './icons/icon-72x72.png'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('Schedule Timeline', options)
  );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
  console.log('[Service Worker] Notification clicked:', event.action);
  event.notification.close();
  
  if (event.action === 'view_schedule') {
    event.waitUntil(
      clients.openWindow('/index.html')
    );
  }
});

// Background sync handler
self.addEventListener('sync', (event) => {
  console.log('[Service Worker] Sync triggered:', event.tag);
  
  if (event.tag === 'sync-schedule') {
    event.waitUntil(syncScheduleData());
  }
});

async function syncScheduleData() {
  try {
    // Simulate syncing schedule data
    console.log('[Service Worker] Syncing schedule data...');
    // In a real app, you would fetch updated schedule data from the server
    const response = await fetch('/api/schedule/updates');
    const data = await response.json();
    
    // Broadcast update to all clients
    const clientsList = await clients.matchAll();
    clientsList.forEach((client) => {
      client.postMessage({
        type: 'SCHEDULE_UPDATED',
        data: data
      });
    });
  } catch (error) {
    console.error('[Service Worker] Sync failed:', error);
  }
}

console.log('[Service Worker] Service Worker loaded successfully');
