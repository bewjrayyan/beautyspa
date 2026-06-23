<template x-if="!cartIsEmpty">
    <aside class="order-summary-wrap">
        <div class="order-summary cart-order-summary">
            <div class="order-summary-top">
                <h3 class="section-title">{{ trans('storefront::cart.cart_summary') }}</h3>
            </div>

            <div class="order-summary-middle">
                <p
                    class="cart-summary-meta"
                    x-show="$store.cart.totalQty > 0"
                    x-text="`${$store.cart.lineCount} {{ trans('storefront::cart.treatments') }} · ${$store.cart.totalQty} {{ trans('storefront::cart.qty') }}`"
                ></p>

                <ul class="list-inline order-summary-list">
                    <template x-for="cartItem in $store.cart.items" :key="cartItem.id">
                        <li x-data="CartItem(cartItem)" class="cart-summary-line-item">
                            <label>
                                <span
                                    class="cart-summary-line-item__name"
                                    x-text="productName"
                                ></span>

                                <template
                                    x-for="(line, index) in summaryTreatmentLines"
                                    :key="`${cartItem.id}-variation-${index}`"
                                >
                                    <span
                                        class="cart-summary-line-item__meta"
                                        x-text="`${line.name}: ${line.value}`"
                                    ></span>
                                </template>

                                <template x-if="qty > 1">
                                    <span
                                        class="cart-summary-line-item__qty"
                                        x-text="`× ${qty}`"
                                    ></span>
                                </template>
                            </label>

                            <div class="cart-summary-line-item__prices">
                                <template x-if="hasSpecialPrice">
                                    <span
                                        class="previous-price"
                                        x-text="formatCurrency(lineRegularTotal(qty))"
                                    ></span>
                                </template>

                                <span
                                    class="special-price"
                                    :class="{ 'is-regular-price': !hasSpecialPrice }"
                                    x-text="formatCurrency(lineTotal(qty))"
                                ></span>
                            </div>
                        </li>
                    </template>

                    <template x-if="$store.cart.hasSavings">
                        <li class="cart-summary-regular">
                            <label>{{ trans('storefront::cart.subtotal_regular') }}</label>

                            <span
                                class="previous-price"
                                x-text="formatCurrency($store.cart.regularSubTotal)"
                            ></span>
                        </li>
                    </template>

                    <li
                        class="cart-summary-subtotal"
                        :class="{ 'cart-summary-subtotal--divider': !$store.cart.hasSavings }"
                    >
                        <label>{{ trans('storefront::cart.subtotal') }}</label>

                        <span x-text="formatCurrency($store.cart.subTotal)"></span>
                    </li>

                    <template x-if="$store.cart.hasSavings">
                        <li class="cart-summary-savings">
                            <label>{{ trans('storefront::cart.total_savings') }}</label>

                            <span x-text="`-${formatCurrency($store.cart.totalSavings)}`"></span>
                        </li>
                    </template>
                </ul>

                <div class="order-summary-total cart-order-summary-total">
                    <label>{{ trans('storefront::cart.total_payable') }}</label>

                    <span x-text="formatCurrency($store.cart.subTotal)"></span>
                </div>
            </div>

            <div class="order-summary-bottom">
                <a
                    href="{{ route('checkout.create') }}"
                    class="btn btn-primary btn-proceed-to-checkout"
                >
                    {{ trans('storefront::cart.proceed_to_checkout') }}
                </a>
            </div>
        </div>
    </aside>
</template>
