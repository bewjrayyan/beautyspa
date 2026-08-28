@extends('storefront::public.account.layout')

@section('title', trans('storefront::account.pages.my_addresses'))

@section('account_breadcrumb')
    <li class="active">{{ trans('storefront::account.pages.my_addresses') }}</li>
@endsection

@section('panel')
    <div x-data="Addresses(@js($addressesConfig))" class="account-addresses-show panel">
        <div class="account-addresses-show__header panel-header">
            <div class="account-addresses-show__heading">
                <h4>{{ trans('storefront::account.pages.my_addresses') }}</h4>
                <p class="account-addresses-show__lead">{{ trans('storefront::account.addresses.saved_lead') }}</p>
            </div>

            <template x-if="hasAddress">
                <button type="button" class="account-addresses-show__toggle" @click="formOpen ? cancel() : openNewAddress()">
                    <span x-text="formOpen ? '←' : '+'"></span>
                    <span x-cloak x-show="formOpen">{{ trans('storefront::checkout.use_saved_address') }}</span>
                    <span x-cloak x-show="!formOpen">{{ trans('storefront::checkout.use_another_address') }}</span>
                </button>
            </template>
        </div>

        <div x-cloak class="panel-body account-addresses-show__body">
            <template x-if="hasAddress && !formOpen">
                <div class="account-addresses-saved">
                    <div class="account-addresses-role-grid">
                        <div class="account-addresses-saved__box account-addresses-saved__box--billing">
                            <div class="account-addresses-saved__heading">
                                <span class="account-addresses-saved__label">
                                    <i class="las la-file-invoice" aria-hidden="true"></i>
                                    {{ trans('storefront::account.addresses.billing_address') }}
                                </span>
                                <span class="address-card-badge">{{ trans('storefront::checkout.recommended') }}</span>
                            </div>

                            <template x-if="billingAddress">
                                <div class="account-addresses-featured">
                                    <span class="address-card-radio is-checked"><i class="las la-check" aria-hidden="true"></i></span>
                                    <span class="address-card-badge">{{ trans('storefront::account.addresses.default_billing') }}</span>
                                    <span class="address-card-data">
                                        <strong class="address-card-name" x-text="billingAddress.full_name"></strong>
                                        <span x-text="billingAddress.address_1"></span>
                                        <template x-if="billingAddress.address_2"><span x-text="billingAddress.address_2"></span></template>
                                        <span x-text="`${billingAddress.city}, ${billingAddress.state_name ?? billingAddress.state} ${billingAddress.zip}`"></span>
                                        <span x-text="billingAddress.country_name"></span>
                                    </span>
                                </div>
                            </template>
                        </div>

                        <div class="account-addresses-saved__box account-addresses-saved__box--shipping">
                            <div class="account-addresses-saved__heading">
                                <span class="account-addresses-saved__label">
                                    <i class="las la-shipping-fast" aria-hidden="true"></i>
                                    {{ trans('storefront::account.addresses.shipping_address') }}
                                </span>
                            </div>

                            <template x-if="shippingSameAsBilling && billingAddress">
                                <div class="account-addresses-same">
                                    <p class="account-addresses-same__title">{{ trans('storefront::account.addresses.same_as_billing') }}</p>
                                    <p class="account-addresses-same__lead">{{ trans('storefront::account.addresses.same_as_billing_lead') }}</p>
                                    <button type="button" class="account-addresses-show__toggle" @click="openNewAddress()">
                                        <span>+</span>
                                        <span>{{ trans('storefront::account.addresses.use_different_shipping') }}</span>
                                    </button>
                                </div>
                            </template>

                            <template x-if="!shippingSameAsBilling && shippingAddress">
                                <div class="account-addresses-featured">
                                    <span class="address-card-radio is-checked"><i class="las la-check" aria-hidden="true"></i></span>
                                    <span class="address-card-badge address-card-badge--shipping">{{ trans('storefront::account.addresses.default_shipping') }}</span>
                                    <span class="address-card-data">
                                        <strong class="address-card-name" x-text="shippingAddress.full_name"></strong>
                                        <span x-text="shippingAddress.address_1"></span>
                                        <template x-if="shippingAddress.address_2"><span x-text="shippingAddress.address_2"></span></template>
                                        <span x-text="`${shippingAddress.city}, ${shippingAddress.state_name ?? shippingAddress.state} ${shippingAddress.zip}`"></span>
                                        <span x-text="shippingAddress.country_name"></span>
                                    </span>
                                    <button type="button" class="account-addresses-show__toggle account-addresses-show__toggle--inline" @click="useBillingForShipping()">
                                        {{ trans('storefront::account.addresses.use_billing_for_shipping') }}
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="account-addresses-all">
                        <h5 class="account-addresses-all__title">{{ trans('storefront::account.addresses.all_saved_addresses') }}</h5>

                        <div class="address-card-wrap address-card-wrap--modern">
                            <template x-for="address in addressList" :key="address.id">
                                <address class="address-card address-card--modern account-address-card">
                                    <div class="account-address-card__content-wrap">
                                        <span class="address-card-data">
                                            <strong class="address-card-name" x-text="address.full_name"></strong>
                                            <span x-text="address.address_1"></span>
                                            <template x-if="address.address_2"><span x-text="address.address_2"></span></template>
                                            <span x-text="`${address.city}, ${address.state_name ?? address.state} ${address.zip}`"></span>
                                            <span x-text="address.country_name"></span>
                                        </span>

                                        <div class="account-address-card__badges">
                                            <template x-if="isBillingDefault(address)">
                                                <span class="address-card-badge">{{ trans('storefront::account.addresses.default_billing') }}</span>
                                            </template>
                                            <template x-if="isShippingDefault(address)">
                                                <span class="address-card-badge address-card-badge--shipping">{{ trans('storefront::account.addresses.default_shipping') }}</span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="account-address-card__actions">
                                        <button type="button" class="btn btn-link account-address-card__action" @click="changeDefaultAddress(address)" x-show="!isBillingDefault(address)">
                                            {{ trans('storefront::account.addresses.set_as_billing') }}
                                        </button>
                                        <button type="button" class="btn btn-link account-address-card__action" @click="changeDefaultShippingAddress(address)" x-show="!isShippingDefault(address)">
                                            {{ trans('storefront::account.addresses.set_as_shipping') }}
                                        </button>
                                        <button type="button" class="btn btn-link account-address-card__action" @click="useBillingForShipping()" x-show="!shippingSameAsBilling && isBillingDefault(address)">
                                            {{ trans('storefront::account.addresses.use_billing_for_shipping') }}
                                        </button>
                                        <button type="button" class="btn btn-link account-address-card__action" @click="edit(address)">
                                            <i class="las la-pen" aria-hidden="true"></i>
                                            {{ trans('storefront::account.addresses.edit') }}
                                        </button>
                                        <button type="button" class="btn btn-link account-address-card__action account-address-card__action--danger" @click="remove(address)">
                                            <i class="las la-trash-alt" aria-hidden="true"></i>
                                            {{ trans('storefront::account.addresses.delete') }}
                                        </button>
                                    </div>
                                </address>
                            </template>
                        </div>
                    </div>

                    <button type="button" class="btn btn-default btn-lg account-addresses-show__add-btn" @click="openNewAddress()">
                        <i class="las la-plus" aria-hidden="true"></i>
                        {{ trans('storefront::account.addresses.add_new_address') }}
                    </button>
                </div>
            </template>

            <template x-if="!hasAddress || formOpen">
                <form class="account-addresses-form" @submit.prevent="save" @input="errors.clear($event.target.name)">
                    <div class="add-new-address-form">
                        <h5 class="section-title" x-text="editing ? @js(trans('storefront::account.addresses.edit_address')) : @js(trans('storefront::account.addresses.new_address'))"></h5>
                        <div class="row">
                            <div class="col-md-9"><div class="form-group"><label for="first-name">{{ trans('storefront::account.addresses.first_name') }}<span>*</span></label><input name="first_name" type="text" id="first-name" class="form-control" x-model="form.first_name"><template x-if="errors.has('first_name')"><span class="error-message" x-text="errors.get('first_name')"></span></template></div></div>
                            <div class="col-md-9"><div class="form-group"><label for="last-name">{{ trans('storefront::account.addresses.last_name') }}<span>*</span></label><input name="last_name" type="text" id="last-name" class="form-control" x-model="form.last_name"><template x-if="errors.has('last_name')"><span class="error-message" x-text="errors.get('last_name')"></span></template></div></div>
                            <div class="col-md-18"><div class="form-group"><label for="address-1">{{ trans('storefront::account.addresses.street_address') }}<span>*</span></label><input name="address_1" type="text" id="address-1" placeholder="{{ trans('storefront::account.addresses.address_line_1') }}" class="form-control" x-model="form.address_1"><template x-if="errors.has('address_1')"><span class="error-message" x-text="errors.get('address_1')"></span></template></div><div class="form-group"><input name="address_2" type="text" id="address-2" placeholder="{{ trans('storefront::account.addresses.address_line_2') }}" class="form-control" x-model="form.address_2"></div></div>
                            <div class="col-md-9"><div class="form-group"><label for="city">{{ trans('storefront::account.addresses.city') }}<span>*</span></label><input name="city" type="text" id="city" class="form-control" x-model="form.city"><template x-if="errors.has('city')"><span class="error-message" x-text="errors.get('city')"></span></template></div></div>
                            <div class="col-md-9"><div class="form-group"><label for="zip">{{ trans('storefront::account.addresses.zip') }}<span>*</span></label><input name="zip" type="text" id="zip" class="form-control" x-model="form.zip"><template x-if="errors.has('zip')"><span class="error-message" x-text="errors.get('zip')"></span></template></div></div>
                            <div class="col-md-9"><div class="form-group"><label for="country">{{ trans('storefront::account.addresses.country') }}<span>*</span></label><select :value="form.country" name="country" id="country" class="form-control arrow-black" @change="changeCountry($event.target.value)"><template x-for="(name, code) in countries"><option :value="code" x-text="name"></option></template></select><template x-if="errors.has('country')"><span class="error-message" x-text="errors.get('country')"></span></template></div></div>
                            <div class="col-md-9"><div class="form-group"><label for="state">{{ trans('storefront::account.addresses.state') }}<span>*</span></label><template x-if="hasNoStates"><input name="state" type="text" id="state" class="form-control" x-model="form.state"></template><template x-if="!hasNoStates"><select name="state" id="state" class="form-control arrow-black" x-model="form.state"><option value="">{{ trans('storefront::account.addresses.please_select') }}</option><template x-for="(name, code) in states"><option :value="code" x-html="name"></option></template></select></template><template x-if="errors.has('state')"><span class="error-message" x-text="errors.get('state')"></span></template></div></div>
                            <div class="col-md-18 account-addresses-form__actions">
                                <button type="button" class="btn btn-lg btn-default btn-cancel" x-show="hasAddress" @click="cancel">{{ trans('storefront::account.addresses.cancel') }}</button>
                                <button type="submit" class="btn btn-lg btn-primary btn-save-address" :class="{ 'btn-loading': loading }">{{ trans('storefront::account.addresses.save_address') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </template>
        </div>
    </div>
@endsection

@push('globals')
    <script>AestheticCart.langs['storefront::account.addresses.confirm'] = '{{ trans('storefront::account.addresses.confirm') }}';</script>
    @vite(['modules/Storefront/Resources/assets/public/sass/pages/account/addresses/main.scss','modules/Storefront/Resources/assets/public/js/pages/account/addresses/main.js'])
@endpush
