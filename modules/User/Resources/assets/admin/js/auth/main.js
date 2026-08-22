import Alpine from "alpinejs";
import { registerOtpDigitInput } from "../../../../../../Storefront/Resources/assets/public/js/lib/otpDigitInput";

window.Alpine = Alpine;

registerOtpDigitInput(Alpine);

let phoneModulePromise = null;
let sweetModulePromise = null;

function loadPhoneModule() {
    if (!phoneModulePromise) {
        phoneModulePromise = import(
            /* webpackChunkName: "auth-phone" */
            "../../../../../../Storefront/Resources/assets/public/js/lib/modernPhoneInput"
        );
    }

    return phoneModulePromise;
}

function loadSweetModule() {
    if (!sweetModulePromise) {
        sweetModulePromise = import(
            /* webpackChunkName: "auth-sweet" */
            "@admin/js/SweetNotification"
        ).then((mod) => {
            const SweetNotification = mod.default;

            window.SweetNotification = SweetNotification;
            window.notify = Object.assign(mod.notify, {
                success: mod.success,
                error: mod.error,
                warning: mod.warning,
                info: mod.info,
            });
            window.success = mod.success;
            window.error = mod.error;
            window.warning = mod.warning;
            window.info = mod.info;

            return mod;
        });
    }

    return sweetModulePromise;
}

window.bootModernPhoneInputs = async (root = document) => {
    const mod = await loadPhoneModule();

    mod.bootModernPhoneInputs(root);

    return mod;
};

async function bootNotifications() {
    const mod = await loadSweetModule();

    mod.bootFlashes();

    return mod;
}

function boot() {
    if (document.getElementById("sweet-notification-flashes")) {
        bootNotifications();
        return;
    }

    if ("requestIdleCallback" in window) {
        requestIdleCallback(() => {
            loadSweetModule();
        }, { timeout: 2500 });
    } else {
        setTimeout(() => loadSweetModule(), 2000);
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
} else {
    boot();
}

Alpine.start();
