<div class="account-order-totals order-details-bottom">
    <ul class="list-inline order-summary-list">
        @include('storefront::public.account.orders.show.order_summary_lines', ['order' => $order])
    </ul>

    <div class="order-summary-total">
        <label>{{ trans('storefront::account.view_order.total') }}</label>

        <span class="total-price">
            {{ $formatOrderMoney($order->total) }}
        </span>
    </div>
</div>
