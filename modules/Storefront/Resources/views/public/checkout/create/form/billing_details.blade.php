<div class="billing-details">
    <div class="checkout-card-header">
        <div class="checkout-card-heading">
            <span class="checkout-card-icon"><i class="las la-map-marker"></i></span>
            <h4 class="checkout-card-title">{{ trans('storefront::checkout.billing_details') }}</h4>
        </div>

        <template x-if="hasReusableBilling">
            <button type="button" class="checkout-card-link" @click="addNewBillingAddress">
                <span x-text="form.newBillingAddress ? '←' : '+'"></span>
                <span x-cloak x-show="form.newBillingAddress">{{ trans('storefront::checkout.use_saved_address') }}</span>
                <span x-cloak x-show="!form.newBillingAddress">{{ trans('storefront::checkout.use_another_address') }}</span>
            </button>
        </template>
    </div>

    <template x-if="hasReusableBilling && !form.newBillingAddress">
        <div x-cloak class="checkout-saved-address-box">
            <div class="checkout-saved-address-heading">
                <span class="checkout-promo-label">
                    <i class="las la-map-marker checkout-promo-label__icon"></i>
                    {{ trans('storefront::checkout.use_saved_address') }}
                </span>
                <span class="address-card-badge">{{ trans('storefront::checkout.recommended') }}</span>
            </div>

            <template x-if="hasAddress">
                <div class="address-card-wrap address-card-wrap--modern">
                    <template x-for="address in addresses" :key="address.id">
                        <address
                            class="address-card address-card--modern"
                            :class="{ active: form.billingAddressId === address.id }"
                            @click="changeBillingAddress(address)"
                        >
                            <span class="address-card-radio" :class="{ 'is-checked': form.billingAddressId === address.id }">
                                <i class="las la-check"></i>
                            </span>

                            <template x-if="defaultAddress.address_id === address.id">
                                <span class="address-card-badge">{{ trans('storefront::checkout.default') }}</span>
                            </template>

                            <div class="address-card-data">
                                <strong class="address-card-name" x-text="address.full_name"></strong>
                                <span x-text="address.address_1"></span>

                                <template x-if="address.address_2">
                                    <span x-text="address.address_2"></span>
                                </template>

                                <span x-text="`${address.city}, ${address.state_name ?? address.state} ${address.zip}`"></span>
                                <span x-text="address.country_name"></span>
                            </div>
                        </address>
                    </template>
                </div>
            </template>

            <template x-if="!hasAddress && isCompleteAddress(customerBilling)">
                <address class="address-card address-card--modern active checkout-recent-address">
                    <span class="address-card-radio is-checked"><i class="las la-check"></i></span>
                    <span class="address-card-badge">{{ trans('storefront::checkout.last_used') }}</span>

                    <div class="address-card-data">
                        <strong class="address-card-name" x-text="`${customerBilling.first_name} ${customerBilling.last_name}`"></strong>
                        <span x-text="customerBilling.address_1"></span>

                        <template x-if="customerBilling.address_2">
                            <span x-text="customerBilling.address_2"></span>
                        </template>

                        <span x-text="`${customerBilling.city}, ${customerBilling.state} ${customerBilling.zip}`"></span>
                        <span x-text="countries[customerBilling.country] ?? customerBilling.country"></span>
                    </div>
                </address>
            </template>
        </div>
    </template>

    <div x-cloak class="add-new-address-wrap">
        <div class="add-new-address-form" x-show="!hasReusableBilling || form.newBillingAddress">
            <div class="row">
                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-first-name">
                            {{ trans('checkout::attributes.billing.first_name') }}<span>*</span>
                        </label>

                        <input
                            type="text"
                            name="billing[first_name]"
                            id="billing-first-name"
                            class="form-control"
                            x-model="form.billing.first_name"
                        >

                        <template x-if="errors.has('billing.first_name')">
                            <span class="error-message" x-text="errors.get('billing.first_name')"></span>
                        </template>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-last-name">
                            {{ trans('checkout::attributes.billing.last_name') }}<span>*</span>
                        </label>

                        <input
                            type="text"
                            name="billing[last_name]"
                            id="billing-last-name"
                            class="form-control"
                            x-model="form.billing.last_name"
                        >

                        <template x-if="errors.has('billing.last_name')">
                            <span class="error-message" x-text="errors.get('billing.last_name')"></span>
                        </template>
                    </div>
                </div>

                <div class="col-md-18">
                    <div class="form-group">
                        <label for="billing-address-1">
                            {{ trans('checkout::attributes.street_address') }}<span>*</span>
                        </label>

                        <input
                            type="text"
                            name="billing[address_1]"
                            id="billing-address-1"
                            class="form-control"
                            placeholder="{{ trans('checkout::attributes.billing.address_1') }}"
                            x-model="form.billing.address_1"
                        >

                        <template x-if="errors.has('billing.address_1')">
                            <span class="error-message" x-text="errors.get('billing.address_1')"></span>
                        </template>
                    </div>

                    <div class="form-group">
                        <input
                            type="text"
                            name="billing[address_2]"
                            class="form-control"
                            placeholder="{{ trans('checkout::attributes.billing.address_2') }}"
                            x-model="form.billing.address_2"
                        >
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-city">
                            {{ trans('checkout::attributes.billing.city') }}<span>*</span>
                        </label>

                        <input
                            type="text"
                            name="billing[city]"
                            id="billing-city"
                            class="form-control"
                            x-model="form.billing.city"
                            @change="changeBillingCity($event.target.value)"
                        >

                        <template x-if="errors.has('billing.city')">
                            <span class="error-message" x-text="errors.get('billing.city')"></span>
                        </template>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-zip">
                            {{ trans('checkout::attributes.billing.zip') }}<span>*</span>
                        </label>

                        <input
                            type="text"
                            name="billing[zip]"
                            id="billing-zip"
                            class="form-control"
                            x-model="form.billing.zip"
                            @change="changeBillingZip($event.target.value)"
                        >

                        <template x-if="errors.has('billing.zip')">
                            <span class="error-message" x-text="errors.get('billing.zip')"></span>
                        </template>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-country">
                            {{ trans('checkout::attributes.billing.country') }}<span>*</span>
                        </label>

                        <select
                            name="billing[country]"
                            id="billing-country"
                            class="form-control arrow-black"
                            x-model="form.billing.country"
                            @change="changeBillingCountry($event.target.value)"
                        >
                            <option value="">{{ trans('storefront::checkout.please_select') }}</option>

                            <template x-for="(name, code) in countries" :key="code">
                                <option :value="code" x-text="name"></option>
                            </template>
                        </select>

                        <template x-if="errors.has('billing.country')">
                            <span class="error-message" x-text="errors.get('billing.country')"></span>
                        </template>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-state">
                            {{ trans('checkout::attributes.billing.state') }}<span>*</span>
                        </label>

                        <template x-if="!hasBillingStates">
                            <input
                                type="text"
                                name="billing[state]"
                                id="billing-state"
                                class="form-control"
                                x-model="form.billing.state"
                            >
                        </template>

                        <template x-if="hasBillingStates">
                            <select
                                name="billing[state]"
                                id="billing-state"
                                class="form-control arrow-black"
                                x-model="form.billing.state"
                                @change="changeBillingState($event.target.value)"
                            >
                                <option value="">{{ trans('storefront::checkout.please_select') }}</option>

                                <template x-for="(name, code) in states.billing" :key="code">
                                    <option :value="code" x-text="name"></option>
                                </template>
                            </select>
                        </template>

                        <template x-if="errors.has('billing.state')">
                            <span class="error-message" x-text="errors.get('billing.state')"></span>
                        </template>
                    </div>
                </div>

                <template x-if="loggedIn">
                    <div class="col-md-18">
                        <div class="checkout-address-save-options">
                            <label class="checkout-address-save-option" for="save-billing-address">
                                <input
                                    type="checkbox"
                                    id="save-billing-address"
                                    x-model="form.saveBillingAddress"
                                >
                                <span>{{ trans('storefront::checkout.save_address_for_next_order') }}</span>
                            </label>

                            <label
                                x-show="form.saveBillingAddress && hasAddress"
                                class="checkout-address-save-option checkout-address-save-option--secondary"
                                for="make-billing-address-default"
                            >
                                <input
                                    type="checkbox"
                                    id="make-billing-address-default"
                                    x-model="form.makeBillingAddressDefault"
                                >
                                <span>{{ trans('storefront::checkout.make_default_address') }}</span>
                            </label>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
