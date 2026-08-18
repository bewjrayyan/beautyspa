@php
    $variant = $variant ?? 'sidebar';
    $showTitle = $showTitle ?? false;
@endphp

<div @class([
    'order-show__payment-breakdown',
    'order-show__payment-breakdown--inline' => $variant === 'inline',
])>
    @if ($showTitle)
        <!-- Existing code before payment title -->
<h6 class="order-show__payment-breakdown-title" style="margin-bottom: 20px;">{{ trans('order::orders.payment_summary') }}</h6>
<!-- Existing code after payment title -->
    @endif

    <div class="order-show__payment-breakdown-inner">
        <table class="order-show__totals-table">
            <tbody>
                @include('order::partials.pricing_breakdown', ['order' => $order, 'style' => 'admin'])
            </tbody>
        </table>

        <div @class([
            'order-show__totals-footer',
            'order-show__totals-footer--inline' => $variant === 'inline',
        ])>
            <span>{{ trans('order::orders.total') }}</span>
            <strong>{{ $order->total->format() }}</strong>
        </div>
    </div>
</div>
