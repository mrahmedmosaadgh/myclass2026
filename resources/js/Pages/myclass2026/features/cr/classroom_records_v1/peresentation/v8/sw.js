// Service Worker for Presentation Builder V8 - Offline First
const CACHE_NAME = 'presentation-builder-v8-v1'
const STATIC_CACHE = 'presentation-builder-v8-static-v1'

// Files to cache for offline functionality
const STATIC_ASSETS = [
  '/',
  '/classroom-records/presentation/builder-v8',
  // Add any critical CSS/JS files here
]

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('[SW] Installing service worker v1')
  
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('[SW] Caching static assets')
        return cache.addAll(STATIC_ASSETS)
      })
      .then(() => self.skipWaiting())
  )
})

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating service worker v1')
  
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames
            .filter((name) => name !== STATIC_CACHE && name !== CACHE_NAME)
            .map((name) => {
              console.log('[SW] Deleting old cache:', name)
              return caches.delete(name)
            })
        )
      })
      .then(() => self.clients.claim())
  )
})

// Fetch event - network first, then cache
self.addEventListener('fetch', (event) => {
  const { request } = event
  const url = new URL(request.url)

  // Skip non-GET requests and external resources
  if (request.method !== 'GET' || url.origin !== self.location.origin) {
    return
  }

  // Handle different types of requests
  if (url.pathname.includes('/api/')) {
    // API requests - network first, cache fallback
    event.respondWith(
      fetch(request)
        .then((response) => {
          // Cache successful API responses for 5 minutes
          if (response.ok) {
            const responseClone = response.clone()
            caches.open(CACHE_NAME)
              .then((cache) => cache.put(request, responseClone))
          }
          return response
        })
        .catch(() => {
          // Fallback to cache when offline
          return caches.match(request)
        })
    )
  } else {
    // Static assets - cache first, network fallback
    event.respondWith(
      caches.match(request)
        .then((response) => {
          if (response) {
            return response
          }
          
          // Not in cache, fetch from network
          return fetch(request)
            .then((response) => {
              // Cache successful responses
              if (response.ok) {
                const responseClone = response.clone()
                caches.open(STATIC_CACHE)
                  .then((cache) => cache.put(request, responseClone))
              }
              return response
            })
        })
        .catch(() => {
          // Offline fallback
          if (request.destination === 'document') {
            // Return offline page for navigation requests
            return new Response(
              `
              <!DOCTYPE html>
              <html>
                <head>
                  <title>Offline - Presentation Builder V8</title>
                  <meta charset="utf-8">
                  <meta name="viewport" content="width=device-width, initial-scale=1">
                  <style>
                    body {
                      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                      display: flex;
                      align-items: center;
                      justify-content: center;
                      min-height: 100vh;
                      margin: 0;
                      background: #f3f4f6;
                      color: #374151;
                    }
                    .offline-container {
                      text-align: center;
                      max-width: 400px;
                      padding: 2rem;
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
                      background: #6366f1;
                      color: white;
                      border: none;
                      padding: 0.75rem 1.5rem;
                      border-radius: 0.5rem;
                      font-size: 1rem;
                      cursor: pointer;
                    }
                    .retry-btn:hover {
                      background: #4f46e5;
                    }
                  </style>
                </head>
                <body>
                  <div class="offline-container">
                    <div class="offline-icon">📱</div>
                    <h1>You're Offline</h1>
                    <p>Presentation Builder V8 is available offline. Your work is saved locally and will sync when you're back online.</p>
                    <button class="retry-btn" onclick="window.location.reload()">
                      Try Again
                    </button>
                  </div>
                </body>
              </html>
              `,
              {
                status: 200,
                statusText: 'OK',
                headers: {
                  'Content-Type': 'text/html'
                }
              }
            )
          }
        })
    )
  }
})

// Background sync for offline data
self.addEventListener('sync', (event) => {
  if (event.tag === 'presentation-sync') {
    event.waitUntil(syncPresentationData())
  }
})

// Sync presentation data when back online
async function syncPresentationData() {
  try {
    // Get all pending sync data from IndexedDB
    const pendingData = await getPendingSyncData()
    
    // Sync each pending operation
    for (const data of pendingData) {
      try {
        await fetch('/api/presentation/sync', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data)
        })
        
        // Remove synced data from pending queue
        await removePendingSyncData(data.id)
      } catch (error) {
        console.error('[SW] Failed to sync data:', error)
      }
    }
  } catch (error) {
    console.error('[SW] Sync failed:', error)
  }
}

// IndexedDB helpers for offline queue management
function getPendingSyncData() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('presentation-sync-db', 1)
    
    request.onerror = () => reject(request.error)
    request.onsuccess = () => {
      const db = request.result
      const transaction = db.transaction(['sync-queue'], 'readonly')
      const store = transaction.objectStore('sync-queue')
      const getAllRequest = store.getAll()
      
      getAllRequest.onerror = () => reject(getAllRequest.error)
      getAllRequest.onsuccess = () => resolve(getAllRequest.result)
    }
  })
}

function removePendingSyncData(id) {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('presentation-sync-db', 1)
    
    request.onerror = () => reject(request.error)
    request.onsuccess = () => {
      const db = request.result
      const transaction = db.transaction(['sync-queue'], 'readwrite')
      const store = transaction.objectStore('sync-queue')
      const deleteRequest = store.delete(id)
      
      deleteRequest.onerror = () => reject(deleteRequest.error)
      deleteRequest.onsuccess = () => resolve()
    }
  })
}

// Push notification handler
self.addEventListener('push', (event) => {
  const options = {
    body: 'Your presentation has been synced successfully',
    icon: '/icon-192x192.png',
    badge: '/badge-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    }
  }

  event.waitUntil(
    self.registration.showNotification('Presentation Builder V8', options)
  )
})
