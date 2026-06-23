@php
    $style = $style ?? 'invoice';
    $formatMoney = fn ($amount) => $amount->convert($order->currency, $order->currency_rate)->format($order->currency);

    $pricingLines = [
        [
            'label' => trans('order::print.subtotal'),
            'value' => $formatMoney($order->sub_total),
        ],
    ];

    if ($order->hasPhysicalProducts() && $order->hasShippingMethod()) {
        $pricingLines[] = [
            'label' => $order->shipping_method,
            'value' => $formatMoney($order->shipping_cost),
        ];
    }

    foreach ($order->taxes as $tax) {
        $pricingLines[] = [
            'label' => $tax->name,
            'value' => $formatMoney($tax->order_tax->amount),
        ];
    }

    if ($order->hasCoupon()) {
        $pricingLines[] = [
            'label' => trans('order::print.coupon').' ('.$order->coupon->code.')',
            'value' => '&minus;'.$formatMoney($order->discount),
            'discount' => true,
        ];
    }

    if (app('modules')->isEnabled('Loyalty') && $order->hasLoyaltyRedemption()) {
        $pricingLines[] = [
            'label' => trans('loyalty::orders.points_redeemed').' ('.number_format((int) $order->loyalty_points_redeemed).' '.trans('order::orders.loyalty_pts').')',
            'value' => '&minus;'.$formatMoney($order->loyaltyDiscountAmount()),
            'discount' => true,
        ];
    }

    if ($order->hasPaymentProcessingFee()) {
        $pricingLines[] = [
            'label' => trans('order::print.payment_processing_fee'),
            'value' => $formatMoney($order->paymentProcessingFee()),
        ];
    }

    if (app('modules')->isEnabled('Loyalty') && (int) ($order->loyalty_points_earned ?? 0) > 0) {
        $pricingLines[] = [
            'label' => trans('loyalty::orders.points_earned'),
            'value' => number_format((int) $order->loyalty_points_earned).' '.trans('order::orders.loyalty_pts'),
            'meta' => true,
        ];
    }
@endphp

@foreach ($pricingLines as $line)
    @switch($style)
        @case('payment-receipt')
            <div @class([
                'payment-receipt__summary-row',
                'payment-receipt__summary-row--meta' => ! empty($line['meta']),
            ])>
                <dt>{{ $line['label'] }}</dt>
                <dd>{!! $line['value'] !!}</dd>
            </div>
            @break

        @case('order-receipt')
            <div @class([
                'order-receipt__total-row',
                'order-receipt__total-row--discount' => ! empty($line['discount']),
                'order-receipt__total-row--meta' => ! empty($line['meta']),
            ])>
                <dt>{{ $line['label'] }}</dt>
                <dd>{!! $line['value'] !!}</dd>
            </div>
            @break

        @default
            <dl @class([
                'order-invoice__row',
                'order-invoice__row--discount' => ! empty($line['discount']),
                'order-invoice__row--meta' => ! empty($line['meta']),
            ])>
                <dt>{{ $line['label'] }}</dt>
                <dd>{!! $line['value'] !!}</dd>
            </dl>
    @endswitch
@endforeach
