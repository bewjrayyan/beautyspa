import flatpickr from "flatpickr";
import { buildDatepickerOptions } from "../../../lib/modernDatepicker";
import {
    bootModernPhoneInputs,
    formatPhoneE164,
    getPhoneInputE164,
} from "../../../lib/modernPhoneInput";
import Errors from "../../../components/Errors";
import { resolveRecaptchaToken } from "../../../functions";
import "../../../components/CartItem";

/** Local calendar Y-m-d — never use toISOString() (UTC can be yesterday in MY morning). */
function localDateYmd(date = new Date()) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, "0");
    const d = String(date.getDate()).padStart(2, "0");

    return `${y}-${m}-${d}`;
}

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
        availabilityDatesUrl = null,
        treatmentProductId = null,
        treatmentCartItems = [],
        treatmentAllowTbaByBranch = {},
        treatmentAllowTbaByProductBranch = {},
        treatmentDurationByProductBranch = {},
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
        availabilityDatesUrl,
        treatmentProductId,
        treatmentCartItems,
        treatmentAllowTbaByBranch,
        treatmentAllowTbaByProductBranch,
        treatmentDurationByProductBranch,
        treatmentSchedules: [],
        slotLabels,
        spaBranches,
        loggedIn: Boolean(window.AestheticCart?.loggedIn),
        form: {
            customer_email: customerEmail,
            customer_phone: customerPhone,
            billing: {},
            shipping: {},
            billingAddressId: null,
            shippingAddressId: null,
            newBillingAddress: false,
            newShippingAddress: false,
            saveBillingAddress: false,
            makeBillingAddressDefault: false,
            saveShippingAddress: false,
            makeShippingAddressDefault: false,
            ship_to_a_different_address: false,
            beautician_id: "",
            appointment_date: "",
            schedule_later: "1",
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
        appointmentSlotsRequestId: 0,
        availableAppointmentDates: [],
        loadingAppointmentDates: false,
        appointmentDatesResolved: false,
        appointmentDatesLoadFailed: false,
        errors: new Errors(),
        accountEmailExists: false,
        checkingAccountEmail: false,
        accountLoginPassword: "",
        accountLoginError: "",
        loggingInToAccount: false,
        emailCheckTimeout: null,
        paymentProofFile: null,
        paymentProofFileName: "",
        paymentProofPreviewUrl: "",
        paymentProofIsImage: false,
        paymentProofFileSize: "",
        paymentProofDragging: false,
        paymentProofError: "",

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

        get hasReusableBilling() {
            return (
                this.hasAddress || this.isCompleteAddress(this.customerBilling)
            );
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
            return this.form.payment_method === "bank_transfer";
        },

        get offlinePaymentMethods() {
            return ["bank_transfer", "cod"];
        },

        get isOfflinePaymentMethod() {
            return this.offlinePaymentMethods.includes(
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
            return localDateYmd();
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

        get isScheduleLater() {
            return (
                this.form.schedule_later === true ||
                this.form.schedule_later === 1 ||
                this.form.schedule_later === "1"
            );
        },

        get canScheduleLater() {
            const branchId = String(this.form.spa_branch_id || "");

            if (!branchId || !(branchId in this.treatmentAllowTbaByBranch)) {
                return true;
            }

            return Boolean(this.treatmentAllowTbaByBranch[branchId]);
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
                hour12: true,
            });
        },

        formatSlotOptionLabel(option) {
            const base = option?.time_label || this.formatAppointmentSlot(option?.time || option);

            if (option?.status === "booked") {
                return `${base} (${this.slotLabels.booked || "Booked"})`;
            }

            if (option?.status === "unavailable") {
                return `${base} (${this.slotLabels.unavailable || "Unavailable"})`;
            }

            return base;
        },

        async loadAppointmentSlots() {
            if (
                this.isScheduleLater ||
                !this.availabilitySlotsUrl ||
                !this.form.spa_branch_id ||
                !this.treatmentProductId ||
                !this.form.beautician_id ||
                !this.form.appointment_date
            ) {
                this.appointmentSlotsRequestId += 1;
                this.appointmentSlots = [];
                this.form.appointment_time = "";
                return;
            }

            const requestId = ++this.appointmentSlotsRequestId;
            this.loadingAppointmentSlots = true;

            try {
                const url = this.availabilitySlotsUrl.replace(
                    "__BEAUTICIAN__",
                    String(this.form.beautician_id)
                );
                const response = await axios.get(url, {
                    params: {
                        date: this.form.appointment_date,
                        spa_branch_id: this.form.spa_branch_id || null,
                        product_id: this.treatmentProductId || null,
                    },
                });

                if (requestId !== this.appointmentSlotsRequestId) {
                    return;
                }

                this.appointmentSlots = response.data.slots || [];

                if (
                    !this.appointmentSlots.includes(this.form.appointment_time)
                ) {
                    this.form.appointment_time = this.appointmentSlots[0] || "";
                }
            } catch (error) {
                if (requestId !== this.appointmentSlotsRequestId) {
                    return;
                }

                this.appointmentSlots = [];
                this.form.appointment_time = "";
            } finally {
                if (requestId === this.appointmentSlotsRequestId) {
                    this.loadingAppointmentSlots = false;
                }
            }
        },

        async loadAvailableAppointmentDates() {
            if (
                this.isScheduleLater ||
                !this.availabilityDatesUrl ||
                !this.form.spa_branch_id ||
                !this.treatmentProductId ||
                !this.form.beautician_id
            ) {
                this.availableAppointmentDates = [];
                this.appointmentDatesResolved = false;
                this.appointmentDatesLoadFailed = false;
                this.refreshAppointmentDatePicker();
                this.syncAppointmentDatePickerState();
                return;
            }

            this.loadingAppointmentDates = true;
            this.appointmentDatesResolved = false;
            this.appointmentDatesLoadFailed = false;
            this.syncAppointmentDatePickerState();

            try {
                const from = this.minAppointmentDate;
                const toDate = new Date();
                toDate.setDate(toDate.getDate() + 60);
                const to = localDateYmd(toDate);

                const response = await axios.get(this.availabilityDatesUrl, {
                    params: {
                        spa_branch_id: this.form.spa_branch_id,
                        product_id: this.treatmentProductId,
                        beautician_id: this.form.beautician_id || null,
                        from,
                        to,
                    },
                });

                this.availableAppointmentDates = response.data.dates || [];
                this.appointmentDatesResolved = true;

                if (
                    this.form.appointment_date &&
                    !this.availableAppointmentDates.includes(this.form.appointment_date)
                ) {
                    this.form.appointment_date = this.availableAppointmentDates[0] || "";
                    const dateInput = document.getElementById("appointment-date");
                    if (dateInput) {
                        dateInput.value = this.form.appointment_date;
                    }
                }

                this.refreshAppointmentDatePicker();
            } catch (error) {
                this.availableAppointmentDates = [];
                this.appointmentDatesResolved = true;
                this.appointmentDatesLoadFailed = true;
                this.form.appointment_date = "";
                this.form.appointment_time = "";
                this.refreshAppointmentDatePicker();
            } finally {
                this.loadingAppointmentDates = false;
                this.syncAppointmentDatePickerState();
            }
        },

        appointmentDateEnableRules() {
            return this.availableAppointmentDates.filter(Boolean);
        },

        syncAppointmentDatePickerState() {
            const dateEl = document.getElementById("appointment-date");
            const picker = dateEl?._flatpickr;
            const disabled =
                this.isScheduleLater ||
                this.loadingAppointmentDates ||
                !this.form.spa_branch_id ||
                !this.form.beautician_id ||
                !this.treatmentProductId;

            if (dateEl) {
                dateEl.disabled = disabled;
            }

            if (!picker) {
                return;
            }

            picker.input.disabled = disabled;
            picker._input.disabled = disabled;
            picker._input.setAttribute("aria-disabled", disabled ? "true" : "false");

            if (picker.altInput) {
                picker.altInput.disabled = disabled;
            }

            if (disabled) {
                picker.close();
            }
        },

        refreshAppointmentDatePicker() {
            const dateEl = document.getElementById("appointment-date");
            const picker = dateEl?._flatpickr;

            if (!picker) {
                return;
            }

            // Flatpickr's parseDateRules() calls `.slice()` on `enable` —
            // never pass undefined/null here.
            picker.set("enable", this.appointmentDateEnableRules());
            picker.set("minDate", this.minAppointmentDate);

            if (this.form.appointment_date) {
                picker.setDate(this.form.appointment_date, false);
            } else {
                picker.clear(false);
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

                }
            });

            this.$watch("form.payment_method", (method) => {
                if (method !== "bank_transfer") {
                    this.clearPaymentProof();
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

            this.$watch("form.makeBillingAddressDefault", (isDefault) => {
                if (isDefault) {
                    this.form.makeShippingAddressDefault = false;
                }
            });

            this.$watch("form.makeShippingAddressDefault", (isDefault) => {
                if (isDefault) {
                    this.form.makeBillingAddressDefault = false;
                }
            });

            this.$watch("form.ship_to_a_different_address", (newValue) => {
                if (newValue && this.form.shippingAddressId) {
                    this.form.shipping =
                        this.addresses[this.form.shippingAddressId];
                } else if (newValue) {
                    this.form.newShippingAddress = true;
                    this.form.shipping = this.blankAddress(this.form.billing);
                    this.form.saveShippingAddress = this.loggedIn;
                } else {
                    this.form.shipping = {};
                    this.form.saveShippingAddress = false;
                    this.form.makeShippingAddressDefault = false;
                    this.resetAddressErrors("shipping");
                }

                this.addTaxes();
            });

            this.$watch("form.terms_and_conditions", () => {
                this.errors.clear("terms_and_conditions");
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
                    (this.treatmentSchedules || []).forEach((line, index) => {
                        line.pickerOpen = false;
                        line.appointment_date = "";
                        line.appointment_time = "";
                        line.slots = [];
                        line.availableDates = [];
                        line.datesResolved = false;
                        line.datesLoadFailed = false;
                        if (!this.canScheduleLaterForLine(line)) {
                            line.schedule_later = "0";
                        }
                        this.destroyLineDatePicker(index);
                        if (!this.isLineScheduleLater(line) && line.beautician_id) {
                            this.loadLineAvailableDates(index);
                        }
                    });
                });
            }

            this.setTabReminder();

            if (this.requiresTreatmentBooking) {
                this.bootTreatmentSchedules();
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
                const captchaResponse = await resolveRecaptchaToken("login");

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

                if (window.grecaptcha?.reset && !window.AestheticCart?.recaptchaV3Enabled) {
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

        bootTreatmentSchedules() {
            this.ensureTreatmentSchedules();
            this.$nextTick(() => {
                (this.treatmentSchedules || []).forEach((line, index) => {
                    if (!this.isLineScheduleLater(line) && line.beautician_id) {
                        this.loadLineAvailableDates(index);
                        if (line.appointment_date) {
                            this.loadLineAppointmentSlots(index);
                        }
                    }
                });
                this.initAppointmentPickers();
            });
        },

        ensureTreatmentSchedules() {
            if (!this.requiresTreatmentBooking) {
                this.treatmentSchedules = [];
                return;
            }

            const sourceItems = Array.isArray(this.treatmentCartItems)
                ? this.treatmentCartItems.slice()
                : [];

            if (!sourceItems.length && this.treatmentProductId) {
                sourceItems.push({
                    cart_item_id: "legacy",
                    product_id: this.treatmentProductId,
                    name: "Treatment",
                });
            }

            const existing = new Map(
                (this.treatmentSchedules || []).map((line) => [
                    String(line.cart_item_id || `p-${line.product_id}`),
                    line,
                ])
            );

            this.treatmentSchedules = sourceItems.map((item) => {
                const key = String(item.cart_item_id || `p-${item.product_id}`);
                const prev = existing.get(key);

                return {
                    cart_item_id: String(item.cart_item_id || ""),
                    product_id: Number(item.product_id),
                    name: item.name || "Treatment",
                    beautician_id: prev?.beautician_id || "",
                    schedule_later: prev?.schedule_later ?? "1",
                    appointment_date: prev?.appointment_date || "",
                    appointment_time: prev?.appointment_time || "",
                    slots: prev?.slots || [],
                    slotOptions: prev?.slotOptions || [],
                    baseSlotOptions: (prev && Number(prev.product_id) === Number(item.product_id)) ? (prev.baseSlotOptions || []) : [],
                    slotsLoadedFor: (prev && Number(prev.product_id) === Number(item.product_id)) ? (prev.slotsLoadedFor || null) : null,
                    availableDates: prev?.availableDates || [],
                    dateOptions: prev?.dateOptions || [],
                    loadingSlots: false,
                    loadingDates: false,
                    pickerOpen: false,
                    datesResolved: Boolean(prev?.datesResolved),
                    datesLoadFailed: Boolean(prev?.datesLoadFailed),
                    durationMinutes: prev?.durationMinutes || null,
                    slotConflict: Boolean(prev?.slotConflict),
                };
            });

            (this.treatmentSchedules || []).forEach((line) => {
                if (!this.canScheduleLaterForLine(line) && this.isLineScheduleLater(line)) {
                    line.schedule_later = "0";
                }
            });
        },

        lineBeautician(line) {
            if (!line?.beautician_id) return null;
            return this.beauticians.find((b) => String(b.id) === String(line.beautician_id)) ?? null;
        },

        isLineScheduleLater(line) {
            return line?.schedule_later === true || line?.schedule_later === 1 || line?.schedule_later === "1";
        },

        canScheduleLaterForLine(line) {
            const branchId = String(this.form.spa_branch_id || "");
            const productId = Number(line?.product_id || 0);
            const byProduct = this.treatmentAllowTbaByProductBranch?.[productId];

            if (byProduct && branchId && Object.prototype.hasOwnProperty.call(byProduct, branchId)) {
                return Boolean(byProduct[branchId]);
            }

            if (!branchId || !(branchId in (this.treatmentAllowTbaByBranch || {}))) {
                return true;
            }

            return Boolean(this.treatmentAllowTbaByBranch[branchId]);
        },

        lineAppointmentTimeOptions(line) {
            if (!line?.beautician_id) {
                return [{
                    key: "need-beautician",
                    value: "",
                    label: this.slotLabels.select_beautician || "Select beautician first",
                    disabled: true,
                }];
            }

            if (!line.appointment_date) {
                return [{
                    key: "need-date",
                    value: "",
                    label: this.slotLabels.select_date || "Select a date first",
                    disabled: true,
                }];
            }

            if (line.loadingSlots) {
                const loadingSelected = line.appointment_time
                    ? String(line.appointment_time).slice(0, 5)
                    : "";

                if (loadingSelected) {
                    return [
                        {
                            key: `loading-selected-${loadingSelected}`,
                            value: loadingSelected,
                            label: this.formatAppointmentSlot(loadingSelected),
                            disabled: true,
                        },
                        {
                            key: "loading",
                            value: "",
                            label: this.slotLabels.loading || "Loading…",
                            disabled: true,
                        },
                    ];
                }

                return [{ key: "loading", value: "", label: this.slotLabels.loading || "Loading…", disabled: true }];
            }

            const selected = line.appointment_time
                ? String(line.appointment_time).slice(0, 5)
                : "";
            const slotOptions = Array.isArray(line.slotOptions) ? line.slotOptions.slice() : [];

            const displayOptions = slotOptions.filter((opt) => opt.status !== "past");

            if (!displayOptions.length) {
                return [{ key: "empty", value: "", label: this.slotLabels.empty || "No available times", disabled: true }];
            }

            return [
                {
                    key: "placeholder",
                    value: "",
                    label: this.slotLabels.select || "Select time",
                    disabled: true,
                },
                ...displayOptions.map((opt, index) => ({
                    key: `slot-${index}-${opt.time}-${opt.status}`,
                    value: opt.time,
                    label: this.formatSlotOptionLabel(opt),
                    disabled: opt.status !== "available",
                })),
            ];
        },

        selectLineBeautician(lineIndex, beautician) {
            const line = this.treatmentSchedules[lineIndex];
            if (!line) return;

            line.beautician_id = String(beautician.id);
            line.pickerOpen = false;
            this.spaBranchPickerOpen = false;
            this.errors.clear(`treatment_bookings.${lineIndex}.beautician_id`);

            // Reset schedule when beautician changes — slots belong to that beautician.
            line.appointment_date = "";
            line.appointment_time = "";
            line.slots = [];
            line.slotOptions = [];
            line.baseSlotOptions = [];
            line.slotsLoadedFor = null;
            line.availableDates = [];
            line.dateOptions = [];
            line.datesResolved = false;
            line.datesLoadFailed = false;

            this.destroyLineDatePicker(lineIndex);

            if (!this.isLineScheduleLater(line)) {
                this.loadLineAvailableDates(lineIndex);
            }
        },

        onLineScheduleModeChange(lineIndex) {
            const line = this.treatmentSchedules[lineIndex];
            if (!line) return;

            if (this.isLineScheduleLater(line)) {
                line.appointment_date = "";
                line.appointment_time = "";
                line.slots = [];
                line.slotOptions = [];
                line.baseSlotOptions = [];
                line.slotsLoadedFor = null;
                line.availableDates = [];
                line.datesResolved = false;
                line.datesLoadFailed = false;
                this.destroyLineDatePicker(lineIndex);
            } else if (line.beautician_id) {
                // Wait for x-show to reveal date inputs before Flatpickr binds.
                this.$nextTick(() => this.loadLineAvailableDates(lineIndex));
            } else {
                this.$nextTick(() => this.initAppointmentPickers());
            }
        },

        destroyLineDatePicker(lineIndex) {
            const dateEl = this.$el?.querySelector(
                `.checkout-datepicker[data-line-index="${lineIndex}"]`
            );
            if (dateEl?._flatpickr) {
                dateEl._flatpickr.destroy();
            }
        },

        openLineDatePicker(lineIndex) {
            const line = this.treatmentSchedules[lineIndex];
            if (
                !line ||
                this.isLineScheduleLater(line) ||
                !line.beautician_id ||
                line.loadingDates ||
                !(line.availableDates || []).length
            ) {
                return;
            }

            const dateEl = this.$el?.querySelector(
                `.checkout-datepicker[data-line-index="${lineIndex}"]`
            );
            const picker = dateEl?._flatpickr;

            if (picker) {
                picker.open();
                return;
            }

            this.initAppointmentPickers();
            this.$nextTick(() => {
                const el = this.$el?.querySelector(
                    `.checkout-datepicker[data-line-index="${lineIndex}"]`
                );
                el?._flatpickr?.open();
            });
        },

        async loadLineAvailableDates(lineIndex) {
            const line = this.treatmentSchedules[lineIndex];
            if (
                !line ||
                this.isLineScheduleLater(line) ||
                !this.availabilityDatesUrl ||
                !this.form.spa_branch_id ||
                !line.product_id ||
                !line.beautician_id
            ) {
                if (line) {
                    line.availableDates = [];
                    line.datesResolved = false;
                    line.datesLoadFailed = false;
                }
                this.$nextTick(() => this.initLineDatePicker(lineIndex));
                return;
            }

            line.loadingDates = true;
            line.datesLoadFailed = false;

            try {
                const from = this.minAppointmentDate;
                const toDate = new Date(`${from}T12:00:00`);
                toDate.setDate(toDate.getDate() + 60);
                const to = localDateYmd(toDate);
                const { data } = await axios.get(this.availabilityDatesUrl, {
                    params: {
                        product_id: line.product_id,
                        spa_branch_id: this.form.spa_branch_id,
                        beautician_id: line.beautician_id,
                        from,
                        to,
                    },
                });
                line.dateOptions = Array.isArray(data.date_options)
                    ? data.date_options
                    : (Array.isArray(data.dates)
                        ? data.dates.map((date) => ({ date, status: "available" }))
                        : []);
                line.availableDates = line.dateOptions
                    .filter((opt) => opt.status === "available")
                    .map((opt) => opt.date);
                line.datesResolved = true;

                if (
                    line.appointment_date &&
                    !line.availableDates.includes(line.appointment_date)
                ) {
                    line.appointment_date = "";
                    line.appointment_time = "";
                    line.slots = [];
                    line.slotOptions = [];
                }
            } catch (e) {
                line.availableDates = [];
                line.dateOptions = [];
                line.datesResolved = true;
                line.datesLoadFailed = true;
                line.appointment_date = "";
                line.appointment_time = "";
                line.slots = [];
            } finally {
                line.loadingDates = false;
                this.destroyLineDatePicker(lineIndex);
                this.$nextTick(() => this.initLineDatePicker(lineIndex));
            }
        },

        lineScheduleWindow(line) {
            const time = String(line?.appointment_time || "").slice(0, 5);
            if (!time || !/^\d{2}:\d{2}$/.test(time)) {
                return null;
            }

            const [hour, minute] = time.split(":").map(Number);
            const start = (hour * 60) + minute;
            const duration = this.resolveLineDurationMinutes(line);

            return { start, end: start + duration };
        },

        resolveLineDurationMinutes(line) {
            if (Number(line?.durationMinutes) > 0) {
                return Number(line.durationMinutes);
            }

            const productId = Number(line?.product_id || 0);
            const branchId = String(this.form.spa_branch_id || "");
            const byProduct = this.treatmentDurationByProductBranch?.[productId];

            if (byProduct && branchId && Object.prototype.hasOwnProperty.call(byProduct, branchId)) {
                return Math.max(1, Number(byProduct[branchId]) || 60);
            }

            return 60;
        },

        buildSiblingHolds(excludeLineIndex) {
            return (this.treatmentSchedules || [])
                .filter((line, index) => index !== excludeLineIndex && !this.isLineScheduleLater(line))
                .filter((line) => line.beautician_id && line.appointment_date && line.appointment_time)
                .map((line) => ({
                    beautician_id: Number(line.beautician_id),
                    appointment_date: String(line.appointment_date),
                    appointment_time: String(line.appointment_time).slice(0, 5),
                    product_id: Number(line.product_id) || null,
                    duration_minutes: this.resolveLineDurationMinutes(line),
                }));
        },

        syncLineAppointmentTimeWithSlots(line) {
            if (!line?.appointment_time) {
                return;
            }

            const time = String(line.appointment_time).slice(0, 5);
            const slot = (line.slotOptions || []).find(
                (opt) => opt.time === time && opt.status === "available"
            );

            if (!slot) {
                line.appointment_time = "";
            }
        },

        onLineAppointmentTimeChange(lineIndex) {
            this.$nextTick(() => {
                const line = this.treatmentSchedules[lineIndex];
                const selected = line?.appointment_time
                    ? String(line.appointment_time).slice(0, 5)
                    : "";

                this.reapplyAllSiblingHolds();

                (this.treatmentSchedules || []).forEach((otherLine, index) => {
                    if (index === lineIndex || this.isLineScheduleLater(otherLine)) {
                        return;
                    }

                    this.syncLineAppointmentTimeWithSlots(otherLine);
                });

                this.checkSiblingScheduleConflicts();

                if (
                    line &&
                    selected &&
                    (line.slots || []).includes(selected)
                ) {
                    line.appointment_time = selected;
                }
            });
        },

        applyHoldsToSlotOptions(baseOptions, line, holds) {
            const duration = this.resolveLineDurationMinutes(line);

            return (baseOptions || []).map((option) => {
                const cloned = { ...option };

                if (cloned.status !== "available" || !holds.length) {
                    return cloned;
                }

                const startMin = this.minutesFromClock(cloned.time);
                const endMin = startMin === null ? null : startMin + duration;

                if (startMin === null || endMin === null) {
                    return cloned;
                }

                const conflicts = holds.some((hold) => {
                    if (Number(hold.beautician_id) !== Number(line.beautician_id)) {
                        return false;
                    }

                    if (String(hold.appointment_date) !== String(line.appointment_date)) {
                        return false;
                    }

                    const holdStart = this.minutesFromClock(hold.appointment_time);
                    const holdDuration = Math.max(1, Number(hold.duration_minutes) || 60);
                    const holdEnd = holdStart === null ? null : holdStart + holdDuration;

                    if (holdStart === null || holdEnd === null) {
                        return false;
                    }

                    return startMin < holdEnd && endMin > holdStart;
                });

                if (conflicts) {
                    cloned.status = "booked";
                }

                return cloned;
            });
        },

        reapplyAllSiblingHolds() {
            (this.treatmentSchedules || []).forEach((line, index) => {
                if (this.isLineScheduleLater(line)) {
                    return;
                }

                const base = (line.baseSlotOptions && line.baseSlotOptions.length)
                    ? line.baseSlotOptions
                    : line.slotOptions;

                if (!Array.isArray(base) || !base.length) {
                    return;
                }

                const holds = this.buildSiblingHolds(index);
                line.slotOptions = this.applyHoldsToSlotOptions(base, line, holds);
                line.slots = line.slotOptions
                    .filter((option) => option.status === "available")
                    .map((option) => this.formatAppointmentSlot(option.time));
            });
        },

        minutesFromClock(time) {
            const normalized = String(time || "").slice(0, 5);

            if (!/^\d{2}:\d{2}$/.test(normalized)) {
                return null;
            }

            const [hour, minute] = normalized.split(":").map(Number);

            return (hour * 60) + minute;
        },

        lineTimeIsInSchedule(line, time) {
            const normalized = String(time || "").slice(0, 5);

            return (line?.slotOptions || []).some(
                (option) => option.time === normalized && option.status === "available"
            );
        },

        lineAvailableScheduleTimes(line) {
            return (line?.slotOptions || [])
                .filter((option) => option.status === "available")
                .map((option) => option.time_label || this.formatAppointmentSlot(option.time))
                .filter(Boolean)
                .join(", ");
        },

        checkSiblingScheduleConflicts() {
            const lines = this.treatmentSchedules || [];
            lines.forEach((line) => {
                line.slotConflict = false;
            });

            const scheduled = [];

            lines.forEach((line, index) => {
                if (this.isLineScheduleLater(line)) {
                    return;
                }

                const beauticianId = Number(line.beautician_id || 0);
                const date = String(line.appointment_date || "");
                const window = this.lineScheduleWindow(line);

                if (!beauticianId || !date || !window) {
                    return;
                }

                scheduled.push({ index, beauticianId, date, ...window });
            });

            for (let i = 0; i < scheduled.length; i++) {
                for (let j = i + 1; j < scheduled.length; j++) {
                    const a = scheduled[i];
                    const b = scheduled[j];

                    if (
                        a.beauticianId === b.beauticianId &&
                        a.date === b.date &&
                        a.start < b.end &&
                        a.end > b.start
                    ) {
                        lines[a.index].slotConflict = true;
                        lines[b.index].slotConflict = true;
                    }
                }
            }
        },

        async loadLineAppointmentSlots(lineIndex) {
            const line = this.treatmentSchedules[lineIndex];
            if (
                !line ||
                this.isLineScheduleLater(line) ||
                !this.availabilitySlotsUrl ||
                !this.form.spa_branch_id ||
                !line.product_id ||
                !line.beautician_id ||
                !line.appointment_date
            ) {
                if (line) {
                    line.slots = [];
                    if (!line.appointment_date) {
                        line.appointment_time = "";
                    }
                }
                return;
            }

            const previousTime = line.appointment_time
                ? String(line.appointment_time).slice(0, 5)
                : "";

            line.loadingSlots = true;
            try {
                const url = this.availabilitySlotsUrl.replace(
                    "__BEAUTICIAN__",
                    String(line.beautician_id)
                );
                const { data } = await axios.get(url, {
                    params: {
                        date: line.appointment_date,
                        product_id: line.product_id,
                        spa_branch_id: this.form.spa_branch_id,
                    },
                });
                line.baseSlotOptions = Array.isArray(data.slot_options)
                    ? data.slot_options.map((option) => ({ ...option }))
                    : (data.slots || []).map((time) => ({ time, status: "available" }));
                if (data.duration_minutes) {
                    line.durationMinutes = Number(data.duration_minutes);
                }

                line.slotsLoadedFor = {
                    product_id: Number(line.product_id),
                    appointment_date: String(line.appointment_date || ""),
                    beautician_id: Number(line.beautician_id || 0),
                    spa_branch_id: Number(this.form.spa_branch_id || 0),
                };

                this.reapplyAllSiblingHolds();

                if (previousTime && line.slots.includes(previousTime)) {
                    line.appointment_time = previousTime;
                } else if (!previousTime && line.slots.length === 1) {
                    line.appointment_time = line.slots[0];
                } else if (previousTime && !line.slots.includes(previousTime)) {
                    line.appointment_time = "";
                } else {
                    this.syncLineAppointmentTimeWithSlots(line);
                }

                this.checkSiblingScheduleConflicts();
            } catch (e) {
                line.slots = [];
                line.slotOptions = [];
                line.baseSlotOptions = [];
                line.slotsLoadedFor = null;
                line.appointment_time = "";
            } finally {
                line.loadingSlots = false;
            }
        },

        initLineDatePicker(lineIndex) {
            if (!this.requiresTreatmentBooking) {
                return;
            }

            this.$nextTick(() => {
                const dateEl = this.$el?.querySelector(
                    `.checkout-datepicker[data-line-index="${lineIndex}"]`
                );
                const line = this.treatmentSchedules[lineIndex];

                if (!dateEl) {
                    return;
                }

                if (!line || this.isLineScheduleLater(line)) {
                    if (dateEl._flatpickr) {
                        dateEl._flatpickr.destroy();
                    }

                    return;
                }

                this.mountLineDatePicker(dateEl, lineIndex, line);
            });
        },

        mountLineDatePicker(dateEl, lineIndex, line) {
                        if (dateEl._flatpickr) {
                            dateEl._flatpickr.destroy();
                        }

                        const enableDates = this.lineDateEnableRules(line);
                        const canOpen =
                            Boolean(line.beautician_id) &&
                            !line.loadingDates &&
                            this.lineDateHasSelectableDays(line);

                        flatpickr(dateEl, {
                            ...buildDatepickerOptions(dateEl),
                            altInput: false,
                            allowInput: false,
                            minDate: this.minAppointmentDate,
                            defaultDate: line.appointment_date || undefined,
                            clickOpens: canOpen,
                            enable: enableDates.length ? enableDates : false,
                            onDayCreate: (_selectedDates, dateStr, _instance, dayElem) => {
                                const current = this.treatmentSchedules[lineIndex];
                                const status = this.lineDateStatusMap(current)[dateStr];

                                if (status === "fully_booked") {
                                    dayElem.classList.add("checkout-flatpickr-day--booked");
                                    dayElem.title = this.slotLabels.dateFullyBooked || "Fully booked";
                                } else if (status === "closed") {
                                    dayElem.classList.add("checkout-flatpickr-day--closed");
                                    dayElem.title = this.slotLabels.dateClosed || "Closed";
                                }
                            },
                            onOpen: (_selectedDates, _dateStr, instance) => {
                                const current = this.treatmentSchedules[lineIndex];
                                if (
                                    !current?.beautician_id ||
                                    current.loadingDates ||
                                    !this.lineDateHasSelectableDays(current)
                                ) {
                                    instance.close();
                                }
                            },
                            onChange: (_selectedDates, dateStr) => {
                                const current = this.treatmentSchedules[lineIndex];
                                if (!current) {
                                    return;
                                }

                                if (current.appointment_date === dateStr) {
                                    return;
                                }

                                current.appointment_date = dateStr;
                                current.appointment_time = "";
                                current.slots = [];
                                current.slotOptions = [];
                                current.baseSlotOptions = [];
                                current.slotsLoadedFor = null;
                                this.loadLineAppointmentSlots(lineIndex);
                            },
                        });

                        if (line.appointment_date) {
                            dateEl.value = line.appointment_date;
                        } else {
                            dateEl.value = "";
                        }
        },

        initAppointmentPickers() {
            if (!this.requiresTreatmentBooking) {
                return;
            }

            this.$nextTick(() => {
                this.$el
                    ?.querySelectorAll(".checkout-datepicker[data-line-index]")
                    .forEach((dateEl) => {
                        const lineIndex = Number(dateEl.dataset.lineIndex);
                        const line = this.treatmentSchedules[lineIndex];
                        if (!line || this.isLineScheduleLater(line)) {
                            if (dateEl._flatpickr) {
                                dateEl._flatpickr.destroy();
                            }
                            return;
                        }

                        this.mountLineDatePicker(dateEl, lineIndex, line);
                    });
            });
        },

        lineDateStatusMap(line) {
            const map = {};

            (line?.dateOptions || []).forEach((option) => {
                if (option?.date) {
                    map[option.date] = option.status;
                }
            });

            return map;
        },

        lineDateHasSelectableDays(line) {
            return (line?.dateOptions || []).some(
                (option) => option.status === "available" || option.status === "fully_booked"
            );
        },

        lineDateEnableRules(line) {
            if (!line?.beautician_id || line.loadingDates) {
                return [];
            }

            return (line?.availableDates || []).filter(Boolean);
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
                save_billing_address:
                    this.loggedIn &&
                    this.form.newBillingAddress &&
                    this.form.saveBillingAddress
                        ? 1
                        : 0,
                make_billing_address_default:
                    this.loggedIn &&
                    this.form.newBillingAddress &&
                    this.form.saveBillingAddress &&
                    this.form.makeBillingAddressDefault
                        ? 1
                        : 0,
                save_shipping_address:
                    this.loggedIn &&
                    this.form.ship_to_a_different_address &&
                    this.form.newShippingAddress &&
                    this.form.saveShippingAddress
                        ? 1
                        : 0,
                make_shipping_address_default:
                    this.loggedIn &&
                    this.form.ship_to_a_different_address &&
                    this.form.newShippingAddress &&
                    this.form.saveShippingAddress &&
                    this.form.makeShippingAddressDefault
                        ? 1
                        : 0,
                payment_method: this.form.payment_method,
                shipping_method: this.form.shipping_method,
                terms_and_conditions: this.form.terms_and_conditions ? 1 : 0,
                order_note: this.form.order_note,
                billing: { ...this.form.billing },
            };

            if (this.requiresTreatmentBooking) {
                payload.treatment_bookings = (this.treatmentSchedules || []).map((line) => {
                    const later = line.schedule_later === true || line.schedule_later === 1 || line.schedule_later === "1";
                    return {
                        cart_item_id: line.cart_item_id || null,
                        product_id: line.product_id,
                        beautician_id: line.beautician_id || null,
                        schedule_later: later ? 1 : 0,
                        appointment_date: later ? null : line.appointment_date || null,
                        appointment_time: !later && line.appointment_time
                            ? String(line.appointment_time).slice(0, 5)
                            : null,
                    };
                });

                const first = payload.treatment_bookings[0] || {};
                payload.schedule_later = first.schedule_later ?? 0;
                payload.beautician_id = first.beautician_id || null;
                payload.appointment_date = first.appointment_date || null;
                payload.appointment_time = first.appointment_time || null;
            }

            if (this.hasSpaBranches) {
                payload.spa_branch_id = this.form.spa_branch_id || null;
            }

            if (this.form.ship_to_a_different_address) {
                payload.shipping = { ...this.form.shipping };
            }

            return payload;
        },

        appendCheckoutFormData(formData, payload) {
            Object.entries(payload).forEach(([key, value]) => {
                if (key === "billing" || key === "shipping" || key === "treatment_bookings") {
                    return;
                }

                if (value === null || value === undefined || value === "") {
                    return;
                }

                formData.append(key, value);
            });

            (payload.treatment_bookings || []).forEach((line, index) => {
                Object.entries(line || {}).forEach(([field, fieldValue]) => {
                    if (fieldValue === null || fieldValue === undefined || fieldValue === "") {
                        return;
                    }
                    formData.append(`treatment_bookings[${index}][${field}]`, fieldValue);
                });
            });

            Object.entries(payload.billing || {}).forEach(([key, value]) => {
                if (value !== null && value !== undefined && value !== "") {
                    formData.append(`billing[${key}]`, value);
                }
            });

            if (payload.shipping) {
                Object.entries(payload.shipping).forEach(([key, value]) => {
                    if (value !== null && value !== undefined && value !== "") {
                        formData.append(`shipping[${key}]`, value);
                    }
                });
            }

            if (this.paymentProofFile) {
                formData.append("payment_proof", this.paymentProofFile);
            }
        },

        buildCheckoutRequestBody() {
            const payload = this.buildCheckoutPayload();

            if (this.form.payment_method !== "bank_transfer") {
                return payload;
            }

            const formData = new FormData();
            this.appendCheckoutFormData(formData, payload);

            return formData;
        },

        formatPaymentProofSize(bytes) {
            const n = Number(bytes) || 0;

            if (n < 1024) {
                return `${n} B`;
            }

            if (n < 1024 * 1024) {
                return `${(n / 1024).toFixed(1)} KB`;
            }

            return `${(n / (1024 * 1024)).toFixed(1)} MB`;
        },

        revokePaymentProofPreview() {
            if (this.paymentProofPreviewUrl) {
                URL.revokeObjectURL(this.paymentProofPreviewUrl);
            }

            this.paymentProofPreviewUrl = "";
            this.paymentProofIsImage = false;
        },

        clearPaymentProof() {
            this.revokePaymentProofPreview();
            this.paymentProofFile = null;
            this.paymentProofFileName = "";
            this.paymentProofFileSize = "";
            this.paymentProofError = "";
            this.paymentProofDragging = false;

            const input = document.getElementById("payment-proof-input");

            if (input) {
                input.value = "";
            }
        },

        setPaymentProofFile(file) {
            this.paymentProofError = "";

            if (!file) {
                this.clearPaymentProof();

                return;
            }

            const allowed = [
                "image/jpeg",
                "image/png",
                "image/webp",
                "application/pdf",
            ];
            const maxBytes = 10 * 1024 * 1024;
            const name = String(file.name || "").toLowerCase();
            const byExt = /\.(jpe?g|png|webp|pdf)$/.test(name);

            if (!allowed.includes(file.type) && !byExt) {
                this.paymentProofError = trans("storefront::checkout.payment_proof_invalid_type");
                this.clearPaymentProof();

                return;
            }

            if (file.size > maxBytes) {
                this.paymentProofError = trans("storefront::checkout.payment_proof_too_large");
                this.clearPaymentProof();

                return;
            }

            this.revokePaymentProofPreview();
            this.paymentProofFile = file;
            this.paymentProofFileName = file.name || "";
            this.paymentProofFileSize = this.formatPaymentProofSize(file.size);
            this.paymentProofIsImage = String(file.type || "").startsWith("image/")
                || /\.(jpe?g|png|webp)$/.test(name);

            if (this.paymentProofIsImage) {
                this.paymentProofPreviewUrl = URL.createObjectURL(file);
            }
        },

        onPaymentProofChange(event) {
            const file = event.target.files?.[0] || null;
            this.setPaymentProofFile(file);
        },

        onPaymentProofDragEnter(event) {
            event.preventDefault();
            event.stopPropagation();
            this.paymentProofDragging = true;
        },

        onPaymentProofDragOver(event) {
            event.preventDefault();
            event.stopPropagation();
            this.paymentProofDragging = true;
        },

        onPaymentProofDragLeave(event) {
            event.preventDefault();
            event.stopPropagation();

            if (event.currentTarget.contains(event.relatedTarget)) {
                return;
            }

            this.paymentProofDragging = false;
        },

        onPaymentProofDrop(event) {
            event.preventDefault();
            event.stopPropagation();
            this.paymentProofDragging = false;

            const file = event.dataTransfer?.files?.[0] || null;
            this.setPaymentProofFile(file);

            const input = document.getElementById("payment-proof-input");

            if (input && file) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                } catch (_) {
                    // Some browsers block programmatic FileList assignment.
                }
            }
        },

        recordValidationErrors(response) {
            const bag = response?.data?.errors || {};

            this.errors.record(bag);

            const treatmentKey = Object.keys(bag).find((key) => key.startsWith("treatment_bookings."));
            const preferredKey = treatmentKey || Object.keys(bag)[0];

            if (preferredKey && bag[preferredKey]?.[0]) {
                const match = preferredKey.match(/^treatment_bookings\.(\d+)\./);
                const lineIndex = match ? Number(match[1]) : null;
                const line = lineIndex !== null ? this.treatmentSchedules?.[lineIndex] : null;
                const message = bag[preferredKey][0];
                const label = line?.name ? `${line.name}: ${message}` : message;

                notify(label);

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
            this.form.saveBillingAddress = false;
            this.form.makeBillingAddressDefault = false;

            this.mergeSavedBillingAddress();
        },

        addNewBillingAddress() {
            this.resetAddressErrors("billing");
            this.form.newBillingAddress = !this.form.newBillingAddress;

            if (this.form.newBillingAddress) {
                this.form.billing = this.blankAddress(this.form.billing);
                this.form.saveBillingAddress = this.loggedIn;
                this.form.makeBillingAddressDefault = false;

                return;
            }

            this.form.saveBillingAddress = false;
            this.form.makeBillingAddressDefault = false;
            this.mergeSavedBillingAddress();
        },

        changeShippingAddress(address) {
            if (
                this.form.newShippingAddress ||
                this.form.shippingAddressId === address.id
            ) {
                return;
            }

            this.form.shippingAddressId = address.id;
            this.form.saveShippingAddress = false;
            this.form.makeShippingAddressDefault = false;

            this.mergeSavedShippingAddress();
        },

        addNewShippingAddress() {
            this.resetAddressErrors("shipping");

            this.form.newShippingAddress = !this.form.newShippingAddress;

            if (this.form.newShippingAddress) {
                this.form.shipping = this.blankAddress(this.form.billing);
                this.form.saveShippingAddress = this.loggedIn;
                this.form.makeShippingAddressDefault = false;

                return;
            }

            this.form.saveShippingAddress = false;
            this.form.makeShippingAddressDefault = false;
            this.mergeSavedShippingAddress();
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
                this.form.saveBillingAddress = this.loggedIn;

                return;
            }

            this.form.billing = { ...this.customerBilling };
            this.form.newBillingAddress = !this.isCompleteAddress(
                this.customerBilling
            );
            this.form.newShippingAddress = true;
            this.form.saveBillingAddress =
                this.loggedIn && this.form.newBillingAddress;
        },

        isCompleteAddress(address) {
            return [
                "first_name",
                "last_name",
                "address_1",
                "city",
                "state",
                "zip",
                "country",
            ].every((field) => String(address?.[field] || "").trim() !== "");
        },

        blankAddress(source = {}) {
            return {
                first_name:
                    source?.first_name || this.customerBilling?.first_name || "",
                last_name:
                    source?.last_name || this.customerBilling?.last_name || "",
                address_1: "",
                address_2: "",
                city: "",
                state: "",
                zip: "",
                country:
                    source?.country ||
                    this.customerBilling?.country ||
                    this.firstCountry ||
                    "",
            };
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

                return;
            }

            if (
                !this.form.newBillingAddress &&
                this.isCompleteAddress(this.customerBilling)
            ) {
                this.form.billing = { ...this.customerBilling };
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

        validateTreatmentSchedulesBeforeSubmit() {
            if (!this.requiresTreatmentBooking) {
                return true;
            }

            this.reapplyAllSiblingHolds();
            this.checkSiblingScheduleConflicts();

            if ((this.treatmentSchedules || []).some((line) => !this.isLineScheduleLater(line) && line.slotConflict)) {
                notify(trans("storefront::checkout.appointment_time_conflicts_sibling"));
                return false;
            }

            for (let index = 0; index < (this.treatmentSchedules || []).length; index++) {
                const line = this.treatmentSchedules[index];

                if (this.isLineScheduleLater(line)) {
                    continue;
                }

                if (line.loadingDates || line.loadingSlots) {
                    notify(trans("storefront::checkout.loading_appointment_schedule"));
                    return false;
                }

                const loadedFor = line.slotsLoadedFor;
                if (
                    loadedFor &&
                    (Number(loadedFor.product_id) !== Number(line.product_id)
                        || String(loadedFor.appointment_date) !== String(line.appointment_date)
                        || Number(loadedFor.beautician_id) !== Number(line.beautician_id)
                        || Number(loadedFor.spa_branch_id) !== Number(this.form.spa_branch_id))
                ) {
                    notify(trans("storefront::checkout.loading_appointment_schedule"));
                    this.loadLineAppointmentSlots(index);
                    return false;
                }

                if (!line.beautician_id || !line.appointment_date || !line.appointment_time) {
                    notify(trans("storefront::checkout.complete_treatment_schedule"));
                    return false;
                }

                if (!(line.availableDates || []).includes(line.appointment_date)) {
                    notify(trans("treatmentreservation::public.slot_unavailable"));
                    return false;
                }

                const time = String(line.appointment_time).slice(0, 5);

                if (!this.lineTimeIsInSchedule(line, time)) {
                    notify(
                        trans("storefront::checkout.appointment_time_not_in_schedule", {
                            treatment: line.name || "Treatment",
                        })
                    );
                    return false;
                }

                const slot = (line.slotOptions || []).find((opt) => opt.time === time);

                if (line.slotConflict) {
                    notify(trans("storefront::checkout.appointment_time_conflicts_sibling"));
                    return false;
                }

                if (!slot || slot.status !== "available") {
                    if (slot?.status === "unavailable") {
                        notify(trans("treatmentreservation::public.slot_beautician_unavailable"));
                        return false;
                    }

                    if (slot?.status === "booked") {
                        notify(trans("treatmentreservation::public.slot_unavailable"));
                        return false;
                    }

                    notify(trans("storefront::checkout.appointment_time_not_in_schedule", {
                        treatment: line.name || "Treatment",
                    }));
                    return false;
                }
            }

            return true;
        },

        placeOrder() {
            if (!this.form.terms_and_conditions || this.placingOrder) {
                return;
            }

            if (!this.validateTreatmentSchedulesBeforeSubmit()) {
                return;
            }

            if (this.accountEmailExists && !AestheticCart.loggedIn) {
                notify(
                    trans("storefront::checkout.please_login_to_continue")
                );

                return;
            }

            if (
                this.form.payment_method === "bank_transfer" &&
                !this.paymentProofFile
            ) {
                notify(trans("storefront::checkout.payment_proof_required"));

                return;
            }

            this.placingOrder = true;

            const checkoutPayload = this.buildCheckoutRequestBody();
            axios
                .post(
                    AestheticCart.url("/checkout"),
                    checkoutPayload
                )
                .then(({ data }) => {
                    if (data?.redirectUrl) {
                        window.location.href = data.redirectUrl;

                        return;
                    }

                    if (this.isOfflinePaymentMethod) {
                        if (data?.orderId) {
                            this.confirmOrder(
                                data.orderId,
                                this.form.payment_method
                            );
                        } else {
                            this.placingOrder = false;
                            notify(
                                data?.message ||
                                    trans(
                                        "storefront::storefront.something_went_wrong"
                                    )
                            );
                        }

                        return;
                    }

                    this.confirmOrder(
                        data.orderId,
                        this.form.payment_method
                    );
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
            if (!orderId) {
                this.placingOrder = false;
                notify(trans("storefront::storefront.something_went_wrong"));

                return;
            }

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

    })
);
