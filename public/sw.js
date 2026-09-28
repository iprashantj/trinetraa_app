const CACHE_VERSION = "trinetraa-v2";
const STATIC_CACHE = CACHE_VERSION + "-static";
const IMAGE_CACHE = CACHE_VERSION + "-images";
const API_CACHE = CACHE_VERSION + "-api";

const PRECACHE_ASSETS = [
  "/",
  "/eyewears",
  "/about",
  "/contact",
  "/services",
  "/brands",
  "/offers",
  "/appointment",
  "/css/globals.css",
  "/css/public.css",
  "/js/app.js",
  "/offline.html",
];

self.addEventListener("install", (e) => {
  e.waitUntil(
    caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE_ASSETS.filter(Boolean))).then(() => self.skipWaiting())
  );
});

self.addEventListener("activate", (e) => {
  e.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => !k.startsWith(CACHE_VERSION)).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", (e) => {
  const { request } = e;
  if (request.method !== "GET") return;
  const url = new URL(request.url);

  // Never cache admin pages or auth API
  if (url.pathname.startsWith("/admin") || url.pathname.startsWith("/api/auth") || url.pathname.startsWith("/api/customer/auth")) return;

  // API read endpoints: stale-while-revalidate (5 min TTL)
  if (url.pathname.startsWith("/api/public/")) {
    e.respondWith(staleWhileRevalidate(API_CACHE, request, 300));
    return;
  }

  // Images: cache-first (1 week)
  if (/\.(webp|jpg|jpeg|png|gif|svg|ico)$/i.test(url.pathname)) {
    e.respondWith(cacheFirst(IMAGE_CACHE, request));
    return;
  }

  // Static assets: cache-first
  if (/\.(css|js|woff2|woff|ttf)$/i.test(url.pathname)) {
    e.respondWith(cacheFirst(STATIC_CACHE, request));
    return;
  }

  // HTML pages: network-first, fallback to cache then offline page
  if (request.headers.get("accept") && request.headers.get("accept").includes("text/html")) {
    e.respondWith(networkFirstWithOfflineFallback(request));
    return;
  }

  // Everything else: network-first
  e.respondWith(fetch(request).catch(() => caches.match(request)));
});

// Cache-first: return cached response immediately; update in background if stale
async function cacheFirst(cacheName, request) {
  const cache = await caches.open(cacheName);
  const cached = await cache.match(request);
  if (cached) return cached;
  const res = await fetch(request);
  if (res && res.status === 200) cache.put(request, res.clone());
  return res;
}

// Stale-while-revalidate with TTL
async function staleWhileRevalidate(cacheName, request, ttlSeconds) {
  const cache = await caches.open(cacheName);
  const cached = await cache.match(request);
  const fetchPromise = fetch(request).then((res) => {
    if (res && res.status === 200) cache.put(request, res.clone());
    return res;
  }).catch(() => cached);
  if (cached) {
    const dateHeader = cached.headers.get("date");
    if (dateHeader) {
      const age = (Date.now() - new Date(dateHeader).getTime()) / 1000;
      if (age > ttlSeconds) return fetchPromise;
    }
    return cached;
  }
  return fetchPromise;
}

// Network-first for HTML, offline fallback
async function networkFirstWithOfflineFallback(request) {
  try {
    const res = await fetch(request);
    if (res && res.status === 200) {
      const cache = await caches.open(STATIC_CACHE);
      cache.put(request, res.clone());
    }
    return res;
  } catch {
    const cached = await caches.match(request);
    if (cached) return cached;
    const offline = await caches.match("/offline.html");
    return offline || new Response("<h1>Offline</h1><p>Please check your internet connection.</p>", { headers: { "Content-Type": "text/html" } });
  }
}

// Handle push notifications
self.addEventListener("push", (e) => {
  const data = e.data ? e.data.json() : { title: "Trinetraa Optician", body: "You have a new notification." };
  e.waitUntil(
    self.registration.showNotification(data.title || "Trinetraa Optician", {
      body: data.body || "",
      icon: "/icons/icon-192.png",
      badge: "/icons/icon-192.png",
      data: { url: data.url || "/" },
    })
  );
});

self.addEventListener("notificationclick", (e) => {
  e.notification.close();
  const url = (e.notification.data && e.notification.data.url) || "/";
  e.waitUntil(clients.openWindow(url));
});
