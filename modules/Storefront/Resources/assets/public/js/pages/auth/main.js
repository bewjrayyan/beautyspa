import Alpine from "alpinejs";
import { bootModernPhoneInputs } from "../../lib/modernPhoneInput";
import { registerOtpDigitInput } from "../../lib/otpDigitInput";

window.Alpine = Alpine;
window.bootModernPhoneInputs = bootModernPhoneInputs;

registerOtpDigitInput(Alpine);

function createOtpMethods(getPhoneInputId) {
    return {
        resolvePhone() {
            const phoneInput = document.getElementById(getPhoneInputId());

            if (phoneInput?._iti) {
                return phoneInput._iti.getNumber() || phoneInput.dataset.fullNumber || phoneInput.value || "";
            }

            return phoneInput?.dataset.fullNumber || phoneInput?.value || this.phone || "";
        },

        apiFetch(url, options = {}) {
            const targetUrl = url.startsWith("http") ? url : AestheticCart.url(url);

            return fetch(targetUrl, {
                credentials: "same-origin",
                ...options,
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": AestheticCart.csrfToken || this.csrfToken(),
                    "X-Requested-With": "XMLHttpRequest",
                    ...(options.headers || {}),
                },
            });
        },

        async sendOtp() {
            this.loading = true;
            this.error = "";
            this.success = "";

            const phoneInput = document.getElementById(getPhoneInputId());

            if (phoneInput?._iti && this.step === "phone") {
                if (! phoneInput._iti.isValidNumber()) {
                    this.error = this.invalidPhoneMessage;
                    this.loading = false;

                    return;
                }
            }

            this.phone = this.resolvePhone();

            try {
                const response = await this.apiFetch(this.sendUrl, {
                    method: "POST",
                    body: JSON.stringify({ phone: this.phone }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || "Failed to send OTP.");
                }

                this.success = data.message;
                this.step = "otp";
            } catch (e) {
                this.error = e.message;
            } finally {
                this.loading = false;
            }
        },

        async verifyOtp() {
            this.loading = true;
            this.error = "";
            this.success = "";

            this.phone = this.resolvePhone();

            try {
                const response = await this.apiFetch(this.verifyUrl, {
                    method: "POST",
                    body: JSON.stringify({ phone: this.phone, otp: String(this.otp || "").trim() }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || "Verification failed.");
                }

                window.location.href = data.redirect || "/account";
            } catch (e) {
                this.error = e.message;
            } finally {
                this.loading = false;
            }
        },

        csrfToken() {
            return document.querySelector('input[name="_token"]')?.value ?? "";
        },
    };
}

Alpine.data("customerLoginMethods", () => ({
    mode: "email",
    step: "phone",
    phone: "",
    phoneValid: false,
    otp: "",
    loading: false,
    error: "",
    success: "",
    sendUrl: "",
    verifyUrl: "",
    invalidPhoneMessage: "",

    init() {
        this.sendUrl = this.$el.dataset.sendUrl ?? "";
        this.verifyUrl = this.$el.dataset.verifyUrl ?? "";
        this.invalidPhoneMessage = this.$el.dataset.invalidPhoneMessage ?? "";

        window.addEventListener("open-whatsapp-otp", () => {
            this.mode = "whatsapp";
            this.step = "phone";
            this.error = "";
            this.success = "";
            this.initPhoneInput();
        });
    },

    initPhoneInput() {
        this.$nextTick(() => {
            bootModernPhoneInputs(this.$el);
        });
    },

    ...createOtpMethods(() => "customer-otp-phone"),
}));

Alpine.data("whatsappOtpLogin", () => ({
    isOpen: false,
    step: "phone",
    phone: "",
    otp: "",
    loading: false,
    error: "",
    success: "",
    sendUrl: "",
    verifyUrl: "",
    invalidPhoneMessage: "",

    init() {
        this.sendUrl = this.$el.dataset.sendUrl ?? "";
        this.verifyUrl = this.$el.dataset.verifyUrl ?? "";
        this.invalidPhoneMessage = this.$el.dataset.invalidPhoneMessage ?? "";

        window.addEventListener("open-whatsapp-otp", () => {
            if (document.querySelector(".customer-login-tabs")) {
                return;
            }

            this.show();
        });
    },

    show() {
        this.isOpen = true;
        this.step = "phone";
        this.error = "";
        this.success = "";

        this.$nextTick(() => {
            bootModernPhoneInputs(this.$el);
        });
    },

    closeModal() {
        this.isOpen = false;
        this.loading = false;
    },

    ...createOtpMethods(() => "whatsapp-otp-phone"),
}));

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bootModernPhoneInputs);
} else {
    bootModernPhoneInputs();
}

Alpine.start();
