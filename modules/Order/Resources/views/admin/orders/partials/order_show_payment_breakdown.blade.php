@php
    $variant = $variant ?? 'sidebar';
    $showTitle = $showTitle ?? false;
@endphp

<div @class([
    'order-show__payment-breakdown',
    'order-show__payment-breakdown--inline' => $variant === 'inline',
])>
    @if ($variant !== 'inline')
        @if ($showTitle)
            <h6 class="order-show__payment-breakdown-title">{{ trans('order::orders.payment_summary') }}</h6>
        @endif
    @endif

    <div class="order-show__payment-breakdown-inner">
        @if ($variant === 'inline')
            <div class="order-show__items-footer">
                @if (app('modules')->isEnabled('Loyalty') && ! empty($orderStampData))
                    @include('loyalty::admin.orders.partials.stamp_information', array_merge($orderStampData, ['variant' => 'inline']))
                @endif

                <div class="order-show__totals-footer order-show__totals-footer--inline">
                    <span class="order-show__totals-footer-label">{{ trans('order::orders.total') }}</span>
                    <strong class="order-show__totals-footer-value">{{ $order->total->format() }}</strong>
                </div>
            </div>
        @else
            <table class="order-show__totals-table">
                <tbody>
                    @include('order::partials.pricing_breakdown', [
                        'order' => $order,
                        'style' => 'admin',
                        'loyaltyPointsEarnedOverride' => $orderRewardData['points_earned'] ?? null,
                    ])

                    @include('order::admin.orders.partials.order_summary_order_information')
                </tbody>
            </table>

            <div class="order-show__totals-footer">
                <span>{{ trans('order::orders.total') }}</span>
                <strong>{{ $order->total->format() }}</strong>
            </div>
        @endif
    </div>
</div>
