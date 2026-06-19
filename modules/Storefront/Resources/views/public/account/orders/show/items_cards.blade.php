<div class="account-order-item-cards d-lg-none">
    @foreach ($order->products as $product)
        <div class="account-order-item-card">
            <div class="account-order-item-card__main">
                <div class="account-order-item-card__icon" aria-hidden="true">
                    <i class="las la-spa"></i>
                </div>

                <div class="account-order-item-card__info">
                    <a href="{{ $product->url() }}" class="account-order-item-card__name">
                        {{ $product->name }}
                    </a>

                    @if ($product->hasAnyVariation())
                        <ul class="list-inline account-order-item-card__options">
                            @foreach ($product->variations as $variation)
                                <li>
                                    <label>{{ $variation->name }}:</label>
                                    {{ $variation->values()->first()?->label }}{{ $loop->last ? '' : ',' }}
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($product->hasAnyOption())
                        <ul class="list-inline account-order-item-card__options">
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
                </div>
            </div>

            <div class="account-order-item-card__pricing">
                <div class="account-order-item-card__row">
                    <span>{{ trans('storefront::account.view_order.unit_price') }}</span>
                    <span>{{ $product->unit_price->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
                </div>

                <div class="account-order-item-card__row">
                    <span>{{ trans('storefront::account.view_order.quantity') }}</span>
                    <span>{{ $product->qty }}</span>
                </div>

                <div class="account-order-item-card__row account-order-item-card__row--total">
                    <span>{{ trans('storefront::account.view_order.line_total') }}</span>
                    <span>{{ $product->line_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
                </div>
            </div>
        </div>
    @endforeach
</div>
