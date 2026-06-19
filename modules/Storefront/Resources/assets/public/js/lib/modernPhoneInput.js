import intlTelInput from "intl-tel-input/intlTelInputWithUtils";
import "intl-tel-input/styles";

function defaultCountry() {
    return (window.AestheticCart?.defaultPhoneCountry || "my").toLowerCase();
}

function isVisiblePhoneInput(input) {
    if (!input.isConnected) {
        return false;
    }

    const panel = input.closest(".customer-login-tabs__panel, .whatsapp-otp-modal");

    if (panel) {
        const style = window.getComputedStyle(panel);

        if (style.display === "none" || style.visibility === "hidden") {
            return false;
        }
    }

    return input.offsetParent !== null || input.getClientRects().length > 0;
}

function destroyModernPhoneInput(input) {
    if (input._iti) {
        try {
            input._iti.destroy();
        } catch (error) {
            // Ignore teardown errors from partially initialized instances.
        }

        input._iti = null;
    }

    const wrapper = input.parentElement;

    if (wrapper?.classList.contains("iti")) {
        const parent = wrapper.parentElement;

        if (parent) {
            parent.insertBefore(input, wrapper);
            wrapper.remove();
        }
    }
}

export function buildPhoneInputOptions(el) {
    const preferred = (el.dataset.preferredCountries || "my,sg").split(",");

    // intl-tel-input v29+ option names (v17 options are ignored with console warnings).
    return {
        initialCountry: (el.dataset.initialCountry || defaultCountry()).toLowerCase(),
        countryOrder: preferred,
        separateDialCode: true,
        numberDisplayFormat: "INTERNATIONAL",
        formatAsYouType: true,
        strictMode: true,
        placeholderNumberPolicy: "AGGRESSIVE",
    };
}

export function initModernPhoneInputs(root = document) {
    root.querySelectorAll("input.modern-phone-input").forEach((input) => {
        if (root === document && !isVisiblePhoneInput(input)) {
            return;
        }

        const sync = () => {
            if (!input._iti) {
                return;
            }

            const number = input._iti.getNumber() || "";
            input.dataset.fullNumber = number;

            input.dispatchEvent(
                new CustomEvent("phone:change", {
                    bubbles: true,
                    detail: {
                        number,
                        valid: Boolean(input._iti.isValidNumber()),
                    },
                })
            );
        };

        if (input._iti) {
            sync();

            return;
        }

        destroyModernPhoneInput(input);

        try {
            const iti = intlTelInput(input, buildPhoneInputOptions(input));
            input._iti = iti;

            if (input.value) {
                iti.setNumber(input.value);
            }

            input.addEventListener("blur", sync);
            input.addEventListener("countrychange", sync);
            input.addEventListener("input", sync);

            sync();
        } catch (error) {
            console.error("Failed to initialize phone input.", error);
        }
    });
}

export function getPhoneInputE164(input) {
    if (!input) {
        return "";
    }

    if (input._iti) {
        return input._iti.getNumber() || "";
    }

    return input.dataset.fullNumber || input.value || "";
}

export function formatPhoneE164(phone) {
    const raw = String(phone || "").trim();

    if (!raw) {
        return "";
    }

    const compact = raw.replace(/\s+/g, "");

    if (/^\+[1-9]\d{6,14}$/.test(compact)) {
        return compact;
    }

    let digits = raw.replace(/\D+/g, "");

    if (!digits) {
        return "";
    }

    if (digits.startsWith("00")) {
        digits = digits.slice(2);
    } else if (digits.startsWith("0")) {
        digits = "60" + digits.slice(1);
    } else if (digits.startsWith("61") && !digits.startsWith("60")) {
        digits = "60" + digits.slice(1);
    }

    return digits ? `+${digits}` : "";
}

export function normalizePhoneInputsOnSubmit(root = document) {
    root.querySelectorAll("form").forEach((form) => {
        if (form.dataset.phoneSubmitBound) {
            return;
        }

        form.dataset.phoneSubmitBound = "1";

        form.addEventListener("submit", () => {
            form.querySelectorAll("input.modern-phone-input").forEach((input) => {
                const e164 = getPhoneInputE164(input);

                if (e164) {
                    input.value = e164;
                }
            });
        });
    });
}

export function bootModernPhoneInputs(root = document) {
    initModernPhoneInputs(root);
    normalizePhoneInputsOnSubmit(root);
}
