import intlTelInput from "intl-tel-input/intlTelInputWithUtils";
import "intl-tel-input/styles";

function defaultCountry() {
    return (window.AestheticCart?.defaultPhoneCountry || "my").toLowerCase();
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

        const iti = intlTelInput(input, buildPhoneInputOptions(input));
        input._iti = iti;

        if (input.value) {
            iti.setNumber(input.value);
        }

        input.addEventListener("blur", sync);
        input.addEventListener("countrychange", sync);
        input.addEventListener("input", sync);

        sync();
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
                if (input._iti) {
                    input.value = input._iti.getNumber() || input.value;
                }
            });
        });
    });
}

export function bootModernPhoneInputs(root = document) {
    initModernPhoneInputs(root);
    normalizePhoneInputsOnSubmit(root);
}
