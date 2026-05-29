import Alpine from "alpinejs";
import { bootModernPhoneInputs } from "../../../../../../Storefront/Resources/assets/public/js/lib/modernPhoneInput";
import { registerOtpDigitInput } from "../../../../../../Storefront/Resources/assets/public/js/lib/otpDigitInput";

window.Alpine = Alpine;
window.bootModernPhoneInputs = bootModernPhoneInputs;

registerOtpDigitInput(Alpine);

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bootModernPhoneInputs);
} else {
    bootModernPhoneInputs();
}

Alpine.start();
