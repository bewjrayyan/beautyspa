<div class="account-order-items order-details-middle">
    @include('storefront::public.account.orders.show.items_cards')

    <div class="table-responsive account-order-items__table-wrap d-none d-lg-block">
        <table class="table table-borderless order-details-table account-order-items__table">
            <thead>
                <tr>
                    <th scope="col">{{ trans('storefront::account.product_name') }}</th>
                    <th scope="col">{{ trans('storefront::account.view_order.unit_price') }}</th>
                    <th scope="col" class="account-order-items__quantity-heading">{{ trans('storefront::account.view_order.quantity') }}</th>
                    <th scope="col" class="account-order-items__total-heading">{{ trans('storefront::account.view_order.line_total') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($order->products as $product)
                    @php
                        $productImage = $product->product?->base_image?->path;
                    @endphp

                    <tr>
                        <td>
                            <div class="account-order-items__product">
                                <a href="{{ $product->url() }}" class="account-order-items__thumb">
                                    <img
                                        src="{{ $productImage ?: asset('build/assets/image-placeholder.png') }}"
                                        alt="{{ $product->name }}"
                                        width="58"
                                        height="58"
                                        loading="lazy"
                                        decoding="async"
                                        @class(['image-placeholder' => ! $productImage])
                                    >
                                </a>

                                <div class="account-order-items__product-info">
                                    <span class="account-order-items__eyebrow">
                                        {{ trans('storefront::account.view_order.item_number', ['number' => str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT)]) }}
                                    </span>

                                    <a href="{{ $product->url() }}" class="product-name">
                                        {{ $product->name }}
                                    </a>

                                    @if ($product->hasAnyVariation() || $product->hasAnyOption())
                                        <ul class="list-inline product-options">
                                            @foreach ($product->variations as $variation)
                                                <li>
                                                    <span>{{ $variation->name }}</span>
                                                    <strong>{{ $variation->values->first()?->label ?? $variation->value }}</strong>
                                                </li>
                                            @endforeach

                                            @foreach ($product->options as $option)
                                                <li>
                                                    <span>{{ $option->name }}</span>
                                                    <strong>{{ $option->isFieldType() ? $option->value : $option->values->implode('label', ', ') }}</strong>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="account-order-item-pricing account-order-item-pricing--table">
                                @include('storefront::public.account.orders.show.item_pricing', [
                                    'order' => $order,
                                    'product' => $product,
                                    'compact' => true,
                                    'tablePriceOnly' => true,
                                ])
                            </div>
                        </td>

                        <td class="account-order-items__quantity-cell">
                            <span class="quantity" aria-label="{{ trans('storefront::account.view_order.quantity') }}">
                                {{ $product->qty }}
                            </span>
                        </td>

                        <td class="account-order-items__total-cell">
                            @php
                                $discountPricing = $orderProductDiscounts[$product->getKey()] ?? null;
                            @endphp

                            @if ($discountPricing)
                                <span class="product-price">
                                    {{ $formatOrderMoney($discountPricing['discounted_line_total']) }}
                                </span>
                            @else
                                <span class="product-price">
                                    {{ $formatOrderMoney($product->line_total) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
