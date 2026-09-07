@guest
    <div class="checkout-card checkout-card-account account-details checkout-account-gate">
        <div class="checkout-card-header">
            <div class="checkout-card-heading">
                <span class="checkout-card-icon"><i class="las la-user"></i></span>
                <div>
                    <h4 class="checkout-card-title">{{ trans('storefront::checkout.account_gate_title') }}</h4>
                    <p class="checkout-account-gate__lead">{{ trans('storefront::checkout.account_gate_lead') }}</p>
                </div>
            </div>
        </div>

        <div class="checkout-account-gate__body">
            <div class="checkout-account-gate__identity">
                <div class="form-group checkout-account-gate__email">
                    <label for="customer-email">
                        {{ trans('checkout::attributes.customer_email') }}<span>*</span>
                    </label>

                    <div class="checkout-account-gate__email-wrap">
                        <input
                            type="email"
                            name="customer_email"
                            id="customer-email"
                            class="form-control"
                            autocomplete="email"
                            placeholder="{{ trans('storefront::checkout.account_gate_email_placeholder') }}"
                            x-model="form.customer_email"
                            @blur="checkAccountEmail()"
                            @keydown.enter.prevent="checkAccountEmail()"
                        >

                        <span
                            class="checkout-account-gate__email-status"
                            x-cloak
                            x-show="checkingAccountEmail"
                        >
                            <i class="las la-spinner la-spin" aria-hidden="true"></i>
                        </span>
                    </div>

                    <p
                        class="account-email-checking"
                        x-cloak
                        x-show="checkingAccountEmail"
                    >
                        {{ trans('storefront::checkout.checking_email') }}
                    </p>

                    <template x-if="errors.has('customer_email')">
                        <span class="error-message" x-text="errors.get('customer_email')"></span>
                    </template>
                </div>

                <div class="form-group checkout-account-gate__phone">
                    <label for="customer-phone">
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

            <div
                class="checkout-account-panel checkout-account-panel--login"
                x-cloak
                x-show="accountEmailExists"
            >
                <div class="checkout-account-panel__banner">
                    <span class="checkout-account-panel__banner-icon" aria-hidden="true">
                        <i class="las la-user-check"></i>
                    </span>
                    <div>
                        <strong>{{ trans('storefront::checkout.account_already_exists') }}</strong>
                        <p>{{ trans('storefront::checkout.please_sign_in_to_continue') }}</p>
                    </div>
                </div>

                <div class="checkout-account-panel__fields">
                    <div class="form-group">
                        <label for="checkout-login-password">
                            {{ trans('checkout::attributes.password') }}<span>*</span>
                        </label>

                        <div
                            class="checkout-password-field"
                            x-data="{ showPassword: false }"
                        >
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                id="checkout-login-password"
                                class="form-control"
                                autocomplete="current-password"
                                placeholder="{{ trans('storefront::checkout.account_gate_password_placeholder') }}"
                                x-model="accountLoginPassword"
                                @keydown.enter.prevent="loginToAccount()"
                            >

                            <button
                                type="button"
                                class="checkout-password-toggle"
                                :aria-label="showPassword
                                    ? '{{ trans('user::auth.hide_password') }}'
                                    : '{{ trans('user::auth.show_password') }}'"
                                :title="showPassword
                                    ? '{{ trans('user::auth.hide_password') }}'
                                    : '{{ trans('user::auth.show_password') }}'"
                                @click="showPassword = !showPassword"
                            >
                                <i
                                    class="las"
                                    :class="showPassword ? 'la-eye-slash' : 'la-eye'"
                                    aria-hidden="true"
                                ></i>
                            </button>
                        </div>

                        <template x-if="accountLoginError">
                            <span class="error-message" x-text="accountLoginError"></span>
                        </template>
                    </div>

                    @if (setting('google_recaptcha_enabled'))
                        <div class="checkout-account-panel__recaptcha">
                            @include('storefront::public.partials.google_recaptcha')
                        </div>
                    @endif

                    <button
                        type="button"
                        class="btn btn-primary btn-sign-in-checkout checkout-account-panel__cta"
                        :class="{ 'btn-loading': loggingInToAccount }"
                        :disabled="loggingInToAccount || !accountLoginPassword"
                        @click="loginToAccount()"
                        x-text="loggingInToAccount ? '{{ trans('storefront::checkout.signing_in') }}' : '{{ trans('storefront::checkout.sign_in_to_checkout') }}'"
                    >
                        {{ trans('storefront::checkout.sign_in_to_checkout') }}
                    </button>

                    <div class="checkout-account-panel__links">
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

            <div
                class="checkout-account-panel checkout-account-panel--new"
                x-cloak
                x-show="!accountEmailExists && isValidEmail(form.customer_email) && !checkingAccountEmail"
            >
                <p class="checkout-account-panel__hint">
                    {{ trans('storefront::checkout.account_gate_choose_path') }}
                </p>

                <div class="checkout-account-mode" role="tablist" aria-label="{{ trans('storefront::checkout.account_gate_choose_path') }}">
                    <button
                        type="button"
                        class="checkout-account-mode__option"
                        role="tab"
                        :class="{ 'is-active': !form.create_an_account }"
                        :aria-selected="(!form.create_an_account).toString()"
                        @click="form.create_an_account = false"
                    >
                        <span class="checkout-account-mode__icon" aria-hidden="true">
                            <i class="las la-shopping-bag"></i>
                        </span>
                        <span class="checkout-account-mode__copy">
                            <strong>{{ trans('storefront::checkout.continue_as_guest') }}</strong>
                            <small>{{ trans('storefront::checkout.continue_as_guest_help') }}</small>
                        </span>
                    </button>

                    <button
                        type="button"
                        class="checkout-account-mode__option"
                        role="tab"
                        :class="{ 'is-active': form.create_an_account }"
                        :aria-selected="form.create_an_account.toString()"
                        @click="form.create_an_account = true"
                    >
                        <span class="checkout-account-mode__icon" aria-hidden="true">
                            <i class="las la-user-plus"></i>
                        </span>
                        <span class="checkout-account-mode__copy">
                            <strong>{{ trans('storefront::checkout.create_account_fast') }}</strong>
                            <small>{{ trans('storefront::checkout.create_account_fast_help') }}</small>
                        </span>
                    </button>
                </div>

                <input
                    type="checkbox"
                    name="create_an_account"
                    id="create-an-account"
                    class="sr-only"
                    tabindex="-1"
                    x-model="form.create_an_account"
                >

                <div
                    class="create-an-account-form checkout-account-register"
                    x-show="form.create_an_account"
                    x-cloak
                >
                    <div class="row">
                        <div class="col-md-9">
                            <div class="form-group">
                                <label for="password">
                                    {{ trans('checkout::attributes.password') }}<span>*</span>
                                </label>

                                <div
                                    class="checkout-password-field"
                                    x-data="{ showPassword: false }"
                                >
                                    <input
                                        :type="showPassword ? 'text' : 'password'"
                                        name="password"
                                        id="password"
                                        class="form-control"
                                        autocomplete="new-password"
                                        x-model="form.password"
                                    >

                                    <button
                                        type="button"
                                        class="checkout-password-toggle"
                                        :aria-label="showPassword
                                            ? '{{ trans('user::auth.hide_password') }}'
                                            : '{{ trans('user::auth.show_password') }}'"
                                        @click="showPassword = !showPassword"
                                    >
                                        <i
                                            class="las"
                                            :class="showPassword ? 'la-eye-slash' : 'la-eye'"
                                            aria-hidden="true"
                                        ></i>
                                    </button>
                                </div>

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

                                <div
                                    class="checkout-password-field"
                                    x-data="{ showPassword: false }"
                                >
                                    <input
                                        :type="showPassword ? 'text' : 'password'"
                                        name="password_confirmation"
                                        id="password-confirmation"
                                        class="form-control"
                                        autocomplete="new-password"
                                        x-model="form.password_confirmation"
                                    >

                                    <button
                                        type="button"
                                        class="checkout-password-toggle"
                                        :aria-label="showPassword
                                            ? '{{ trans('user::auth.hide_password') }}'
                                            : '{{ trans('user::auth.show_password') }}'"
                                        @click="showPassword = !showPassword"
                                    >
                                        <i
                                            class="las"
                                            :class="showPassword ? 'la-eye-slash' : 'la-eye'"
                                            aria-hidden="true"
                                        ></i>
                                    </button>
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

    <div class="checkout-card checkout-card-account account-details checkout-account-gate checkout-account-gate--signed-in">
        <div class="checkout-card-header">
            <div class="checkout-card-heading">
                <span class="checkout-card-icon"><i class="las la-user-check"></i></span>
                <div>
                    <h4 class="checkout-card-title">{{ trans('storefront::checkout.account_details') }}</h4>
                    <p class="checkout-account-gate__lead">{{ trans('storefront::checkout.signed_in_as_lead') }}</p>
                </div>
            </div>

            <span class="checkout-account-gate__badge">
                <i class="las la-check-circle" aria-hidden="true"></i>
                {{ trans('storefront::checkout.signed_in') }}
            </span>
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
