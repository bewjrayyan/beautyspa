<div class="payment-method payment-method--modern">
    <div class="checkout-card-header">
        <div class="checkout-card-heading">
            <span class="checkout-card-icon"><i class="las la-credit-card"></i></span>
            <h4 class="checkout-card-title">{{ trans('storefront::checkout.payment_method') }}</h4>
        </div>
    </div>

    <div class="payment-method-form payment-method-form--modern">
        <template x-for="gateway in gatewayOptions" :key="gateway.id">
            <label
                class="payment-option"
                :class="{ 'is-selected': form.payment_method === gateway.id }"
                :for="'payment-' + gateway.id"
            >
                <input
                    type="radio"
                    name="payment_method"
                    :value="gateway.id"
                    :id="'payment-' + gateway.id"
                    class="payment-option-input"
                    x-model="form.payment_method"
                >

                <span class="payment-option-radio"></span>

                <span class="payment-option-body">
                    <span class="payment-option-label" x-text="gateway.label"></span>
                    <span class="payment-option-desc" x-text="gateway.description"></span>
                </span>

                <span class="payment-option-logos" x-show="gateway.id === 'chip'">
                    <img
                        class="payment-option-logo payment-option-logo--chip-banner"
                        src="{{ asset('images/payments/online-banking.png') }}"
                        alt="{{ trans('storefront::account.view_order.pay_with_chip_alt') }}"
                        width="320"
                        height="48"
                        loading="lazy"
                        decoding="async"
                    >
                </span>

                <span class="payment-option-logos payment-option-logos--chip-banner" x-show="gateway.id === 'chip_fpx'">
                    <img
                        class="payment-option-logo payment-option-logo--chip-banner"
                        src="{{ asset('images/payments/online-banking-fpx.png') }}?v=1"
                        alt=""
                        width="320"
                        height="48"
                        loading="lazy"
                        decoding="async"
                    >
                </span>

                <span class="payment-option-logos payment-option-logos--chip-banner" x-show="gateway.id === 'chip_card'">
                    <img
                        class="payment-option-logo payment-option-logo--chip-banner"
                        src="{{ asset('images/payments/card-international.png') }}?v=2"
                        alt=""
                        width="320"
                        height="48"
                        loading="lazy"
                        decoding="async"
                    >
                </span>

                <span class="payment-option-logos payment-option-logos--chip-banner" x-show="gateway.id === 'chip_atome'">
                    <img
                        class="payment-option-logo payment-option-logo--chip-banner"
                        src="{{ asset('images/payments/atome-chip-part.png') }}?v=3"
                        alt=""
                        width="280"
                        height="48"
                        loading="lazy"
                        decoding="async"
                    >
                </span>
            </label>
        </template>

        <template x-if="hasNoPaymentMethod">
            <span class="error-message">
                {{ trans('storefront::checkout.no_payment_method') }}
            </span>
        </template>
    </div>
</div>

@if (setting('stripe_enabled') && setting('stripe_integration_type') === 'embedded_form')
    <div x-cloak id="stripe-element" x-show="form.payment_method === 'stripe'">
        {{-- A Stripe Element will be mounted here dynamically. --}}
    </div>
@endif

<template x-if="shouldShowPaymentInstructions">
    <div class="payment-instructions payment-instructions--modern">
        <h4 class="checkout-card-title">{{ trans('storefront::checkout.payment_instructions') }}</h4>

        <p x-html="paymentInstructions"></p>

        <template x-if="form.payment_method === 'bank_transfer'">
            <div class="payment-proof-upload">
                <label class="payment-proof-upload__label" for="payment-proof-input">
                    {{ trans('storefront::checkout.payment_proof') }}
                    <span class="required" aria-hidden="true">*</span>
                </label>

                <input
                    type="file"
                    id="payment-proof-input"
                    class="payment-proof-upload__input"
                    accept=".jpg,.jpeg,.png,.pdf,.webp,image/jpeg,image/png,image/webp,application/pdf"
                    @change="onPaymentProofChange($event)"
                >

                <p class="payment-proof-upload__help">
                    {{ trans('storefront::checkout.payment_proof_help') }}
                </p>

                <p class="payment-proof-upload__filename" x-show="paymentProofFileName" x-cloak>
                    {{ trans('storefront::checkout.payment_proof_selected') }}
                    <span x-text="paymentProofFileName"></span>
                </p>
            </div>
        </template>
    </div>
</template>
