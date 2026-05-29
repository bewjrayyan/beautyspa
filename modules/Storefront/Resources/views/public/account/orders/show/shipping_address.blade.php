<div class="account-order-address-card">
    <h3 class="account-order-address-card__title">
        <i class="las la-shipping-fast"></i>
        {{ trans('storefront::account.view_order.shipping_address') }}
    </h3>

    <address class="account-order-address-card__body">
        <span>{{ $order->shipping_full_name }}</span>
        <span>{{ $order->shipping_address_1 }}</span>

        @if ($order->shipping_address_2)
            <span>{{ $order->shipping_address_2 }}</span>
        @endif

        <span>{{ $order->shipping_city }}, {!! $order->shipping_state_name !!} {{ $order->shipping_zip }}</span>
        <span>{{ $order->shipping_country_name }}</span>
    </address>
</div>
