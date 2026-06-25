<div class="order-show__card order-show__card--totals">
    <div class="order-show__card-head">
        <h5><i class="fa fa-calculator" aria-hidden="true"></i> {{ trans('order::orders.order_summary') }}</h5>
    </div>

    @include('order::admin.orders.partials.order_show_payment_breakdown', [
        'order' => $order,
        'variant' => 'sidebar',
    ])
</div>
