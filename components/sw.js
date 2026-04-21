self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open('salem-v1').then((cache) => {
      return cache.addAll([
        '/',
        '/index.php',
        '/public/logo-icon.jpeg'
      ]);
    })
  );
});

self.addEventListener('fetch', (e) => {
  e.respondWith(caches.match(e.request).then((res) => res || fetch(e.request)));
});