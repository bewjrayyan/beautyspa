@php
    $formatMoney = fn ($amount) => $amount->convert($order->currency, $order->currency_rate)->format($order->currency);
@endphp

<ul class="order-complete-items">
    @foreach ($order->products as $line)
        <li class="order-complete-item">
            <div class="order-complete-item-body">
                <span class="order-complete-item-name">{{ $line->name }}</span>

                <div class="order-complete-item-pricing">
                    @include('storefront::public.account.orders.show.item_pricing', [
                        'order' => $order,
                        'product' => $line,
                        'compact' => true,
                    ])
                </div>
            </div>

            <div class="order-complete-item-line-total">
                <span class="order-complete-item-line-total-label">
                    {{ trans('storefront::account.view_order.line_total') }}
                </span>
                <span class="order-complete-item-line-total-value">
                    {{ $formatMoney($line->line_total) }}
                </span>
            </div>
        </li>
    @endforeach
</ul>
