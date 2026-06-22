<div class="account-order-items order-details-middle">
    @include('storefront::public.account.orders.show.items_cards')

    <div class="table-responsive account-order-items__table-wrap d-none d-lg-block">
        <table class="table table-borderless order-details-table account-order-items__table">
            <thead>
                <tr>
                    <th>{{ trans('storefront::account.product_name') }}</th>
                    <th>{{ trans('storefront::account.view_order.unit_price') }}</th>
                    <th>{{ trans('storefront::account.view_order.quantity') }}</th>
                    <th>{{ trans('storefront::account.view_order.line_total') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($order->products as $product)
                    <tr>
                        <td>
                            <a href="{{ $product->url() }}" class="product-name">
                                {{ $product->name }}
                            </a>

                            @if ($product->hasAnyVariation())
                                <ul class="list-inline product-options">
                                    @foreach ($product->variations as $variation)
                                        <li>
                                            <label>{{ $variation->name }}:</label>
                                            {{ $variation->values()->first()?->label }}{{ $loop->last ? "" : "," }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($product->hasAnyOption())
                                <ul class="list-inline product-options">
                                    @foreach ($product->options as $option)
                                        <li>
                                            @if ($option->isFieldType())
                                                <label>{{ $option->name }}:</label> {{ $option->value }}
                                            @else
                                                <label>{{ $option->name }}:</label> {{ $option->values->implode('label', ', ') }}
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>

                        <td>
                            <div class="account-order-item-pricing account-order-item-pricing--table">
                                @include('storefront::public.account.orders.show.item_pricing', [
                                    'order' => $order,
                                    'product' => $product,
                                    'compact' => true,
                                ])
                            </div>
                        </td>

                        <td>
                            <label>{{ trans('storefront::account.view_order.quantity') }}</label>

                            <span class="quantity">
                                {{ $product->qty }}
                            </span>
                        </td>

                        <td>
                            <label>{{ trans('storefront::account.view_order.line_total') }}</label>

                            <span class="product-price">
                                {{ $product->line_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
