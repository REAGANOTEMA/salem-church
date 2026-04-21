// Service Worker for Salem Dominion Ministries PWA
// Universal support for iOS, Android, and all devices

const CACHE_NAME = 'salem-ministries-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/sermons.php',
  '/events.php',
  '/news.php',
  '/about.php',
  '/contact.php',
  '/register.php',
  '/login.php',
  '/public/site.webmanifest',
  '/public/logo-icon.jpeg',
  '/assets/mobile-responsive.css',
  '/assets/universal-device-support.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
  'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap'
];

// Install event - cache resources
self.addEventListener('install', function(event) {
  console.log('Service Worker: Installing...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        console.log('Service Worker: Caching files');
        return cache.addAll(urlsToCache);
      })
      .then(function() {
        console.log('Service Worker: Installation complete');
        return self.skipWaiting();
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', function(event) {
  console.log('Service Worker: Activating...');
  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          if (cacheName !== CACHE_NAME) {
            console.log('Service Worker: Clearing old cache');
            return caches.delete(cacheName);
          }
        })
      );
    }).then(function() {
      console.log('Service Worker: Activation complete');
      return self.clients.claim();
    })
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', function(event) {
  const request = event.request;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip external requests except for CDN resources
  if (url.origin !== location.origin && 
      !url.hostname.includes('cdn.jsdelivr.net') && 
      !url.hostname.includes('cdnjs.cloudflare.com') && 
      !url.hostname.includes('fonts.googleapis.com') && 
      !url.hostname.includes('fonts.gstatic.com')) {
    return;
  }
  
  event.respondWith(
    caches.open(CACHE_NAME).then(function(cache) {
      return cache.match(request).then(function(response) {
        // Return cached version or fetch from network
        if (response) {
          // Update cache in background
          fetch(request).then(function(networkResponse) {
            if (networkResponse.ok) {
              cache.put(request, networkResponse.clone());
            }
          }).catch(function(error) {
            console.log('Service Worker: Network update failed', error);
          });
          return response;
        }
        
        // Fetch from network and cache
        return fetch(request).then(function(networkResponse) {
          if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
            return networkResponse;
          }
          
          const responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then(function(cache) {
            cache.put(request, responseClone);
          });
          
          return networkResponse;
        }).catch(function(error) {
          console.log('Service Worker: Network failed, serving offline page');
          
          // Serve offline page for navigation requests
          if (request.mode === 'navigate') {
            return caches.match('/index.php');
          }
          
          // Return error for other requests
          return new Response('Offline', {
            status: 503,
            statusText: 'Service Unavailable'
          });
        });
      });
    })
  );
});

// Background sync for offline functionality
self.addEventListener('sync', function(event) {
  console.log('Service Worker: Background sync', event.tag);
  
  if (event.tag === 'background-sync') {
    event.waitUntil(
      // Handle background sync tasks
      console.log('Service Worker: Syncing data')
    );
  }
});

// Push notification support
self.addEventListener('push', function(event) {
  console.log('Service Worker: Push message received');
  
  const options = {
    body: event.data ? event.data.text() : 'New message from Salem Dominion Ministries',
    icon: '/public/logo-icon.jpeg',
    badge: '/public/logo-icon.jpeg',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Explore',
        icon: '/public/logo-icon.jpeg'
      },
      {
        action: 'close',
        title: 'Close',
        icon: '/public/logo-icon.jpeg'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('Salem Dominion Ministries', options)
  );
});

// Notification click handler
self.addEventListener('notificationclick', function(event) {
  console.log('Service Worker: Notification click received');
  
  event.notification.close();
  
  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});

// Periodic background sync (supported on Android)
self.addEventListener('periodicsync', function(event) {
  console.log('Service Worker: Periodic sync');
  
  if (event.tag === 'content-sync') {
    event.waitUntil(
      // Periodic content sync
      console.log('Service Worker: Syncing content periodically')
    );
  }
});

// Network state monitoring
self.addEventListener('online', function(event) {
  console.log('Service Worker: Network is online');
});

self.addEventListener('offline', function(event) {
  console.log('Service Worker: Network is offline');
});

// Message handling from main app
self.addEventListener('message', function(event) {
  console.log('Service Worker: Message received', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'GET_VERSION') {
    event.ports[0].postMessage({ version: CACHE_NAME });
  }
});

// Performance monitoring
self.addEventListener('fetch', function(event) {
  const start = performance.now();
  
  event.respondWith(
    (async function() {
      const response = await fetch(event.request);
      const end = performance.now();
      
      if (end - start > 1000) {
        console.warn('Service Worker: Slow request detected', event.request.url, end - start + 'ms');
      }
      
      return response;
    })()
  );
});

// Cache cleanup on storage quota exceeded
self.addEventListener('quotaexceeded', function(event) {
  console.log('Service Worker: Storage quota exceeded');
  
  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});
