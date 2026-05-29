<div class="order-show__section">
    <h4 class="order-show__section-title">
        <span class="order-show__section-title-text">{{ trans('order::orders.address_information') }}</span>
    </h4>

    <div class="order-show__grid order-show__grid--2">
        <div class="order-show__card order-show__card--address">
            <div class="order-show__card-head">
                <h5><i class="fa fa-credit-card" aria-hidden="true"></i> {{ trans('order::orders.billing_address') }}</h5>
            </div>
            <address class="order-show__address">
                <strong>{{ $order->billing_full_name }}</strong>
                {{ $order->billing_address_1 }}<br>
                @if ($order->billing_address_2)
                    {{ $order->billing_address_2 }}<br>
                @endif
                {{ $order->billing_city }}, {!! $order->billing_state_name !!} {{ $order->billing_zip }}<br>
                {{ $order->billing_country_name }}
            </address>
        </div>

        <div class="order-show__card order-show__card--address">
            <div class="order-show__card-head">
                <h5><i class="fa fa-truck" aria-hidden="true"></i> {{ trans('order::orders.shipping_address') }}</h5>
            </div>
            <address class="order-show__address">
                <strong>{{ $order->shipping_full_name }}</strong>
                {{ $order->shipping_address_1 }}<br>
                @if ($order->shipping_address_2)
                    {{ $order->shipping_address_2 }}<br>
                @endif
                {{ $order->shipping_city }}, {!! $order->shipping_state_name !!} {{ $order->shipping_zip }}<br>
                {{ $order->shipping_country_name }}
            </address>
        </div>
    </div>
</div>
