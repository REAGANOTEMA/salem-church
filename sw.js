const CACHE_NAME = 'salem-v2';
const OFFLINE_URL = 'offline.html';

// Core assets to cache immediately
const CORE_ASSETS = [
  'index.php',
  'offline.html',
  'manifest.json',
  'public/logo-icon.jpeg',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'
];

// Additional assets to cache on demand
const ADDITIONAL_ASSETS = [
  'login.php',
  'register.php',
  'donate.php',
  'contact.php',
  'ministries.php',
  'sermons.php',
  'events.php',
  'gallery.php',
  'news.php',
  'testimonials.php',
  'leadership.php',
  'about.php',
  'children_ministry.php',
  'prophetic-school.php',
  'assets/mobile-responsive.css'
];

// Install event - cache core assets
self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(CORE_ASSETS);
    })
  );
  // Force activation immediately
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name !== CACHE_NAME)
          .map((name) => caches.delete(name))
      );
    })
  );
  // Take control of all pages immediately
  self.clients.claim();
});

// Fetch event - intelligent caching strategy
self.addEventListener('fetch', (e) => {
  // Skip non-GET requests
  if (e.request.method !== 'GET') return;

  e.respondWith(
    caches.match(e.request).then((cachedResponse) => {
      // Strategy 1: Cache first for core assets
      if (CORE_ASSETS.includes(e.request.url) || 
          e.request.url.includes('logo-icon.jpeg') ||
          e.request.url.includes('bootstrap') ||
          e.request.url.includes('font-awesome')) {
        if (cachedResponse) {
          return cachedResponse;
        }
        return fetch(e.request).then((response) => {
          return caches.open(CACHE_NAME).then((cache) => {
            cache.put(e.request, response.clone());
            return response;
          });
        }).catch(() => {
          if (e.request.mode === 'navigate') {
            return caches.match(OFFLINE_URL);
          }
          return new Response('Offline', { status: 503 });
        });
      }

      // Strategy 2: Network first for dynamic content (PHP pages)
      if (e.request.url.includes('.php')) {
        return fetch(e.request)
          .then((response) => {
            // Cache successful responses
            if (response.ok) {
              return caches.open(CACHE_NAME).then((cache) => {
                cache.put(e.request, response.clone());
                return response;
              });
            }
            return response;
          })
          .catch(() => {
            // Fallback to cache if network fails
            if (cachedResponse) {
              return cachedResponse;
            }
            // Show offline page for navigation requests
            if (e.request.mode === 'navigate') {
              return caches.match(OFFLINE_URL);
            }
            return new Response('Offline', { status: 503 });
          });
      }

      // Strategy 3: Cache first for static assets
      if (cachedResponse) {
        return cachedResponse;
      }

      // Fetch from network and cache
      return fetch(e.request)
        .then((response) => {
          if (response.ok) {
            return caches.open(CACHE_NAME).then((cache) => {
              cache.put(e.request, response.clone());
              return response;
            });
          }
          return response;
        })
        .catch(() => {
          // Final fallback
          if (e.request.mode === 'navigate') {
            return caches.match(OFFLINE_URL);
          }
          return new Response('Offline', { status: 503 });
        });
    })
  );
});

// Background sync for when connection is restored
self.addEventListener('sync', (e) => {
  if (e.tag === 'background-sync') {
    e.waitUntil(doBackgroundSync());
  }
});

function doBackgroundSync() {
  // Here you could sync any data that was stored while offline
  return Promise.resolve();
}
