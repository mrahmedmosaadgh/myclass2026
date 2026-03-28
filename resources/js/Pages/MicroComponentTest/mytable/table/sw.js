const CACHE_NAME = 'schedule-cache-v2';
const urlsToCache = [
  './',
  './index.html',
  './full_schedule.json',
  './manifest.json',
  './icon.png',
  './notification1.mp3'
];

// Install the service worker and cache the files
self.addEventListener('install', event => {
  console.log('[SW] Installing service worker');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[SW] Caching app shell');
        return cache.addAll(urlsToCache);
      })
      .then(() => self.skipWaiting())
  );
});

// Activate the service worker and clean up old caches
self.addEventListener('activate', event => {
  console.log('[SW] Activating service worker');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('[SW] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Serve cached files when offline with network fallback
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Return cached version or try network
        if (response) {
          console.log('[SW] Serving from cache:', event.request.url);
          return response;
        }
        
        console.log('[SW] Fetching from network:', event.request.url);
        return fetch(event.request).catch(() => {
          // Return a basic offline page for HTML requests
          if (event.request.destination === 'document') {
            return caches.match('./offline.html');
          }
        });
      })
  );
});