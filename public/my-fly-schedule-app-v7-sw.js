/* eslint-disable no-restricted-globals */

const VERSION = 'v7';
const CACHE_NAME = `my-fly-schedule-${VERSION}`;

// Keep it minimal and safe. This SW enables basic offline caching for the shell.
// It won't cache API responses.

const CORE_ASSETS = [
  '/my-fly-schedule-app/ver7',
  '/my-fly-schedule-app/v7/manifest.webmanifest',
  '/my-fly-schedule-app/v7/icon.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(CORE_ASSETS))
      .then(() => self.skipWaiting())
      .catch(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((k) => k.startsWith('my-fly-schedule-') && k !== CACHE_NAME)
          .map((k) => caches.delete(k))
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Only handle same-origin GET requests.
  if (req.method !== 'GET') return;
  if (url.origin !== self.location.origin) return;

  // Never cache API.
  if (url.pathname.startsWith('/api/')) return;

  event.respondWith(
    caches.match(req).then((cached) => {
      if (cached) return cached;
      return fetch(req).then((res) => {
        // Cache successful basic responses.
        if (res && res.status === 200 && res.type === 'basic') {
          const copy = res.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(req, copy));
        }
        return res;
      });
    })
  );
});
