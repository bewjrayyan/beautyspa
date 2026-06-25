import flatpickr from "flatpickr";
import { buildDatepickerOptions } from "../../../lib/modernDatepicker";
import {
    bootModernPhoneInputs,
    formatPhoneE164,
    getPhoneInputE164,
} from "../../../lib/modernPhoneInput";
import Errors from "../../../components/Errors";
import "../../../components/CartItem";

Alpine.data(
    "Checkout",
    ({
        customerEmail,
        customerPhone,
        customerBilling = null,
        addresses,
        defaultAddress,
        gateways,
        countries,
        requiresTreatmentBooking = false,
        beauticians = [],
        availabilitySlotsUrl = null,
        slotLabels = {},
        spaBranches = [],
        loyaltyBalance = 0,
        loyaltyWorthRm = 0,
        loyaltyMaxPoints = 0,
    }) => ({
        addresses,
        defaultAddress,
        customerBilling,
        gateways,
        countries,
        requiresTreatmentBooking,
        beauticians,
        availabilitySlotsUrl,
        slotLabels,
        spaBranches,
        form: {
            customer_email: customerEmail,
            customer_phone: customerPhone,
            billing: {},
            shipping: {},
            billingAddressId: null,
            shippingAddressId: null,
            newBillingAddress: false,
            newShippingAddress: false,
            ship_to_a_different_address: false,
            beautician_id: "",
            appointment_date: "",
            appointment_time: "",
            spa_branch_id: "",
            order_note: "",
            terms_and_conditions: false,
            payment_method: "",
            shipping_method: "",
        },
        states: {
            billing: {},
            shipping: {},
        },
        controller: null,
        shippingMethodName: null,
        applyingCoupon: false,
        couponCode: null,
        couponError: null,
        applyingLoyalty: false,
        loyaltyPoints: null,
        loyaltyError: null,
        loyaltyBalance,
        loyaltyWorthRm,
        loyaltyMaxPoints,
        placingOrder: false,
        beauticianPickerOpen: false,
        spaBranchPickerOpen: false,
        appointmentSlots: [],
        loadingAppointmentSlots: false,
        stripe: null,
        stripeElements: null,
        authorizeNetToken: null,
        payFastFormFields: {},
        errors: new Errors(),
        accountEmailExists: false,
        checkingAccountEmail: false,
        accountLoginPassword: "",
        accountLoginError: "",
        loggingInToAccount: false,
        emailCheckTimeout: null,

        get cartFetched() {
            return this.$store.cart.fetched;
        },

        get cart() {
            return this.$store.cart.cart;
        },

        get cartIsEmpty() {
            return this.$store.cart.isEmpty;
        },

        get hasAddress() {
            return Object.keys(this.addresses).length !== 0;
        },

        get firstCountry() {
            return Object.keys(this.countries)[0];
        },

        get hasBillingStates() {
            return Object.keys(this.states.billing).length !== 0;
        },

        get hasShippingStates() {
            return Object.keys(this.states.shipping).length !== 0;
        },

        get hasNoPaymentMethod() {
            return this.gatewayOptions.length === 0;
        },

        get gatewayOptions() {
            return Object.entries(this.gateways).map(([key, gateway]) => ({
                id: gateway.id ?? key,
                ...gateway,
            }));
        },

        get firstPaymentMethod() {
            return this.gatewayOptions[0]?.id ?? "";
        },

        get shouldShowPaymentInstructions() {
            return ["bank_transfer", "check_payment"].includes(
                this.form.payment_method
            );
        },

        get paymentInstructions() {
            if (this.shouldShowPaymentInstructions) {
                return this.gateways[this.form.payment_method].instructions;
            }
        },

        get hasShippingMethod() {
            return Object.keys(this.cart.availableShippingMethods).length !== 0;
        },

        get hasFreeShipping() {
            return this.cart.coupon?.free_shipping ?? false;
        },

        get chipPaymentFee() {
            const gateway = this.gateways[this.form.payment_method];

            if (!gateway) {
                return 0;
            }

            if (gateway.surcharge_subunit) {
                return gateway.surcharge_subunit / 100;
            }

            return 0;
        },

        get checkoutTotal() {
            const cartTotal = Number(this.$store.cart.total ?? 0);

            return cartTotal + this.chipPaymentFee;
        },

        get firstShippingMethod() {
            return Object.keys(this.cart.availableShippingMethods)[0];
        },

        get minAppointmentDate() {
            return new Date().toISOString().split("T")[0];
        },

        get hasSpaBranchSelected() {
            return (
                this.hasSpaBranches && String(this.form.spa_branch_id || "") !== ""
            );
        },

        get availableBeauticians() {
            if (!this.requiresTreatmentBooking) {
                return [];
            }

            if (this.hasSpaBranches) {
                if (!this.hasSpaBranchSelected) {
                    return [];
                }

                const branchId = String(this.form.spa_branch_id);

                return this.beauticians.filter((beautician) => {
                    const branchIds = beautician.spa_branch_ids || [];

                    return branchIds.some((id) => String(id) === branchId);
                });
            }

            return this.beauticians;
        },

        get selectedBeautician() {
            if (!this.form.beautician_id) {
                return null;
            }

            return (
                this.availableBeauticians.find(
                    (beautician) =>
                        String(beautician.id) ===
                        String(this.form.beautician_id)
                ) ?? null
            );
        },

        get hasSpaBranches() {
            return Array.isArray(this.spaBranches) && this.spaBranches.length > 0;
        },

        get beauticianPlaceholderText() {
            if (this.hasSpaBranches && !this.hasSpaBranchSelected) {
                return (
                    this.slotLabels.select_spa_branch_first ||
                    "Select a spa branch first"
                );
            }

            return this.slotLabels.select_beautician || "Select beautician";
        },

        get appointmentTimeSelectOptions() {
            if (this.loadingAppointmentSlots) {
                return [
                    {
                        key: "loading",
                        value: "",
                        label: this.slotLabels.loading || "Loading…",
                        disabled: true,
                    },
                ];
            }

            if (!this.appointmentSlots.length) {
                return [
                    {
                        key: "empty",
                        value: "",
                        label: this.slotLabels.empty || "No available times",
                        disabled: true,
                    },
                ];
            }

            return this.appointmentSlots.map((slot, index) => ({
                key: `slot-${index}-${slot}`,
                value: slot,
                label: this.formatAppointmentSlot(slot),
                disabled: false,
            }));
        },

        get selectedSpaBranch() {
            if (!this.form.spa_branch_id) {
                return null;
            }

            return (
                this.spaBranches.find(
                    (branch) =>
                        String(branch.id) === String(this.form.spa_branch_id)
                ) ?? null
            );
        },

        selectSpaBranch(branch) {
            this.form.spa_branch_id = String(branch.id);
            this.spaBranchPickerOpen = false;
            this.beauticianPickerOpen = false;
            this.errors.clear("spa_branch_id");
        },

        selectBeautician(beautician) {
            this.form.beautician_id = String(beautician.id);
            this.beauticianPickerOpen = false;
            this.spaBranchPickerOpen = false;
            this.errors.clear("beautician_id");
            this.loadAppointmentSlots();
        },

        formatAppointmentSlot(slot) {
            if (!slot) {
                return "";
            }

            const [hour, minute] = String(slot).split(":").map(Number);
            const date = new Date();

            date.setHours(hour, minute, 0, 0);

            return date.toLocaleTimeString([], {
                hour: "numeric",
                minute: "2-digit",
            });
        },

        async loadAppointmentSlots() {
            if (!this.availabilitySlotsUrl || !this.form.beautician_id || !this.form.appointment_date) {
                return;
            }

            this.loadingAppointmentSlots = true;

            try {
                const url = this.availabilitySlotsUrl.replace(
                    "__BEAUTICIAN__",
                    String(this.form.beautician_id)
                );
                const response = await axios.get(url, {
                    params: { date: this.form.appointment_date },
                });

                this.appointmentSlots = response.data.slots || [];

                if (
                    !this.appointmentSlots.includes(this.form.appointment_time)
                ) {
                    this.form.appointment_time = this.appointmentSlots[0] || "";
                }
            } catch (error) {
                this.appointmentSlots = [];
                this.form.appointment_time = "";
            } finally {
                this.loadingAppointmentSlots = false;
            }
        },

        init() {
            Alpine.effect(() => {
                if (this.cartFetched) {
                    this.hideSkeleton();
                    this.changePaymentMethod(this.firstPaymentMethod);

                    if (this.cart.shippingMethodName) {
                        this.changeShippingMethod(this.cart.shippingMethodName);
                    } else {
                        this.updateShippingMethod(this.firstShippingMethod);
                    }

                    if (
                        AestheticCart.stripeEnabled &&
                        AestheticCart.stripeIntegrationType === "embedded_form"
                    ) {
                        this.renderStripeElements();
                    }
                }
            });

            this.$watch("form.billing.city", (newCity) => {
                if (newCity) {
                    this.addTaxes();
                }
            });

            this.$watch("form.shipping.city", (newCity) => {
                if (newCity) {
                    this.addTaxes();
                }
            });

            this.$watch("form.billing.zip", (newZip) => {
                if (newZip) {
                    this.addTaxes();
                }
            });

            this.$watch("form.shipping.zip", (newZip) => {
                if (newZip) {
                    this.addTaxes();
                }
            });

            this.$watch("form.billing.state", (newState) => {
                if (newState) {
                    this.addTaxes();
                }
            });

            this.$watch("form.shipping.state", (newState) => {
                if (newState) {
                    this.addTaxes();
                }
            });

            this.$watch("form.ship_to_a_different_address", (newValue) => {
                if (newValue && this.form.shippingAddressId) {
                    this.form.shipping =
                        this.addresses[this.form.shippingAddressId];
                } else {
                    this.form.shipping = {};
                    this.resetAddressErrors("shipping");
                }

                this.addTaxes();
            });

            this.$watch("form.terms_and_conditions", () => {
                this.errors.clear("terms_and_conditions");
            });

            this.$watch("form.payment_method", (newPaymentMethod) => {
                if (newPaymentMethod === "paypal") {
                    this.$nextTick(this.renderPayPalButton());
                }
            });

            this.initBillingDefaults();

            this.normalizeBillingCountry();
            this.initTreatmentBookingDefaults();
            this.initSpaBranchDefaults();
            this.syncBeauticianWithBranch();

            if (this.hasSpaBranches) {
                this.$watch("form.spa_branch_id", () => {
                    this.beauticianPickerOpen = false;
                    this.spaBranchPickerOpen = false;
                    this.syncBeauticianWithBranch();
                });
            }

            this.initAppointmentPickers();
            this.setTabReminder();

            if (this.requiresTreatmentBooking && this.availabilitySlotsUrl) {
                this.$watch("form.beautician_id", () => this.loadAppointmentSlots());
                this.$watch("form.appointment_date", () => this.loadAppointmentSlots());
                this.loadAppointmentSlots();
            }

            this.$nextTick(() => {
                bootModernPhoneInputs(this.$el);
            });

            this.$watch("form.customer_email", () => {
                this.scheduleAccountEmailCheck();
            });

            this.$watch("form.create_an_account", (value) => {
                if (value && this.accountEmailExists) {
                    this.form.create_an_account = false;
                }
            });

            if (this.form.customer_email) {
                this.scheduleAccountEmailCheck();
            }
        },

        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email || "").trim());
        },

        scheduleAccountEmailCheck() {
            clearTimeout(this.emailCheckTimeout);

            const email = String(this.form.customer_email || "").trim();

            if (!email || !this.isValidEmail(email)) {
                this.accountEmailExists = false;
                this.accountLoginError = "";

                return;
            }

            this.emailCheckTimeout = setTimeout(() => {
                this.checkAccountEmail();
            }, 500);
        },

        async checkAccountEmail() {
            const email = String(this.form.customer_email || "").trim();

            if (!this.isValidEmail(email)) {
                this.accountEmailExists = false;

                return;
            }

            this.checkingAccountEmail = true;
            this.accountLoginError = "";

            try {
                const { data } = await axios.post(
                    AestheticCart.url("/checkout/check-email"),
                    { email }
                );

                this.accountEmailExists = Boolean(data.exists);

                if (this.accountEmailExists) {
                    this.form.create_an_account = false;
                }
            } catch (error) {
                this.accountEmailExists = false;
            } finally {
                this.checkingAccountEmail = false;
            }
        },

        async loginToAccount() {
            if (this.loggingInToAccount || !this.accountLoginPassword) {
                return;
            }

            this.loggingInToAccount = true;
            this.accountLoginError = "";

            try {
                const payload = {
                    email: this.form.customer_email,
                    password: this.accountLoginPassword,
                };
                const captchaResponse = window.grecaptcha?.getResponse?.();

                if (captchaResponse) {
                    payload["g-recaptcha-response"] = captchaResponse;
                }

                const { data } = await axios.post(
                    AestheticCart.url("/checkout/login"),
                    payload
                );

                notify(data.message);

                window.location.href =
                    data.redirect || AestheticCart.url("/checkout");
            } catch (error) {
                this.accountLoginError =
                    error.response?.data?.errors?.["g-recaptcha-response"]?.[0] ||
                    error.response?.data?.message ||
                    trans("storefront::storefront.something_went_wrong");

                if (window.grecaptcha) {
                    grecaptcha.reset();
                }
            } finally {
                this.loggingInToAccount = false;
            }
        },

        useDifferentEmail() {
            this.form.customer_email = "";
            this.accountEmailExists = false;
            this.accountLoginPassword = "";
            this.accountLoginError = "";
            this.form.create_an_account = false;
            this.errors.clear("customer_email");

            this.$nextTick(() => {
                document.getElementById("customer-email")?.focus();
            });
        },

        initAppointmentPickers() {
            if (!this.requiresTreatmentBooking) {
                return;
            }

            this.$nextTick(() => {
                const dateEl = document.getElementById("appointment-date");
                const timeEl = document.getElementById("appointment-time");

                if (!dateEl) {
                    return;
                }

                if (dateEl && !dateEl._flatpickr) {
                    flatpickr(dateEl, {
                        ...buildDatepickerOptions(dateEl),
                        minDate: this.minAppointmentDate,
                        defaultDate:
                            this.form.appointment_date ||
                            this.minAppointmentDate,
                        onChange: (_selectedDates, dateStr) => {
                            this.form.appointment_date = dateStr;

                            if (this.availabilitySlotsUrl) {
                                this.loadAppointmentSlots();
                            }
                        },
                    });
                }

                if (!this.availabilitySlotsUrl && timeEl && !timeEl._flatpickr) {
                    flatpickr(timeEl, {
                        ...buildDatepickerOptions(timeEl),
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "H:i",
                        altFormat: "h:i K",
                        defaultDate: this.form.appointment_time || "10:00",
                        onChange: (_selectedDates, timeStr) => {
                            this.form.appointment_time = timeStr;
                        },
                    });
                }
            });
        },

        normalizeBillingCountry() {
            const country = this.form.billing?.country;

            if (country && !this.countries[country]) {
                this.form.billing.country = this.firstCountry;
                this.form.billing.state = "";
            }

            if (this.form.billing?.country) {
                this.fetchStates(this.form.billing.country, (response) => {
                    this.states.billing = response.data;

                    if (
                        this.hasBillingStates &&
                        this.form.billing.state &&
                        !this.states.billing[this.form.billing.state]
                    ) {
                        this.form.billing.state =
                            Object.keys(this.states.billing)[0] || "";
                    }
                });
            }
        },

        resolveCustomerPhone() {
            const input = this.$el?.querySelector(
                '#customer-phone, input.modern-phone-input[name="customer_phone"]'
            );
            const fromInput = getPhoneInputE164(input);

            if (fromInput) {
                return fromInput;
            }

            return formatPhoneE164(this.form.customer_phone);
        },

        buildCheckoutPayload() {
            this.normalizeBillingCountry();
            this.initTreatmentBookingDefaults();

            const customerPhone = this.resolveCustomerPhone();
            this.form.customer_phone = customerPhone;

            const payload = {
                customer_email: this.form.customer_email,
                customer_phone: customerPhone,
                create_an_account: this.form.create_an_account ? 1 : 0,
                password: this.form.password,
                ship_to_a_different_address: this.form
                    .ship_to_a_different_address
                    ? 1
                    : 0,
                payment_method: this.form.payment_method,
                shipping_method: this.form.shipping_method,
                terms_and_conditions: this.form.terms_and_conditions ? 1 : 0,
                order_note: this.form.order_note,
                billing: { ...this.form.billing },
            };

            if (this.requiresTreatmentBooking) {
                payload.beautician_id = this.form.beautician_id;
                payload.appointment_date = this.form.appointment_date;
                payload.appointment_time = this.form.appointment_time
                    ? String(this.form.appointment_time).slice(0, 5)
                    : null;
            }

            if (this.hasSpaBranches) {
                payload.spa_branch_id = this.form.spa_branch_id || null;
            }

            if (this.form.ship_to_a_different_address) {
                payload.shipping = { ...this.form.shipping };
            }

            return payload;
        },

        recordValidationErrors(response) {
            const bag = response?.data?.errors || {};

            this.errors.record(bag);

            const firstKey = Object.keys(bag)[0];

            if (firstKey && bag[firstKey]?.[0]) {
                notify(bag[firstKey][0]);

                return;
            }

            notify(
                response?.data?.message ||
                    trans("storefront::storefront.something_went_wrong")
            );
        },

        syncBeauticianWithBranch() {
            if (!this.requiresTreatmentBooking) {
                return;
            }

            const available = this.availableBeauticians;
            const selectedStillValid =
                this.form.beautician_id &&
                available.some(
                    (beautician) =>
                        String(beautician.id) === String(this.form.beautician_id)
                );

            if (!selectedStillValid) {
                this.form.beautician_id = "";
                this.form.appointment_time = "";
                this.appointmentSlots = [];
            }

            this.loadAppointmentSlots();
        },

        initTreatmentBookingDefaults() {
            if (!this.requiresTreatmentBooking) {
                return;
            }

            if (!this.form.appointment_date) {
                this.form.appointment_date = this.minAppointmentDate;
            }

            if (!this.availabilitySlotsUrl && !this.form.appointment_time) {
                this.form.appointment_time = "10:00";
            }
        },

        initSpaBranchDefaults() {
            if (!this.hasSpaBranches || !this.form.spa_branch_id) {
                return;
            }

            if (
                !this.spaBranches.some(
                    (branch) => String(branch.id) === String(this.form.spa_branch_id)
                )
            ) {
                this.form.spa_branch_id = "";
            }
        },

        setTabReminder() {
            const originalTitle = document.title;
            let timeoutId;

            document.addEventListener("visibilitychange", function () {
                if (document.hidden) {
                    timeoutId = setTimeout(() => {
                        document.title = trans(
                            "storefront::checkout.remember_about_your_order"
                        );
                    }, 1000);
                } else {
                    clearTimeout(timeoutId);

                    document.title = originalTitle;
                }
            });
        },

        hideSkeleton() {
            const selectors = [
                ".cart-items-skeleton",
                ".order-summary-list-skeleton",
                ".order-summary-total-skeleton",
            ];

            selectors.forEach((selector) => {
                const element = document.querySelector(selector);

                if (element) {
                    element.remove();
                }
            });
        },

        changeBillingAddress(address) {
            if (
                this.form.newBillingAddress ||
                this.form.billingAddressId === address.id
            ) {
                return;
            }

            this.form.billingAddressId = address.id;

            this.mergeSavedBillingAddress();
        },

        addNewBillingAddress() {
            this.resetAddressErrors("billing");

            this.form.billing = {};
            this.form.newBillingAddress = !this.form.newBillingAddress;

            if (!this.form.newBillingAddress) {
                this.mergeSavedBillingAddress();
            }
        },

        changeShippingAddress(address) {
            if (
                this.form.newShippingAddress ||
                this.form.shippingAddressId === address.id
            ) {
                return;
            }

            this.form.shippingAddressId = address.id;

            this.mergeSavedShippingAddress();
        },

        addNewShippingAddress() {
            this.resetAddressErrors("shipping");

            this.form.shipping = {};
            this.form.newShippingAddress = !this.form.newShippingAddress;

            if (!this.form.newShippingAddress) {
                this.mergeSavedShippingAddress();
            }
        },

        // Reset address errors based on address type
        resetAddressErrors(addressType) {
            Object.keys(this.errors.errors).map((key) => {
                key.indexOf(addressType) !== -1 && this.errors.clear(key);
            });
        },

        initBillingDefaults() {
            if (this.defaultAddress?.address_id) {
                this.form.billingAddressId = this.defaultAddress.address_id;
                this.form.shippingAddressId = this.defaultAddress.address_id;
                this.mergeSavedBillingAddress();
                this.mergeSavedShippingAddress();

                return;
            }

            if (this.hasAddress) {
                const firstAddress = Object.values(this.addresses)[0];

                if (firstAddress?.id) {
                    this.form.billingAddressId = firstAddress.id;
                    this.form.shippingAddressId = firstAddress.id;
                    this.mergeSavedBillingAddress();
                    this.mergeSavedShippingAddress();
                }

                return;
            }

            if (!this.customerBilling) {
                this.form.newBillingAddress = true;
                this.form.newShippingAddress = true;

                return;
            }

            this.form.billing = { ...this.customerBilling };
            this.form.newBillingAddress = true;
            this.form.newShippingAddress = true;
        },

        resolveSavedAddress(addressId) {
            if (!addressId) {
                return null;
            }

            return (
                this.addresses[addressId] ??
                this.addresses[String(addressId)] ??
                null
            );
        },

        mergeSavedBillingAddress() {
            this.resetAddressErrors("billing");

            if (!this.form.newBillingAddress && this.form.billingAddressId) {
                const address = this.resolveSavedAddress(
                    this.form.billingAddressId
                );

                if (address) {
                    this.form.billing = address;
                }
            }
        },

        mergeSavedShippingAddress() {
            this.resetAddressErrors("shipping");

            if (
                this.form.ship_to_a_different_address &&
                !this.form.newShippingAddress &&
                this.form.shippingAddressId
            ) {
                const address = this.resolveSavedAddress(
                    this.form.shippingAddressId
                );

                if (address) {
                    this.form.shipping = address;
                }
            }
        },

        changeBillingCity(city) {
            this.form.billing.city = city;
        },

        changeShippingCity(city) {
            this.form.shipping.city = city;
        },

        changeBillingZip(zip) {
            this.form.billing.zip = zip;
        },

        changeShippingZip(zip) {
            this.form.shipping.zip = zip;
        },

        changeBillingCountry(country) {
            this.form.billing.country = country;

            if (country === "") {
                this.form.billing.state = "";
                this.states.billing = {};

                return;
            }

            this.fetchStates(country, (response) => {
                this.states.billing = response.data;
                this.form.billing.state = "";
            });
        },

        changeShippingCountry(country) {
            this.form.shipping.country = country;

            if (country === "") {
                this.form.shipping.state = "";
                this.states.shipping = {};

                return;
            }

            this.fetchStates(country, (response) => {
                this.states.shipping = response.data;
                this.form.shipping.state = "";
            });
        },

        fetchStates(country, callback) {
            axios.get(AestheticCart.apiUrl(`/countries/${country}/states`)).then(callback);
        },

        changeBillingState(state) {
            this.form.billing.state = state;
        },

        changeShippingState(state) {
            this.form.shipping.state = state;
        },

        changePaymentMethod(paymentMethod) {
            this.form.payment_method = paymentMethod;
        },

        changeShippingMethod(shippingMethodName) {
            this.form.shipping_method = shippingMethodName;
        },

        async updateShippingMethod(shippingMethodName) {
            if (!shippingMethodName) {
                return;
            }

            this.changeShippingMethod(shippingMethodName);

            try {
                const response = await axios.post("/cart/shipping-method", {
                    shipping_method: shippingMethodName,
                });

                this.$store.cart.updateCart(response.data);
            } catch (error) {
                notify(error.response.data.message);
            }
        },

        async addTaxes() {
            try {
                const response = await axios.post("/cart/taxes", this.form);

                this.$store.cart.updateCart(response.data);
            } catch (error) {
                notify(error.response.data.message);
            }
        },

        applyCoupon() {
            if (!this.couponCode) {
                return;
            }

            this.applyingCoupon = true;

            axios
                .post("/cart/coupon", { coupon: this.couponCode })
                .then((response) => {
                    this.couponCode = null;
                    this.couponError = null;

                    this.$store.cart.updateCart(response.data);
                })
                .catch((error) => {
                    this.couponError = error.response.data.message;
                })
                .finally(() => {
                    this.applyingCoupon = false;
                });
        },

        removeCoupon() {
            axios
                .delete("/cart/coupon")
                .then(() => {
                    this.updateShippingMethod(this.form.shipping_method);
                })
                .catch((error) => {
                    notify(error.response.data.message);
                });
        },

        get loyaltyAppliedLabel() {
            const points = this.cart?.loyalty?.points ?? 0;
            const discount =
                this.cart?.loyalty?.value?.inCurrentCurrency?.amount ?? 0;

            return `${points} pts (−RM ${discount.toFixed(2)})`;
        },

        async useMaxLoyaltyPoints() {
            try {
                const { data } = await axios.get("/cart/loyalty/quote");

                this.loyaltyPoints = data.max_points;
                this.loyaltyMaxPoints = data.max_points;
            } catch (error) {
                this.loyaltyError =
                    error.response?.data?.message ||
                    "Could not load maximum points.";
            }
        },

        applyLoyalty() {
            this.applyingLoyalty = true;
            this.loyaltyError = null;

            axios
                .post("/cart/loyalty", {
                    points: this.loyaltyPoints || undefined,
                })
                .then((response) => {
                    this.loyaltyPoints = null;
                    this.$store.cart.updateCart(response.data);
                })
                .catch((error) => {
                    this.loyaltyError =
                        error.response?.data?.message ||
                        error.response?.data?.errors?.points?.[0] ||
                        "Could not apply points.";
                })
                .finally(() => {
                    this.applyingLoyalty = false;
                });
        },

        removeLoyalty() {
            axios
                .delete("/cart/loyalty")
                .then((response) => {
                    this.$store.cart.updateCart(response.data);
                })
                .catch((error) => {
                    notify(error.response?.data?.message);
                });
        },

        placeOrder() {
            if (!this.form.terms_and_conditions || this.placingOrder) {
                return;
            }

            if (this.accountEmailExists && !AestheticCart.loggedIn) {
                notify(
                    trans("storefront::checkout.please_login_to_continue")
                );

                return;
            }

            this.placingOrder = true;

            axios
                .post(AestheticCart.url("/checkout"), this.buildCheckoutPayload())
                .then(({ data }) => {
                    if (data.redirectUrl) {
                        window.location.href = data.redirectUrl;
                    } else if (this.form.payment_method === "stripe") {
                        this.confirmStripePayment(data);
                    } else if (this.form.payment_method === "paytm") {
                        this.confirmPaytmPayment(data);
                    } else if (this.form.payment_method === "razorpay") {
                        this.confirmRazorpayPayment(data);
                    } else if (this.form.payment_method === "paystack") {
                        this.confirmPaystackPayment(data);
                    } else if (this.form.payment_method === "authorizenet") {
                        this.confirmAuthorizeNetPayment(data);
                    } else if (this.form.payment_method === "flutterwave") {
                        this.confirmFlutterWavePayment(data);
                    } else if (this.form.payment_method === "mercadopago") {
                        this.confirmMercadoPagoPayment(data);
                    } else if (this.form.payment_method === "payfast") {
                        this.confirmPayFastPayment(data);
                    } else {
                        this.confirmOrder(
                            data.orderId,
                            this.form.payment_method
                        );
                    }
                })
                .catch(({ response }) => {
                    this.placingOrder = false;

                    if (!response) {
                        return;
                    }

                    if (response.status === 422) {
                        this.recordValidationErrors(response);

                        return;
                    }

                    notify(
                        response.data?.message ||
                            trans("storefront::storefront.something_went_wrong")
                    );
                });
        },

        confirmOrder(orderId, paymentMethod, params = {}) {
            axios
                .get(`/checkout/${orderId}/complete`, {
                    params: {
                        paymentMethod,
                        ...params,
                    },
                })
                .then(({ data }) => {
                    window.location.href =
                        data?.redirectUrl ||
                        AestheticCart.url("/checkout/complete");
                })
                .catch((error) => {
                    this.placingOrder = false;

                    this.deleteOrder(orderId);

                    notify(error.response.data.message);
                });
        },

        async deleteOrder(orderId) {
            if (!orderId) {
                return;
            }

            const response = await axios.get(
                `/checkout/${orderId}/payment-canceled`
            );

            notify(response.data.message);
        },

        renderPayPalButton() {
            let vm = this;
            let response;

            window.paypal
                .Buttons({
                    async createOrder() {
                        try {
                            response = await axios.post(
                                "/checkout",
                                vm.buildCheckoutPayload()
                            );

                            return response.data.resourceId;
                        } catch ({ response }) {
                            if (response?.status === 422) {
                                vm.recordValidationErrors(response);

                                return;
                            }

                            notify(response.data.message);
                        }
                    },
                    onApprove() {
                        vm.confirmOrder(
                            response.data.orderId,
                            "paypal",
                            response.data
                        );
                    },
                    onError() {
                        vm.deleteOrder(response.data.orderId);
                    },
                    onCancel() {
                        vm.deleteOrder(response.data.orderId);
                    },
                })
                .render("#paypal-button-container");
        },

        async renderStripeElements() {
            this.stripe = Stripe(AestheticCart.stripePublishableKey, {});

            this.stripeElements = this.stripe.elements({
                mode: "payment",
                amount: Math.round(this.$store.cart.total * 100),
                currency: AestheticCart.currency.toLowerCase(),
            });

            this.stripeElements.create("payment").mount("#stripe-element");
        },

        async confirmStripePayment({ client_secret, orderId, return_url }) {
            const elements = this.stripeElements;

            const { error: submitError } = await this.stripeElements.submit();

            if (submitError) {
                this.placingOrder = false;

                this.deleteOrder(orderId);

                notify(submitError.message);

                return;
            }

            const { error } = await this.stripe.confirmPayment({
                elements,
                clientSecret: client_secret,
                confirmParams: {
                    return_url,
                },
            });

            if (error) {
                this.placingOrder = false;

                this.deleteOrder(orderId);

                notify(error.message);
            }
        },

        confirmPaytmPayment({ orderId, amount, txnToken }) {
            let config = {
                root: "",
                flow: "DEFAULT",
                data: {
                    orderId: orderId,
                    token: txnToken,
                    tokenType: "TXN_TOKEN",
                    amount: amount,
                },
                merchant: {
                    name: AestheticCart.storeName,
                    redirect: false,
                },
                handler: {
                    transactionStatus: (response) => {
                        if (response.STATUS === "TXN_SUCCESS") {
                            this.confirmOrder(orderId, "paytm", response);
                        } else if (response.STATUS === "TXN_FAILURE") {
                            this.placingOrder = false;

                            this.deleteOrder(orderId);
                        }

                        window.Paytm.CheckoutJS.close();
                    },
                    notifyMerchant: (eventName) => {
                        if (eventName === "APP_CLOSED") {
                            this.placingOrder = false;

                            this.deleteOrder(orderId);
                        }
                    },
                },
            };

            window.Paytm.CheckoutJS.init(config)
                .then(() => {
                    window.Paytm.CheckoutJS.invoke();
                })
                .catch(() => {
                    this.deleteOrder(orderId);
                });
        },

        confirmRazorpayPayment(razorpayOrder) {
            this.placingOrder = false;

            let vm = this;

            new window.Razorpay({
                key: razorpayOrder.razorpayKeyId,
                name: AestheticCart.storeName,
                description: trans("storefront::checkout.payment_for_order", {
                    id: razorpayOrder.receipt,
                }),
                image: AestheticCart.storeLogo,
                order_id: razorpayOrder.id,
                handler(response) {
                    vm.placingOrder = true;

                    vm.confirmOrder(
                        razorpayOrder.receipt,
                        "razorpay",
                        response
                    );
                },
                modal: {
                    ondismiss() {
                        vm.deleteOrder(razorpayOrder.receipt);
                    },
                },
                prefill: {
                    name: `${vm.form.billing.first_name} ${vm.form.billing.last_name}`,
                    email: vm.form.customer_email,
                    contact: vm.form.customer_phone,
                },
            }).open();
        },

        confirmPaystackPayment({
            key,
            email,
            amount,
            ref,
            currency,
            order_id,
        }) {
            let vm = this;

            PaystackPop.setup({
                key,
                email,
                amount,
                ref,
                currency,
                onClose() {
                    vm.placingOrder = false;

                    vm.deleteOrder(order_id);
                },
                callback(response) {
                    vm.placingOrder = false;

                    vm.confirmOrder(order_id, "paystack", response);
                },
                onBankTransferConfirmationPending(response) {
                    vm.placingOrder = false;

                    vm.confirmOrder(order_id, "paystack", response);
                },
            }).openIframe();
        },

        confirmAuthorizeNetPayment({ token }) {
            this.authorizeNetToken = token;

            this.$nextTick(() => {
                this.$refs.authorizeNetForm.submit();

                this.authorizeNetToken = null;
            });
        },

        confirmFlutterWavePayment({
            public_key,
            tx_ref,
            order_id,
            amount,
            currency,
            payment_options,
            redirect_url,
        }) {
            let vm = this;

            FlutterwaveCheckout({
                public_key,
                tx_ref,
                amount,
                currency,
                payment_options: payment_options.join(", "),
                redirect_url,
                customer: {
                    email: this.form.customer_email,
                    phone_number: this.form.customer_phone,
                    name: this.form.billing.full_name,
                },
                customizations: {
                    title: AestheticCart.storeName,
                    logo: AestheticCart.storeLogo,
                },
                onclose(incomplete) {
                    vm.placingOrder = false;

                    if (incomplete) {
                        vm.deleteOrder(order_id);
                    }
                },
            });
        },

        confirmMercadoPagoPayment(mercadoPagoOrder) {
            this.placingOrder = false;

            const SUPPORTED_LOCALES = {
                en_US: "en-US",
                es_AR: "es-AR",
                es_CL: "es-CL",
                es_CO: "es-CO",
                es_MX: "es-MX",
                es_VE: "es-VE",
                es_UY: "es-UY",
                es_PE: "es-PE",
                pt_BR: "pt-BR",
            };

            const mercadoPago = new MercadoPago(mercadoPagoOrder.publicKey, {
                locale:
                    SUPPORTED_LOCALES[mercadoPagoOrder.currentLocale] ||
                    "en-US",
            });

            mercadoPago.checkout({
                preference: {
                    id: mercadoPagoOrder.preferenceId,
                },
                autoOpen: true,
            });
        },

        confirmPayFastPayment(payFastOrder) {
            this.payFastFormFields = payFastOrder.formFields;

            this.$nextTick(() => {
                this.$refs.payFastForm.submit();
            });
        },
    })
);
