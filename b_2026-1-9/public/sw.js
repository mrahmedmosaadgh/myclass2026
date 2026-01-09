/**
 * Enhanced Service Worker for Offline-First Education Management System
 * Includes push notifications and offline caching capabilities
 */

const CACHE_NAME = 'education-app-v1';
const OFFLINE_URL = '/offline-test';

// Files to cache for offline use (will be populated dynamically)
const CACHE_URLS = [
  '/',
  '/offline-test',
  '/api/health-check'
];

// Install event - cache essential files
self.addEventListener('install', event => {
  console.log('Service Worker: Installing...');

  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Service Worker: Caching files');
        // Filter out unsupported schemes before caching
        const validUrls = CACHE_URLS.filter(url => {
          try {
            const urlObj = new URL(url, self.location.origin);
            return urlObj.protocol === 'http:' || urlObj.protocol === 'https:';
          } catch (e) {
            console.warn('Service Worker: Invalid URL skipped:', url);
            return false;
          }
        });
        return cache.addAll(validUrls);
      })
      .then(() => {
        console.log('Service Worker: Installed successfully');
        return self.skipWaiting();
      })
      .catch(error => {
        console.error('Service Worker: Installation failed', error);
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
            if (cacheName !== CACHE_NAME) {
              console.log('Service Worker: Deleting old cache', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('Service Worker: Activated successfully');
        return self.clients.claim();
      })
  );
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip unsupported schemes (chrome-extension, etc.)
  if (url.protocol !== 'http:' && url.protocol !== 'https:') {
    return;
  }

  // Handle navigation requests (page loads)
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then(response => {
          // If online, cache the response and return it
          if (response && response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME)
              .then(cache => {
                cache.put(request, responseClone);
              })
              .catch(error => {
                console.warn('Failed to cache navigation response:', error);
              });
          }
          return response;
        })
        .catch(error => {
          console.log('Navigation request failed, trying cache:', error);
          // If offline, try to serve from cache
          return caches.match(request)
            .then(cachedResponse => {
              if (cachedResponse) {
                console.log('Serving navigation from cache');
                return cachedResponse;
              }

              // If no cached version, serve the offline test page
              console.log('No cached navigation, serving offline page');
              return caches.match(OFFLINE_URL)
                .then(offlineResponse => {
                  if (offlineResponse) {
                    return offlineResponse;
                  }
                  // If offline page not cached, return a basic HTML response
                  return new Response(
                    '<!DOCTYPE html><html><head><title>Offline</title></head><body><h1>You are offline</h1><p>Please check your internet connection.</p></body></html>',
                    {
                      status: 200,
                      statusText: 'OK',
                      headers: { 'Content-Type': 'text/html' }
                    }
                  );
                });
            });
        })
    );
    return;
  }

  // Handle API requests
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(
      fetch(request)
        .then(response => {
          // Cache successful API responses (but not HEAD requests)
          if (response && response.status === 200 && request.method !== 'HEAD') {
            const responseClone = response.clone();
            caches.open(CACHE_NAME)
              .then(cache => {
                cache.put(request, responseClone);
              })
              .catch(error => {
                console.warn('Failed to cache API response:', error);
              });
          }
          return response;
        })
        .catch(error => {
          console.log('API request failed, trying cache:', error);
          // If offline, try to serve from cache
          return caches.match(request)
            .then(cachedResponse => {
              if (cachedResponse) {
                console.log('Serving API from cache');
                return cachedResponse;
              }

              // For health check, return a simple OK response
              if (url.pathname === '/api/health-check') {
                return new Response(JSON.stringify({ status: 'offline' }), {
                  status: 200,
                  headers: { 'Content-Type': 'application/json' }
                });
              }

              // Return a generic offline response for other API calls
              return new Response(
                JSON.stringify({
                  error: 'Offline',
                  message: 'This request is not available offline'
                }),
                {
                  status: 503,
                  statusText: 'Service Unavailable',
                  headers: { 'Content-Type': 'application/json' }
                }
              );
            })
            .catch(cacheError => {
              console.error('Cache lookup failed:', cacheError);
              // Fallback response if cache lookup fails
              return new Response(
                JSON.stringify({
                  error: 'Service Worker Error',
                  message: 'Unable to process request'
                }),
                {
                  status: 500,
                  statusText: 'Internal Server Error',
                  headers: { 'Content-Type': 'application/json' }
                }
              );
            });
        })
    );
    return;
  }

  // Handle static assets (CSS, JS, images)
  if (request.destination === 'style' ||
      request.destination === 'script' ||
      request.destination === 'image' ||
      url.pathname.startsWith('/build/') ||
      url.pathname.startsWith('/css/') ||
      url.pathname.startsWith('/js/') ||
      url.pathname.startsWith('/images/')) {

    event.respondWith(
      caches.match(request)
        .then(cachedResponse => {
          if (cachedResponse) {
            return cachedResponse;
          }

          return fetch(request)
            .then(response => {
              if (response.status === 200) {
                const responseClone = response.clone();
                caches.open(CACHE_NAME)
                  .then(cache => {
                    cache.put(request, responseClone);
                  });
              }
              return response;
            });
        })
    );
    return;
  }

  // For all other requests, try network first, then cache
  event.respondWith(
    fetch(request)
      .catch(error => {
        console.log('Other request failed, trying cache:', error);
        return caches.match(request)
          .then(cachedResponse => {
            if (cachedResponse) {
              return cachedResponse;
            }
            // If no cache match, return a 404 response
            return new Response('Not Found', {
              status: 404,
              statusText: 'Not Found'
            });
          });
      })
  );
});

