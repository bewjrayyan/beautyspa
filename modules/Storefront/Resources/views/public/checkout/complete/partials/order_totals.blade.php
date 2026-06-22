@php
    $formatMoney = fn ($amount) => $amount->convert($order->currency, $order->currency_rate)->format($order->currency);
@endphp

<div class="order-complete-totals">
    <ul class="order-complete-summary-list">
        @include('storefront::public.account.orders.show.order_summary_lines', ['order' => $order])
    </ul>

    <div class="order-complete-total-row order-complete-total-row--grand">
        <span>{{ trans('storefront::order_complete.order_total') }}</span>
        <span>{{ $formatMoney($order->total) }}</span>
    </div>

    <div class="order-complete-payment-meta">
        <div class="order-complete-total-row">
            <span>{{ trans('storefront::order_complete.payment_method') }}</span>
            <span>{{ $order->payment_method }}</span>
        </div>

        <div class="order-complete-total-row">
            <span>{{ trans('storefront::account.view_order.payment_status') }}</span>
            <span class="badge {{ payment_status_badge_class($order->payment_status) }}">
                {{ $order->paymentStatusLabel() }}
            </span>
        </div>

        @if ($order->transaction?->transaction_id)
            <div class="order-complete-total-row order-complete-total-row--transaction">
                <span>{{ trans('storefront::account.view_order.transaction_id') }}</span>
                <span class="order-complete-transaction-id">{{ $order->transaction->transaction_id }}</span>
            </div>
        @endif
    </div>
</div>
