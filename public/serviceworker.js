const staticCacheName = "pwa-v" + new Date().getTime();
const basePath = new URL(self.location.href).pathname.replace(/serviceworker\.js$/, "");
const offlineUrl = basePath + "offline.html";

const manifestKeys = {
    css: "modules/Storefront/Resources/assets/public/sass/app.scss",
    appJs: "modules/Storefront/Resources/assets/public/js/app.js",
    mainJs: "modules/Storefront/Resources/assets/public/js/main.js",
};

/*
|--------------------------------------------------------------------------
| Cache On Install
|--------------------------------------------------------------------------
*/
self.addEventListener("install", (event) => {
    self.skipWaiting();

    event.waitUntil(
        caches.open(staticCacheName).then(async (cache) => {
            const urls = [offlineUrl, basePath + "build/manifest.json"];

            const manifestRes = await fetch(basePath + "build/manifest.json").catch(
                () => null
            );

            if (manifestRes?.ok) {
                const assets = await manifestRes.json().catch(() => ({}));

                for (const key of Object.values(manifestKeys)) {
                    const file = assets[key]?.file;

                    if (file) {
                        urls.push(basePath + "build/" + file);
                    }
                }
            }

            const iconSizes = [48, 72, 96, 128, 144, 152, 192, 384, 512];

            for (const size of iconSizes) {
                urls.push(basePath + "pwa/icons/" + size + "x" + size + ".png");
            }

            await cacheUrls(cache, urls);
        })
    );
});

async function cacheUrls(cache, urls) {
    await Promise.all(
        [...new Set(urls)].map(async (url) => {
            try {
                const response = await fetch(url, { credentials: "same-origin" });

                if (response.ok) {
                    await cache.put(url, response);
                }
            } catch (error) {
                // Skip missing assets (icons, etc.) so install does not fail.
            }
        })
    );
}

/*
|--------------------------------------------------------------------------
| Clear Cache On Activate
|--------------------------------------------------------------------------
*/
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((cacheName) => cacheName.startsWith("pwa-"))
                    .filter((cacheName) => cacheName !== staticCacheName)
                    .map((cacheName) => caches.delete(cacheName))
            );
        })
    );

    self.clients.claim();
});

/*
|--------------------------------------------------------------------------
| Network-first for pages; cache-first for static build assets only.
|--------------------------------------------------------------------------
*/
self.addEventListener("fetch", (event) => {
    if (event.request.method !== "GET") {
        return;
    }

    const requestUrl = new URL(event.request.url);

    if (requestUrl.origin !== self.location.origin) {
        return;
    }

    const isStaticAsset =
        requestUrl.pathname.startsWith(basePath + "build/") ||
        requestUrl.pathname.startsWith(basePath + "pwa/");

    if (isStaticAsset) {
        event.respondWith(cacheFirst(event.request));

        return;
    }

    if (event.request.mode === "navigate") {
        event.respondWith(networkFirstWithOfflineFallback(event.request));

        return;
    }
});

async function cacheFirst(request) {
    const cached = await caches.match(request);

    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);

        if (response.ok) {
            const cache = await caches.open(staticCacheName);
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        return offlineResponse();
    }
}

async function networkFirstWithOfflineFallback(request) {
    try {
        return await fetch(request);
    } catch (error) {
        const cached = await caches.match(request);

        if (cached) {
            return cached;
        }

        return offlineResponse();
    }
}

async function offlineResponse() {
    const cachedOffline = await caches.match(offlineUrl);

    if (cachedOffline) {
        return cachedOffline;
    }

    return new Response(
        "<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Offline</title></head><body><h1>You are offline</h1><p><button onclick=\"location.reload()\">Try again</button></p></body></html>",
        {
            status: 503,
            headers: { "Content-Type": "text/html; charset=utf-8" },
        }
    );
}

const pwaVersion = 1779717071;
