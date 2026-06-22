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

                    @if ($product->hasAnyOption())
                        <ul class="list-inline account-order-item-card__options">
                            @foreach ($product->options as $option)
                                @if ($option->isFieldType())
                                    <li>
                                        <label>{{ $option->name }}:</label> {{ $option->value }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="account-order-item-card__pricing">
                @include('storefront::public.account.orders.show.item_pricing', [
                    'order' => $order,
                    'product' => $product,
                ])
            </div>
        </div>
    @endforeach
</div>
