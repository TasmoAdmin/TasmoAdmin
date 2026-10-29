// Only static assets and the offline page are cached. Pages, device status and
// commands always go to the network so the UI never shows stale device state.
const CACHE_NAME = "tasmoadmin-v1";

function getOfflineUrl(scope) {
  return `${scope}offline`;
}

function isStaticAsset(url, scope) {
  return url.startsWith(`${scope}resources/`);
}

// URLs the offline page needs (stylesheet, icon) so it renders without a server.
function extractAssetUrls(html, scope, origin) {
  const urls = new Set();
  const pattern = /(?:href|src)="([^"]+)"/g;
  let match;
  while ((match = pattern.exec(html)) !== null) {
    const url = new URL(match[1], origin).href;
    if (isStaticAsset(url, scope)) {
      urls.add(url);
    }
  }
  return [...urls];
}

// Asset URLs carry a ?_= cache tag; keep only the newest copy of each file.
function stripSearch(url) {
  const parsed = new URL(url);
  parsed.search = "";
  return parsed.href;
}

async function precacheOfflinePage(scope) {
  const cache = await caches.open(CACHE_NAME);
  const offlineUrl = getOfflineUrl(scope);
  const response = await fetch(offlineUrl, { cache: "no-store" });
  if (!response.ok) {
    return;
  }
  const html = await response.clone().text();
  await cache.put(offlineUrl, response);
  await Promise.all(
    extractAssetUrls(html, scope, self.location.origin).map((url) =>
      putAsset(cache, url),
    ),
  );
}

async function putAsset(cache, url) {
  const response = await fetch(url);
  if (!response.ok) {
    return response;
  }
  const bare = stripSearch(url);
  const keys = await cache.keys();
  await Promise.all(
    keys
      .filter((key) => key.url !== url && stripSearch(key.url) === bare)
      .map((key) => cache.delete(key)),
  );
  await cache.put(url, response.clone());
  return response;
}

async function staleWhileRevalidate(event) {
  const cache = await caches.open(CACHE_NAME);
  const cached = await cache.match(event.request);
  // Offline, an outdated cache tag still resolves to the newest cached copy.
  const network = putAsset(cache, event.request.url).catch(
    () => cached || cache.match(event.request, { ignoreSearch: true }),
  );
  event.waitUntil(network);
  return cached || network;
}

async function networkWithOfflineFallback(request, scope) {
  try {
    return await fetch(request);
  } catch (error) {
    const cache = await caches.open(CACHE_NAME);
    const offline = await cache.match(getOfflineUrl(scope));
    if (offline) {
      return offline;
    }
    throw error;
  }
}

if (typeof self !== "undefined" && typeof self.skipWaiting === "function") {
  self.addEventListener("install", (event) => {
    event.waitUntil(
      precacheOfflinePage(self.registration.scope).then(() =>
        self.skipWaiting(),
      ),
    );
  });

  self.addEventListener("activate", (event) => {
    event.waitUntil(
      caches
        .keys()
        .then((names) =>
          Promise.all(
            names
              .filter((name) => name !== CACHE_NAME)
              .map((name) => caches.delete(name)),
          ),
        )
        .then(() => self.clients.claim()),
    );
  });

  self.addEventListener("fetch", (event) => {
    const { request } = event;
    const scope = self.registration.scope;
    if (request.method !== "GET") {
      return;
    }
    if (request.mode === "navigate") {
      event.respondWith(networkWithOfflineFallback(request, scope));
      return;
    }
    if (isStaticAsset(request.url, scope)) {
      event.respondWith(staleWhileRevalidate(event));
    }
  });
}

if (typeof module !== "undefined") {
  module.exports = {
    CACHE_NAME,
    extractAssetUrls,
    getOfflineUrl,
    isStaticAsset,
    stripSearch,
  };
}
