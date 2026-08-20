const PWA_DISMISS_KEY = "fleetcart_pwa_install_dismissed";

Alpine.data("PwaInstallModal", () => ({
    modal: null,
    deferredPrompt: null,
    installing: false,
    platform: "desktop",
    canInstall: false,

    init() {
        if (this.shouldSkip()) {
            return;
        }

        this.platform = this.detectPlatform();
        this.modal = bootstrap.Modal.getOrCreateInstance("#pwaInstallModal");

        window.addEventListener("beforeinstallprompt", (event) => {
            event.preventDefault();
            this.deferredPrompt = event;
            this.canInstall = true;
        });

        window.addEventListener("appinstalled", () => {
            this.deferredPrompt = null;
            this.canInstall = false;
            this.dismiss(true);
        });

        window.addEventListener("hide.bs.modal", () => {
            if (document.activeElement instanceof HTMLElement) {
                document.activeElement.blur();
            }
        });

        setTimeout(() => {
            if (!this.shouldSkip()) {
                this.modal.show();
            }
        }, 5000);
    },

    get stepsTitle() {
        const titles = {
            ios: AestheticCart.langs?.["storefront::pwa.ios_title"],
            android: AestheticCart.langs?.["storefront::pwa.android_title"],
            desktop: AestheticCart.langs?.["storefront::pwa.desktop_title"],
        };

        return titles[this.platform] || titles.desktop;
    },

    get steps() {
        const keys = {
            ios: [
                "storefront::pwa.ios_step_1",
                "storefront::pwa.ios_step_2",
                "storefront::pwa.ios_step_3",
            ],
            android: [
                "storefront::pwa.android_step_1",
                "storefront::pwa.android_step_2",
            ],
            desktop: [
                "storefront::pwa.desktop_step_1",
                "storefront::pwa.desktop_step_2",
            ],
        };

        return (keys[this.platform] || keys.desktop).map(
            (key) => AestheticCart.langs?.[key] || ""
        );
    },

    shouldSkip() {
        if (window.matchMedia("(display-mode: standalone)").matches) {
            return true;
        }

        if (window.navigator.standalone === true) {
            return true;
        }

        try {
            return localStorage.getItem(PWA_DISMISS_KEY) === "1";
        } catch (error) {
            return false;
        }
    },

    detectPlatform() {
        const userAgent = navigator.userAgent.toLowerCase();

        if (/iphone|ipad|ipod/.test(userAgent)) {
            return "ios";
        }

        if (/android/.test(userAgent)) {
            return "android";
        }

        return "desktop";
    },

    async install() {
        if (!this.deferredPrompt || this.installing) {
            return;
        }

        this.installing = true;

        try {
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;

            if (outcome === "accepted") {
                this.dismiss(true);
            }
        } finally {
            this.deferredPrompt = null;
            this.canInstall = false;
            this.installing = false;
        }
    },

    dismiss(permanent = false) {
        if (permanent) {
            try {
                localStorage.setItem(PWA_DISMISS_KEY, "1");
            } catch (error) {
                // ignore storage failures
            }
        }

        if (this.modal) {
            this.modal.hide();
        }
    },
}));
