import { trans, formatCurrency } from "./functions";
import { notify, SweetNotification } from "./components/Toaster";
import { bootFlashes } from "./components/SweetNotification";
import { initOtpDigitInput } from "./lib/otpDigitInput";
import Alpine from "alpinejs";
import * as bootstrap from "bootstrap/dist/js/bootstrap.js";
import "./vendors/axios";

/**
 * Load datepicker / phone widgets only when matching inputs exist.
 * Keeps flatpickr and intl-tel-input off most storefront pages.
 */
async function bootFormEnhancements() {
    const hasDatepicker = document.querySelector("input.modern-datepicker");
    const hasPhone = document.querySelector("input.modern-phone-input");

    if (hasDatepicker) {
        const { initModernDatepickers } = await import("./lib/modernDatepicker");
        initModernDatepickers();
    }

    if (hasPhone) {
        const { bootModernPhoneInputs } = await import("./lib/modernPhoneInput");
        bootModernPhoneInputs();
    }
}

function onReady(fn) {
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", fn);
    } else {
        fn();
    }
}

onReady(() => {
    bootFormEnhancements();
    bootFlashes();
});

window.Alpine = Alpine;
window.bootstrap = bootstrap;
window.trans = trans;
window.formatCurrency = formatCurrency;
window.notify = notify;
window.SweetNotification = SweetNotification;
window.initOtpDigitInput = initOtpDigitInput;

Alpine.data("App", () => ({
    hideOverlay() {
        const layoutStore = this.$store.layout;

        layoutStore.closeSidebarMenu();
        layoutStore.closeSidebarCart();
        layoutStore.closeSidebarFilter();
        layoutStore.closeLocalizationMenu();
    },
}));
