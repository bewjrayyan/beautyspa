import Errors from "../../../components/Errors";

Alpine.data(
    "Addresses",
    ({
        initialAddresses,
        initialDefaultAddress,
        shippingSameAsBilling: initialShippingSameAsBilling = true,
        countries,
        profileDefaults = {},
    }) => ({
        addresses: initialAddresses ?? {},
        defaultAddress: initialDefaultAddress ?? {
            address_id: null,
            shipping_address_id: null,
        },
        shippingSameAsBilling: initialShippingSameAsBilling,
        countries,
        profileDefaults,
        formOpen: false,
        editing: false,
        loading: false,
        form: { state: "" },
        states: {},
        errors: new Errors(),

        get firstCountry() {
            return Object.keys(this.countries)[0];
        },

        get hasAddress() {
            return Object.keys(this.addresses).length !== 0;
        },

        get addressList() {
            return Object.values(this.addresses).sort(
                (a, b) => Number(a.id) - Number(b.id),
            );
        },

        get billingAddressId() {
            return Number(this.defaultAddress?.address_id || 0) || null;
        },

        get shippingAddressId() {
            if (this.shippingSameAsBilling) {
                return this.billingAddressId;
            }

            return Number(this.defaultAddress?.shipping_address_id || 0) || null;
        },

        get billingAddress() {
            return this.billingAddressId
                ? this.addresses[this.billingAddressId] ??
                      this.addresses[String(this.billingAddressId)]
                : null;
        },

        get shippingAddress() {
            if (this.shippingSameAsBilling) {
                return this.billingAddress;
            }

            const id = this.defaultAddress?.shipping_address_id;

            return id
                ? this.addresses[id] ?? this.addresses[String(id)]
                : null;
        },

        get hasNoStates() {
            return Object.keys(this.states).length === 0;
        },

        init() {
            this.formOpen = !this.hasAddress;
            this.applyProfileDefaults();
            this.changeCountry(this.firstCountry);
        },

        applyProfileDefaults() {
            if (this.editing) {
                return;
            }

            if (!this.form.first_name && this.profileDefaults.first_name) {
                this.form.first_name = this.profileDefaults.first_name;
            }

            if (!this.form.last_name && this.profileDefaults.last_name) {
                this.form.last_name = this.profileDefaults.last_name;
            }
        },

        syncDefaultPayload(data = {}) {
            if (data.default_address_id !== undefined) {
                this.defaultAddress.address_id = data.default_address_id;
            }

            if (data.shipping_address_id !== undefined) {
                this.defaultAddress.shipping_address_id =
                    data.shipping_address_id;
            }

            if (data.shipping_same_as_billing !== undefined) {
                this.shippingSameAsBilling = data.shipping_same_as_billing;
            }
        },

        isBillingDefault(address) {
            return Number(this.billingAddressId) === Number(address.id);
        },

        isShippingDefault(address) {
            return (
                !this.shippingSameAsBilling &&
                Number(this.defaultAddress?.shipping_address_id) ===
                    Number(address.id)
            );
        },

        openNewAddress() {
            this.editing = false;
            this.errors.reset();
            this.resetForm();
            this.changeCountry(this.firstCountry);
            this.formOpen = true;
        },

        changeDefaultAddress(address) {
            if (this.isBillingDefault(address)) {
                return;
            }

            axios
                .post("/account/addresses/change-default", {
                    address_id: address.id,
                })
                .then((response) => {
                    this.defaultAddress.address_id = address.id;
                    notify(response.data);
                })
                .catch((error) => {
                    notify(error.response?.data?.message ?? error.message);
                });
        },

        changeDefaultShippingAddress(address) {
            if (this.isShippingDefault(address)) {
                return;
            }

            axios
                .post("/account/addresses/change-default-shipping", {
                    address_id: address.id,
                })
                .then((response) => {
                    this.defaultAddress.shipping_address_id = address.id;
                    this.shippingSameAsBilling = false;
                    notify(response.data);
                })
                .catch((error) => {
                    notify(error.response?.data?.message ?? error.message);
                });
        },

        useBillingForShipping() {
            axios
                .post("/account/addresses/use-billing-for-shipping")
                .then((response) => {
                    this.defaultAddress.shipping_address_id = null;
                    this.shippingSameAsBilling = true;
                    notify(response.data);
                })
                .catch((error) => {
                    notify(error.response?.data?.message ?? error.message);
                });
        },

        changeCountry(country) {
            this.form.country = country;
            this.form.state = "";

            this.fetchStates(country);
        },

        async fetchStates(country, callback) {
            const response = await axios.get(
                AestheticCart.apiUrl(`/countries/${country}/states`),
            );

            this.states = response.data;

            if (callback) {
                callback();
            }
        },

        edit(address) {
            this.formOpen = true;
            this.editing = true;
            this.errors.reset();

            this.$nextTick(() => {
                this.form = { ...address };

                this.fetchStates(address.country, () => {
                    this.form.state = "";

                    this.$nextTick(() => {
                        this.form.state = address.state;
                    });
                });
            });
        },

        remove(address) {
            if (!confirm(trans("storefront::account.addresses.confirm"))) {
                return;
            }

            axios
                .delete(`/account/addresses/${address.id}`)
                .then((response) => {
                    delete this.addresses[address.id];
                    delete this.addresses[String(address.id)];

                    this.syncDefaultPayload(response.data);

                    if (!this.hasAddress) {
                        this.formOpen = true;
                        this.editing = false;
                        this.resetForm();
                    }

                    notify(response.data.message);
                })
                .catch((error) => {
                    notify(error.response?.data?.message ?? error.message);
                });
        },

        cancel() {
            this.editing = false;
            this.formOpen = false;
            this.errors.reset();
            this.resetForm();
        },

        save() {
            this.loading = true;
            this.editing ? this.update() : this.create();
        },

        update() {
            axios
                .put(`/account/addresses/${this.form.id}`, this.form)
                .then(({ data }) => {
                    this.formOpen = false;
                    this.editing = false;
                    this.addresses[this.form.id] = data.address;
                    this.resetForm();
                    notify(data.message);
                })
                .catch(({ response }) => {
                    if (response?.status === 422) {
                        this.errors.record(response.data.errors);
                    }

                    notify(response?.data?.message ?? "Error");
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        create() {
            axios
                .post("/account/addresses", this.form)
                .then(({ data }) => {
                    this.formOpen = false;
                    this.addresses[data.address.id] = data.address;
                    this.syncDefaultPayload(data);
                    this.resetForm();
                    notify(data.message);
                })
                .catch(({ response }) => {
                    if (response?.status === 422) {
                        this.errors.record(response.data.errors);
                    }

                    notify(response?.data?.message ?? "Error");
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        resetForm() {
            this.form = {
                state: "",
                first_name: this.profileDefaults.first_name ?? "",
                last_name: this.profileDefaults.last_name ?? "",
            };
        },
    }),
);
