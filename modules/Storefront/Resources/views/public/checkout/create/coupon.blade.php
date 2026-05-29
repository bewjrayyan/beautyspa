<div class="coupon-wrap coupon-wrap--modern">
    <div class="checkout-promo-row">
        <input
            type="text"
            placeholder="{{ trans('storefront::checkout.enter_coupon_code') }}"
            class="form-control checkout-promo-input"
            @keyup.enter="applyCoupon"
            @input="couponError = null"
            x-model="couponCode"
        >

        <button
            type="button"
            class="btn btn-apply-coupon checkout-promo-btn"
            @click.prevent="applyCoupon"
        >
            {{ trans('storefront::checkout.apply') }}
        </button>
    </div>

    <template x-if="couponError">
        <span class="error-message" x-text="couponError"></span>
    </template>
</div>
