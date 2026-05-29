import { trans, formatCurrency } from "./functions";
import { notify } from "./components/Toaster";
import { initModernDatepickers } from "./lib/modernDatepicker";
import { bootModernPhoneInputs } from "./lib/modernPhoneInput";
import { initOtpDigitInput } from "./lib/otpDigitInput";
import Alpine from "alpinejs";
import jQuery from "jquery";
import * as bootstrap from "bootstrap/dist/js/bootstrap.js";
import "./vendors/axios";

function bootFormEnhancements() {
    initModernDatepickers();
    bootModernPhoneInputs();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bootFormEnhancements);
} else {
    bootFormEnhancements();
}

window.Alpine = Alpine;
window.bootstrap = bootstrap;
window.$ = window.jQuery = jQuery;
window.trans = trans;
window.formatCurrency = formatCurrency;
window.notify = notify;
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