// Handle background sync (when connection is restored)
self.addEventListener('sync', event => {
  console.log('Service Worker: Background sync triggered', event.tag);

  if (event.tag === 'offline-sync') {
    event.waitUntil(
      // Trigger the sync queue processing
      self.clients.matchAll()
        .then(clients => {
          clients.forEach(client => {
            client.postMessage({
              type: 'BACKGROUND_SYNC',
              action: 'PROCESS_QUEUE'
            });
          });
        })
    );
  }
});

// Push notification handler
self.addEventListener('push', function(event) {
    console.log('Push event received:', event);

    if (!event.data) {
        console.log('Push event but no data');
        return;
    }

    let data;
    try {
        data = event.data.json();
    } catch (e) {
        console.error('Failed to parse push event data:', e);
        return;
    }

    const options = {
        body: data.body,
        icon: data.icon || '/icon.png',
        badge: data.badge || '/badge.png',
        image: data.image,
        tag: data.tag || 'default',
        vibrate: data.vibrate || [100, 50, 100],
        data: {
            url: data.url,
            ...data.data
        },
        actions: data.actions || [
            { action: 'open', title: 'Open' },
            { action: 'close', title: 'Close' }
        ],
        silent: data.silent || false,
        renotify: data.renotify || false,
        requireInteraction: data.requireInteraction || false
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    // Handle custom actions
    if (event.action === 'close') {
        return;
    }

    const urlToOpen = event.notification.data.url || '/dashboard';
    const promiseChain = clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    })
    .then((windowClients) => {
        // Check if there is already a window/tab open with the target URL
        for (let client of windowClients) {
            if (client.url === urlToOpen && 'focus' in client) {
                return client.focus();
            }
        }
        // If no window/tab is already open, open a new one
        if (clients.openWindow) {
            return clients.openWindow(urlToOpen);
        }
    })
    .catch((error) => {
        console.error('Error handling notification click:', error);
    });

    event.waitUntil(promiseChain);
});

// Handle notification close event
self.addEventListener('notificationclose', function(event) {
    // You can add analytics or logging here if needed
    const dismissedNotification = event.notification;
    const notificationData = dismissedNotification.data;
    console.log('Notification was closed', notificationData);
});
