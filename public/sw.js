/**
 * Service Worker der installierbaren CleanStart-App.
 *
 * Datenschutz: Es werden NUR öffentliche, nicht personenbezogene Dateien zwischengespeichert
 * (Offline-Notfallseite, Icons, Manifest, gebaute CSS/JS/Schriften unter /build/).
 * Seiten mit persönlichen Daten (Dashboard, Profil, Verlauf …) werden nie im Cache abgelegt.
 * Ohne Netz zeigt die App stattdessen /offline.html mit Notrufnummern und Atemübung.
 */

const VERSION = 'v1';
const STATIC_CACHE = `cleanstart-static-${VERSION}`;
const ASSET_CACHE = `cleanstart-assets-${VERSION}`;
const MAX_ASSETS = 40;

const PRECACHE = [
    '/offline.html',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-512.png',
    '/icons/apple-touch-icon.png',
    '/icons/favicon-32.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key.startsWith('cleanstart-') && ![STATIC_CACHE, ASSET_CACHE].includes(key))
                        .map((key) => caches.delete(key)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Seiten: immer aus dem Netz, nie zwischenspeichern – offline die Notfallseite zeigen.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match('/offline.html')));
        return;
    }

    // Gebaute Dateien haben Hash-Namen und ändern sich nie: Cache zuerst.
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(cacheFirst(request, ASSET_CACHE));
        return;
    }

    if (PRECACHE.includes(url.pathname)) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
    }
});

async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    const response = await fetch(request);
    if (response.ok) {
        const cache = await caches.open(cacheName);
        await cache.put(request, response.clone());
        if (cacheName === ASSET_CACHE) {
            await trim(cache);
        }
    }

    return response;
}

/** Ältere Build-Dateien früherer Versionen entfernen (älteste zuerst). */
async function trim(cache) {
    const keys = await cache.keys();
    await Promise.all(keys.slice(0, Math.max(0, keys.length - MAX_ASSETS)).map((key) => cache.delete(key)));
}
