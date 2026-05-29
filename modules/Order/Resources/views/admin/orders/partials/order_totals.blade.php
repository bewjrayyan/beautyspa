<div class="order-show__card order-show__card--totals">
    <div class="order-show__card-head">
        <h5><i class="fa fa-calculator" aria-hidden="true"></i> {{ trans('order::orders.order_summary') }}</h5>
    </div>

    <table class="order-show__totals-table">
        <tbody>
            <tr>
                <td>{{ trans('order::orders.subtotal') }}</td>
                <td>{{ $order->sub_total->format() }}</td>
            </tr>

            @if ($order->hasShippingMethod())
                <tr>
                    <td>{{ $order->shipping_method }}</td>
                    <td>{{ $order->shipping_cost->format() }}</td>
                </tr>
            @endif

            @foreach ($order->taxes as $tax)
                <tr>
                    <td>{{ $tax->name }}</td>
                    <td>{{ $tax->order_tax->amount->format() }}</td>
                </tr>
            @endforeach

            @if ($order->hasCoupon())
                <tr class="order-show__totals-discount">
                    <td>
                        {{ trans('order::orders.coupon') }}
                        <span class="order-show__coupon-code">{{ $order->coupon->code }}</span>
                    </td>
                    <td>&#8211;{{ $order->discount->format() }}</td>
                </tr>
            @endif

            @if (app('modules')->isEnabled('Loyalty') && $order->loyalty_points_redeemed > 0)
                <tr class="order-show__totals-discount">
                    <td>
                        {{ trans('loyalty::orders.points_redeemed') }}
                        <small>({{ number_format($order->loyalty_points_redeemed) }} pts)</small>
                    </td>
                    <td>&#8211;{{ \Modules\Support\Money::inDefaultCurrency($order->loyalty_discount_amount)->format() }}</td>
                </tr>
            @endif

            @if (app('modules')->isEnabled('Loyalty') && $order->loyalty_points_earned > 0)
                <tr>
                    <td>{{ trans('loyalty::orders.points_earned') }}</td>
                    <td>{{ number_format($order->loyalty_points_earned) }} pts</td>
                </tr>
            @endif

        </tbody>
    </table>

    <div class="order-show__totals-footer">
        <span>{{ trans('order::orders.total') }}</span>
        <strong>{{ $order->total->format() }}</strong>
    </div>
</div>
