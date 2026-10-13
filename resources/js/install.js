/**
 * Installierbare App (PWA): Service-Worker-Registrierung und „App installieren“-Karte im Profil.
 */

let deferredPrompt = null;

// Das Ereignis kann vor dem Start von Alpine kommen – daher früh abfangen und merken.
// Kein preventDefault(): Chrome darf seinen eigenen Installationshinweis weiterhin zeigen.
window.addEventListener('beforeinstallprompt', (event) => {
    deferredPrompt = event;
    window.dispatchEvent(new CustomEvent('cleanstart:installable'));
});

export function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) return;

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Ohne Service Worker funktioniert die App weiterhin, nur nicht offline.
        });
    });
}

export function installApp() {
    return {
        canPrompt: deferredPrompt !== null,
        installed: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
        isIos: /iphone|ipad|ipod/i.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1),

        init() {
            window.addEventListener('cleanstart:installable', () => (this.canPrompt = true));
            window.addEventListener('appinstalled', () => {
                this.installed = true;
                this.canPrompt = false;
            });
        },

        async install() {
            if (deferredPrompt === null) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            this.canPrompt = false;
            if (outcome === 'accepted') this.installed = true;
        },
    };
}
