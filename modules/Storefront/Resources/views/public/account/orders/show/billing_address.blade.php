<div class="account-order-address-card">
    <h3 class="account-order-address-card__title">
        <i class="las la-file-invoice"></i>
        {{ trans('storefront::account.view_order.billing_address') }}
    </h3>

    <address class="account-order-address-card__body">
        <span>{{ $order->billing_full_name }}</span>
        <span>{{ $order->billing_address_1 }}</span>

        @if ($order->billing_address_2)
            <span>{{ $order->billing_address_2 }}</span>
        @endif

        <span>{{ $order->billing_city }}, {!! $order->billing_state_name !!} {{ $order->billing_zip }}</span>
        <span>{{ $order->billing_country_name }}</span>
    </address>
</div>
