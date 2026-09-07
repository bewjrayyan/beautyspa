<template x-if="hasShippingMethod">
    <div class="shipping-method shipping-method--modern">
        <div class="checkout-card-header">
            <div class="checkout-card-heading">
                <span class="checkout-card-icon"><i class="las la-shipping-fast"></i></span>
                <h4 class="checkout-card-title">{{ trans('storefront::checkout.shipping_method') }}</h4>
            </div>
        </div>

        <template x-if="form.ship_to_a_different_address">
            <div class="shipping-method-destination" x-cloak>
                <div class="shipping-method-destination__header">
                    <i class="las la-map-marker-alt" aria-hidden="true"></i>
                    <span>{{ trans('storefront::checkout.shipping_address') }}</span>
                </div>

                <template x-if="shippingDestinationSummary">
                    <div class="shipping-method-destination__body">
                        <strong
                            x-show="shippingDestinationSummary.fullName"
                            x-text="shippingDestinationSummary.fullName"
                        ></strong>
                        <template x-for="(line, lineIndex) in shippingDestinationSummary.lines" :key="'ship-line-' + lineIndex">
                            <span x-text="line"></span>
                        </template>
                    </div>
                </template>

                <template x-if="!shippingDestinationSummary">
                    <p class="shipping-method-destination__empty">
                        {{ trans('storefront::checkout.select_shipping_address_first') }}
                    </p>
                </template>
            </div>
        </template>

        <div class="shipping-method-form shipping-method-form--modern">
            <template x-for="(shippingMethod, key) in cart.availableShippingMethods" :key="shippingMethod.name || key">
                <label
                    class="shipping-option"
                    :class="{ 'is-selected': form.shipping_method === shippingMethod.name }"
                    :for="'shipping-' + shippingMethod.name"
                >
                    <input
                        type="radio"
                        name="shipping_method"
                        :value="shippingMethod.name"
                        :id="'shipping-' + shippingMethod.name"
                        class="shipping-option-input"
                        @change="updateShippingMethod(shippingMethod.name)"
                        x-model="form.shipping_method"
                    >

                    <span class="shipping-option-radio" aria-hidden="true"><i class="las la-check"></i></span>

                    <span class="shipping-option-icon" aria-hidden="true">
                        <i :class="shippingMethodIcon(shippingMethod.name)"></i>
                    </span>

                    <span class="shipping-option-body">
                        <span class="shipping-option-label" x-text="shippingMethod.label"></span>
                        <span
                            class="shipping-option-desc"
                            x-text="shippingMethodDescription(shippingMethod.name)"
                        ></span>
                    </span>

                    <span
                        class="shipping-option-price"
                        :class="{ 'text-line-through': hasFreeShipping && shippingMethod.name !== 'free_shipping' }"
                        x-text="formatCurrency(shippingMethod.cost.inCurrentCurrency.amount)"
                    ></span>
                </label>
            </template>
        </div>
    </div>
</template>
