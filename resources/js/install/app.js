import Alpine from "alpinejs";
import Errors from "./components/Errors";
import "./vendors/Axios";
import { bootModernPhoneInputs } from "../../../modules/Storefront/Resources/assets/public/js/lib/modernPhoneInput";

window.Alpine = Alpine;

Alpine.data("App", ({ requirementSatisfied, permissionProvided, suggestedAppUrl }) => ({
    step: 1,
    formSubmitting: false,
    animateAlert: false,
    appInstalled: false,
    errorMessage: null,
    form: {
        app_url: suggestedAppUrl,
        db_host: "localhost",
        db_port: 3306,
    },
    errors: new Errors(),

    get isShowPrev() {
        return this.step > 1 && this.step <= 4;
    },

    get isPrevDisabled() {
        return this.formSubmitting;
    },

    get isNextDisabled() {
        if (this.formSubmitting) {
            return true;
        }

        if (this.step === 2) {
            return !requirementSatisfied;
        }

        if (this.step === 3) {
            return !permissionProvided;
        }

        return false;
    },

    get hasErrorMessage() {
        return Boolean(this.errorMessage);
    },

    prevStep() {
        if (this.isPrevDisabled || this.step <= 1) {
            return;
        }

        this.step--;
    },

    nextStep() {
        if (this.isNextDisabled) {
            return;
        }

        if (this.step === 4) {
            this.submitForm();

            return;
        }

        this.step++;

        if (this.step === 4) {
            this.focusInitialFormField();
        }
    },

    setErrorMessage(message) {
        this.errorMessage = message;
        this.triggerAlertAnimation();
    },

    resetErrorMessage() {
        this.errorMessage = null;
    },

    triggerAlertAnimation() {
        this.animateAlert = true;

        setTimeout(() => {
            this.animateAlert = false;
        }, 1000);
    },

    focusInitialFormField() {
        this.$nextTick(() => {
            this.$refs.configurationForm?.elements[0]?.focus();
        });
    },

    focusFirstErrorField(errors) {
        [...this.$refs.configurationForm.elements].some((el) => {
            if (el.name === Object.keys(errors)[0]) {
                el.focus();

                return true;
            }
        });
    },

    scrollToTop() {
        this.$refs.configurationContent?.scroll({
            top: 0,
            behavior: "auto",
        });
    },

    resetForm() {
        this.form = {
            app_url: suggestedAppUrl,
            db_host: "localhost",
            db_port: 3306,
        };
    },

    syncPhoneFields() {
        this.$refs.configurationForm
            ?.querySelectorAll("input.modern-phone-input")
            .forEach((input) => {
                if (!input._iti || !input.name) {
                    return;
                }

                this.form[input.name] = input._iti.getNumber() || this.form[input.name];
            });
    },

    submitForm() {
        this.formSubmitting = true;
        this.syncPhoneFields();

        const installUrl =
            document.querySelector('meta[name="install-url"]')?.content ??
            "install";

        axios
            .post(installUrl, this.form)
            .then(() => {
                this.appInstalled = true;
                this.resetForm();
                this.resetErrorMessage();
                this.errors.reset();
            })
            .catch(({ response }) => {
                if (!response) {
                    this.scrollToTop();
                    this.setErrorMessage(
                        document.querySelector('meta[name="install-network-error"]')?.content ??
                            "Installation request failed."
                    );

                    return;
                }

                if (response.status === 422) {
                    const errors = response.data.errors;

                    this.resetErrorMessage();
                    this.focusFirstErrorField(errors);
                    this.errors.record(errors);

                    return;
                }

                this.scrollToTop();

                const message =
                    response.data?.message ||
                    (response.status === 404
                        ? document.querySelector('meta[name="install-not-found"]')?.content
                        : `Installation failed (HTTP ${response.status}).`);

                this.setErrorMessage(message);
            })
            .finally(() => {
                this.formSubmitting = false;
            });
    },
}));

document.addEventListener("DOMContentLoaded", () => {
    bootModernPhoneInputs(document);
});

Alpine.start();
