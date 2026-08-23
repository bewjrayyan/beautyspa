<div class="account-order-item-cards d-lg-none">
    @foreach ($order->products as $product)
        @php
            $productImage = $product->product?->base_image?->path;
        @endphp

        <div class="account-order-item-card">
            <div class="account-order-item-card__main">
                <a href="{{ $product->url() }}" class="account-order-item-card__thumb">
                    <img
                        src="{{ $productImage ?: asset('build/assets/image-placeholder.png') }}"
                        alt="{{ $product->name }}"
                        width="52"
                        height="52"
                        loading="lazy"
                        decoding="async"
                        @class(['image-placeholder' => ! $productImage])
                    >
                </a>

                <div class="account-order-item-card__info">
                    <span class="account-order-item-card__eyebrow">
                        {{ trans('storefront::account.view_order.item_number', ['number' => str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT)]) }}
                    </span>

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
