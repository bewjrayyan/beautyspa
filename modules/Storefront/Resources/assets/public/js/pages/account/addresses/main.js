import Errors from "../../../components/Errors";

Alpine.data(
    "Addresses",
    ({
        initialAddresses,
        initialDefaultAddress,
        countries,
        profileDefaults = {},
    }) => ({
        addresses: initialAddresses,
        defaultAddress: initialDefaultAddress,
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

        get hasNoStates() {
            return Object.keys(this.states).length === 0;
        },

        init() {
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

        openNewAddress() {
            this.editing = false;
            this.errors.reset();
            this.resetForm();
            this.changeCountry(this.firstCountry);
            this.formOpen = true;
        },

        changeDefaultAddress(address) {
            if (this.defaultAddress.address_id === address.id) return;

            this.defaultAddress.address_id = address.id;

            axios
                .post("/account/addresses/change-default", {
                    address_id: address.id,
                })
                .then((response) => {
                    notify(response.data);
                })
                .catch((error) => {
                    notify(error.response.data.message);
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

                    notify(response.data.message);
                })
                .catch((error) => {
                    notify(error.response.data.message);
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
                    if (response.status === 422) {
                        this.errors.record(response.data.errors);
                    }

                    notify(response.data.message);
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

                    let address = { [data.address.id]: data.address };

                    this.addresses = {
                        ...this.addresses,
                        ...address,
                    };

                    this.resetForm();

                    notify(data.message);
                })
                .catch(({ response }) => {
                    if (response.status === 422) {
                        this.errors.record(response.data.errors);
                    }

                    notify(response.data.message);
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
    })
);
