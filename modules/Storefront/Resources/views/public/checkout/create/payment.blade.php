@php
    use Modules\Payment\Services\ChipCheckoutLogo;
@endphp

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

                <span class="payment-option-radio" aria-hidden="true"><i class="las la-check"></i></span>

                <span class="payment-option-body">
                    <span class="payment-option-label" x-text="gateway.label"></span>
                    <span
                        class="payment-option-desc"
                        x-show="gateway.description && !(form.payment_method === gateway.id && shouldShowPaymentInstructions)"
                        x-text="gateway.description"
                    ></span>
                </span>

                <span class="payment-option-logos" x-show="gateway.id === 'chip'">
                    <img
                        class="payment-option-logo payment-option-logo--chip-banner"
                        src="{{ ChipCheckoutLogo::url('chip') }}"
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
                        src="{{ ChipCheckoutLogo::url('chip_fpx') }}"
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
                        src="{{ ChipCheckoutLogo::url('chip_card') }}"
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
                        src="{{ ChipCheckoutLogo::url('chip_atome') }}"
                        alt=""
                        width="280"
                        height="48"
                        loading="lazy"
                        decoding="async"
                    >
                </span>

                <span class="payment-option-logos payment-option-logos--chip-banner" x-show="gateway.id === 'chip_ewallet'">
                    <img
                        class="payment-option-logo payment-option-logo--chip-banner"
                        src="{{ ChipCheckoutLogo::url('chip_ewallet') }}"
                        alt="{{ trans('storefront::checkout.chip_ewallet_alt') }}"
                        width="320"
                        height="48"
                        loading="lazy"
                        decoding="async"
                    >
                </span>

                <span class="payment-option-logos payment-option-logos--chip-banner" x-show="gateway.id === 'chip_duitnow'">
                    <img
                        class="payment-option-logo payment-option-logo--chip-banner"
                        src="{{ ChipCheckoutLogo::url('chip_duitnow') }}"
                        alt="{{ trans('storefront::checkout.chip_duitnow_alt') }}"
                        width="320"
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

<template x-if="shouldShowPaymentInstructions">
    <div class="payment-instructions payment-instructions--modern" role="region" aria-label="{{ trans('storefront::checkout.payment_instructions') }}">
        <div class="payment-instructions__header">
            <span class="payment-instructions__icon" aria-hidden="true"><i class="las la-university"></i></span>
            <div>
                <h4 class="payment-instructions__title">{{ trans('storefront::checkout.payment_instructions') }}</h4>
                <p class="payment-instructions__lead">{{ trans('storefront::checkout.payment_instructions_lead') }}</p>
            </div>
        </div>

        <div class="payment-instructions__body" x-html="paymentInstructions"></div>

        <template x-if="form.payment_method === 'bank_transfer'">
            <div class="payment-proof-upload">
                <p class="payment-proof-upload__label">
                    {{ trans('storefront::checkout.payment_proof') }}
                    <span class="required" aria-hidden="true">*</span>
                </p>

                <label
                    class="payment-proof-dropzone"
                    for="payment-proof-input"
                    :class="{ 'has-file': Boolean(paymentProofFileName) }"
                >
                    <input
                        type="file"
                        id="payment-proof-input"
                        class="payment-proof-dropzone__input"
                        accept=".jpg,.jpeg,.png,.pdf,.webp,image/jpeg,image/png,image/webp,application/pdf"
                        @change="onPaymentProofChange($event)"
                    >

                    <span class="payment-proof-dropzone__icon" aria-hidden="true">
                        <i class="las" :class="paymentProofFileName ? 'la-check-circle' : 'la-cloud-upload-alt'"></i>
                    </span>

                    <span class="payment-proof-dropzone__copy">
                        <span class="payment-proof-dropzone__title" x-show="!paymentProofFileName">
                            {{ trans('storefront::checkout.payment_proof_choose') }}
                        </span>
                        <span class="payment-proof-dropzone__title payment-proof-dropzone__title--file" x-show="paymentProofFileName" x-cloak>
                            <span x-text="paymentProofFileName"></span>
                        </span>
                        <span class="payment-proof-dropzone__hint">
                            {{ trans('storefront::checkout.payment_proof_help') }}
                        </span>
                    </span>
                </label>
            </div>
        </template>
    </div>
</template>
