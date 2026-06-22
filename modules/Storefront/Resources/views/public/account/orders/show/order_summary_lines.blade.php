@php
    $formatMoney = fn ($amount) => $amount->convert($order->currency, $order->currency_rate)->format($order->currency);
@endphp

<li>
    <label>{{ trans('storefront::account.view_order.subtotal') }}</label>
    <span>{{ $formatMoney($order->sub_total) }}</span>
</li>

@if ($order->hasPhysicalProducts() && $order->hasShippingMethod())
    <li>
        <label>{{ $order->shipping_method }}</label>
        <span>{{ $formatMoney($order->shipping_cost) }}</span>
    </li>
@endif

@foreach ($order->taxes as $tax)
    <li>
        <label>{{ $tax->name }}</label>
        <span>{{ $formatMoney($tax->order_tax->amount) }}</span>
    </li>
@endforeach

@if ($order->hasCoupon())
    <li class="account-order-summary-line--discount">
        <label>
            {{ trans('storefront::account.view_order.coupon') }}
            <span class="coupon-code">({{ $order->coupon->code }})</span>
        </label>
        <span>-{{ $formatMoney($order->discount) }}</span>
    </li>
@endif

@if (app('modules')->isEnabled('Loyalty') && $order->hasLoyaltyRedemption())
    <li class="account-order-summary-line--discount">
        <label>
            {{ trans('loyalty::orders.points_redeemed') }}
            <span class="coupon-code">({{ number_format((int) $order->loyalty_points_redeemed) }} {{ trans('storefront::account.view_order.loyalty_pts') }})</span>
        </label>
        <span>-{{ $formatMoney($order->loyaltyDiscountAmount()) }}</span>
    </li>
@endif

@if ($order->hasPaymentProcessingFee())
    <li>
        <label>{{ trans('storefront::account.view_order.payment_processing_fee') }}</label>
        <span>{{ $formatMoney($order->paymentProcessingFee()) }}</span>
    </li>
@endif

@if (app('modules')->isEnabled('Loyalty') && (int) ($order->loyalty_points_earned ?? 0) > 0)
    <li class="account-order-summary-line--meta">
        <label>{{ trans('loyalty::orders.points_earned') }}</label>
        <span>{{ number_format((int) $order->loyalty_points_earned) }} {{ trans('storefront::account.view_order.loyalty_pts') }}</span>
    </li>
@endif
