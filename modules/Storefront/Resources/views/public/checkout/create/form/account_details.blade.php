@guest
    <div class="checkout-card checkout-card-account account-details">
        <div class="checkout-card-header">
            <div class="checkout-card-heading">
                <span class="checkout-card-icon"><i class="las la-user"></i></span>
                <h4 class="checkout-card-title">{{ trans('storefront::checkout.account_details') }}</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-9">
                <div class="form-group">
                    <label for="email">
                        {{ trans('checkout::attributes.customer_email') }}<span>*</span>
                    </label>

                    <input
                        type="email"
                        name="customer_email"
                        id="customer-email"
                        class="form-control"
                        autocomplete="email"
                        x-model="form.customer_email"
                        @blur="checkAccountEmail()"
                    >

                    <p
                        class="account-email-checking"
                        x-cloak
                        x-show="checkingAccountEmail"
                    >
                        <i class="las la-spinner la-spin"></i>
                        {{ trans('storefront::checkout.checking_email') }}
                    </p>

                    <template x-if="errors.has('customer_email')">
                        <span class="error-message" x-text="errors.get('customer_email')"></span>
                    </template>
                </div>
            </div>

            <div class="col-md-9">
                <div class="form-group">
                    <label for="phone">
                        {{ trans('checkout::attributes.customer_phone') }}<span>*</span>
                    </label>

                    @include('storefront::public.partials.phone_input', [
                        'name' => 'customer_phone',
                        'id' => 'customer-phone',
                        'value' => auth()->user()?->phone,
                        'required' => true,
                        'extraAttributes' => '@phone:change="form.customer_phone = $event.detail.number"',
                    ])

                    <template x-if="errors.has('customer_phone')">
                        <span class="error-message" x-text="errors.get('customer_phone')"></span>
                    </template>
                </div>
            </div>

            <div class="col-md-18">
                <div
                    class="checkout-account-exists"
                    x-cloak
                    x-show="accountEmailExists"
                >
                    <div class="checkout-account-exists-alert">
                        <i class="las la-info-circle"></i>
                        <div>
                            <strong>{{ trans('storefront::checkout.account_already_exists') }}</strong>
                            <p>{{ trans('storefront::checkout.please_sign_in_to_continue') }}</p>
                        </div>
                    </div>

                    <div class="checkout-inline-login">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="checkout-login-password">
                                        {{ trans('checkout::attributes.password') }}<span>*</span>
                                    </label>

                                    <input
                                        type="password"
                                        id="checkout-login-password"
                                        class="form-control"
                                        autocomplete="current-password"
                                        x-model="accountLoginPassword"
                                        @keydown.enter.prevent="loginToAccount()"
                                    >

                                    <template x-if="accountLoginError">
                                        <span class="error-message" x-text="accountLoginError"></span>
                                    </template>
                                </div>
                            </div>

                            @if (setting('google_recaptcha_enabled'))
                                <div class="col-md-18">
                                    @include('storefront::public.partials.google_recaptcha')
                                </div>
                            @endif

                            <div class="col-md-9 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sign-in-checkout w-100"
                                        :class="{ 'btn-loading': loggingInToAccount }"
                                        :disabled="loggingInToAccount || !accountLoginPassword"
                                        @click="loginToAccount()"
                                        x-text="loggingInToAccount ? '{{ trans('storefront::checkout.signing_in') }}' : '{{ trans('storefront::checkout.sign_in') }}'"
                                    >
                                        {{ trans('storefront::checkout.sign_in') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-inline-login-actions">
                            <a href="{{ storefront_route('reset') }}">
                                {{ trans('storefront::checkout.forgot_password') }}
                            </a>

                            <button
                                type="button"
                                class="btn btn-link btn-use-different-email"
                                @click="useDifferentEmail()"
                            >
                                {{ trans('storefront::checkout.use_different_email') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div x-show="!accountEmailExists" x-cloak>
                    <div class="form-group create-an-account-label">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                name="create_an_account"
                                id="create-an-account"
                                x-model="form.create_an_account"
                            >

                            <label for="create-an-account" class="form-check-label">
                                {{ trans('checkout::attributes.create_an_account') }}
                            </label>
                        </div>

                        <span class="helper-text">
                            {{ trans('storefront::checkout.create_an_account_by_entering_the_information_below') }}
                        </span>
                    </div>

                    <div x-show="form.create_an_account" class="create-an-account-form">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="password">
                                        {{ trans('checkout::attributes.password') }}<span>*</span>
                                    </label>

                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control"
                                        autocomplete="new-password"
                                        x-model="form.password"
                                    >

                                    <template x-if="errors.has('password')">
                                        <span class="error-message" x-text="errors.get('password')"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="password-confirmation">
                                        {{ trans('user::auth.confirm_password') }}<span>*</span>
                                    </label>

                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        id="password-confirmation"
                                        class="form-control"
                                        autocomplete="new-password"
                                        x-model="form.password_confirmation"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endguest

@auth
    @php
        $authPhoneE164 = auth()->user()?->phone
            ? \Modules\User\Support\PhoneNumber::toE164(auth()->user()->phone)
            : '';
    @endphp

    <div class="checkout-card checkout-card-account account-details">
        <div class="checkout-card-header">
            <div class="checkout-card-heading">
                <span class="checkout-card-icon"><i class="las la-user"></i></span>
                <h4 class="checkout-card-title">{{ trans('storefront::checkout.account_details') }}</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-9">
                <div class="form-group">
                    <label for="customer-email">
                        {{ trans('checkout::attributes.customer_email') }}<span>*</span>
                    </label>

                    <input
                        type="email"
                        name="customer_email"
                        id="customer-email"
                        class="form-control"
                        autocomplete="email"
                        readonly
                        x-model="form.customer_email"
                    >
                </div>
            </div>

            <div class="col-md-9">
                <div class="form-group">
                    <label for="customer-phone">
                        {{ trans('checkout::attributes.customer_phone') }}<span>*</span>
                    </label>

                    @include('storefront::public.partials.phone_input', [
                        'name' => 'customer_phone',
                        'id' => 'customer-phone',
                        'value' => $authPhoneE164,
                        'required' => true,
                        'extraAttributes' => '@phone:change="form.customer_phone = $event.detail.number"',
                    ])

                    <template x-if="errors.has('customer_phone')">
                        <span class="error-message" x-text="errors.get('customer_phone')"></span>
                    </template>
                </div>
            </div>
        </div>
    </div>
@endauth
