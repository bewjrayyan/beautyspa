<div class="steps-wrap checkout-steps">
    <div class="container checkout-steps-tabs-wrap">
        <ul class="list-inline step-tabs">
            <li class="step-tab">
                <a href="{{ route('cart.index') }}" class="step-tab-link">
                    <span class="step-tab-text">
                        {{ trans('storefront::cart.my_cart') }}
                        <span class="bg-text">{{ trans('storefront::cart.01') }}</span>
                    </span>
                </a>
            </li>

            <li class="step-tab active">
                <span class="step-tab-text">
                    {{ trans('storefront::cart.checkout') }}
                    <span class="bg-text">{{ trans('storefront::cart.02') }}</span>
                </span>
            </li>

            <li class="step-tab">
                <span class="step-tab-text">
                    {{ trans('storefront::cart.order_complete') }}
                    <span class="bg-text">{{ trans('storefront::cart.03') }}</span>
                </span>
            </li>
        </ul>
    </div>

    <div class="checkout-steps-dropdown-wrap">
        <label class="sr-only" for="checkout-step-select">{{ trans('storefront::cart.checkout') }}</label>
        <select id="checkout-step-select" class="form-control checkout-steps-select" onchange="if (this.value) window.location.href = this.value">
            <option value="{{ route('cart.index') }}">{{ trans('storefront::cart.01') }} — {{ trans('storefront::cart.my_cart') }}</option>
            <option value="{{ route('checkout.create') }}" selected>{{ trans('storefront::cart.02') }} — {{ trans('storefront::cart.checkout') }}</option>
            <option value="" disabled>{{ trans('storefront::cart.03') }} — {{ trans('storefront::cart.order_complete') }}</option>
        </select>
    </div>
</div>
