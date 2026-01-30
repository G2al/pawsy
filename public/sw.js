const CACHE_NAME = 'pawsy-v2';
const ASSETS = ['/', '/index.html', '/sign-in.html', '/sign-up.html', '/forgot-password.html', '/reset-password.html', '/my-pets.html', '/my-bookings.html', '/settings.html', '/search.html', '/offers-rewards.html', '/profile.html', '/css_front/style.css', '/js_front/app.js', '/js_front/auth.js', '/js_front/script.js', '/vender/bootstrap/css/bootstrap.min.css', '/vender/bootstrap/js/bootstrap.bundle.min.js', '/img/logo-pawsy.png', '/img/paw-icon-full-color.svg', '/img/paw-icon-without-color.svg', '/img/paw-icon-with-color-for-header.svg'];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;
  if (!event.request.url.startsWith('http')) return;

  const url = new URL(event.request.url);
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(fetch(event.request));
    return;
  }

  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).then(response => {
        const copy = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
        return response;
      }).catch(() => caches.match(event.request).then(res => res || caches.match('/sign-in.html')))
    );
    return;
  }

  event.respondWith(
    caches.match(event.request).then(cached =>
      cached || fetch(event.request).then(response => {
        const copy = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
        return response;
      })
    )
  );
});
