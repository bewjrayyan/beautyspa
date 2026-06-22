Alpine.store("layout", {
    sidebarMenuOpen: false,
    sidebarCartOpen: false,
    sidebarFilterOpen: false,
    localizationMenuOpen: false,
    overlay: false,

    get isOpenSidebarMenu() {
        return this.sidebarMenuOpen;
    },

    get isOpenSidebarCart() {
        return this.sidebarCartOpen;
    },

    get isOpenSidebarFilter() {
        return this.sidebarFilterOpen;
    },

    get isOpenlocalizationMenu() {
        return this.localizationMenuOpen;
    },

    syncOverlay() {
        this.overlay =
            this.sidebarMenuOpen ||
            this.sidebarCartOpen ||
            this.sidebarFilterOpen ||
            this.localizationMenuOpen;

        document.body.classList.toggle("mobile-overlay-open", this.overlay);
    },

    openSidebarMenu() {
        this.sidebarMenuOpen = true;

        this.syncOverlay();
    },

    closeSidebarMenu() {
        this.sidebarMenuOpen = false;

        this.syncOverlay();
    },

    openSidebarCart(event) {
        if (event) {
            event.preventDefault();
        }

        if (window.location.pathname.endsWith("/checkout")) {
            window.location.href = "/cart";

            return;
        }

        if (Alpine.store("cart").fetching) {
            return;
        }

        this.sidebarCartOpen = true;

        this.syncOverlay();
    },

    closeSidebarCart() {
        this.sidebarCartOpen = false;

        this.syncOverlay();
    },

    openSidebarFilter() {
        this.sidebarFilterOpen = true;

        this.syncOverlay();
    },

    closeSidebarFilter() {
        this.sidebarFilterOpen = false;

        this.syncOverlay();
    },

    openLocalizationMenu() {
        this.localizationMenuOpen = true;

        this.syncOverlay();
    },

    closeLocalizationMenu() {
        this.localizationMenuOpen = false;

        this.syncOverlay();
    },
});
