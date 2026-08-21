import Alpine from "alpinejs";
import { bootModernPhoneInputs } from "../../../../../../Storefront/Resources/assets/public/js/lib/modernPhoneInput";
import { registerOtpDigitInput } from "../../../../../../Storefront/Resources/assets/public/js/lib/otpDigitInput";
import SweetNotification, {
    bootFlashes,
    success,
    error,
    warning,
    info,
    notify,
} from "@admin/js/SweetNotification";

window.Alpine = Alpine;
window.bootModernPhoneInputs = bootModernPhoneInputs;
window.SweetNotification = SweetNotification;
window.notify = Object.assign(notify, { success, error, warning, info });
window.success = success;
window.error = error;
window.warning = warning;
window.info = info;

registerOtpDigitInput(Alpine);

function boot() {
    bootModernPhoneInputs();
    bootFlashes();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
} else {
    boot();
}

Alpine.start();
