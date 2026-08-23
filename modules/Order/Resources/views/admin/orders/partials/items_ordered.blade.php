<section id="order-items" class="order-show__section">
    <div class="order-show__card order-show__card--flush">
        <div class="order-show__card-head order-show__card-head--table">
            <div>
                <span class="order-show__card-eyebrow">{{ trans('order::orders.commerce') }}</span>
                <h3><i class="fa fa-shopping-bag" aria-hidden="true"></i> {{ trans('order::orders.items_ordered') }}</h3>
            </div>
            <span class="order-show__card-count">{{ trans_choice('order::orders.items_count', $order->products->count(), ['count' => $order->products->count()]) }}</span>
        </div>
        <div class="table-responsive">
            <table class="table order-show__items-table">
                <thead>
                    <tr>
                        <th>{{ trans('order::orders.product') }}</th>
                        <th class="text-right">{{ trans('order::orders.unit_price') }}</th>
                        <th class="text-center">{{ trans('order::orders.quantity') }}</th>
                        <th class="text-right">{{ trans('order::orders.line_total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->products as $product)
                        @php
                            $discountPricing = $orderProductDiscounts[$product->getKey()] ?? null;
                            $originalLineAmount = $discountPricing
                                ? $discountPricing['original_line_total']->amount()
                                : 0;
                            $savingsPercent = $discountPricing && $originalLineAmount > 0
                                ? min(100, max(1, (int) round(
                                    ($discountPricing['savings']->amount() / $originalLineAmount) * 100
                                )))
                                : null;
                        @endphp
                        <tr>
                            <td class="order-show__product-cell">
                                @if ($product->trashed())
                                    @php
                                        $image = ($product->variant && $product->variant->base_image->id) ? $product->variant->base_image : $product->base_image;
                                    @endphp
                                    @if ($image && $image->id)
                                        <img src="{{ $image->path }}" alt="{{ $product->name }}" class="order-show__product-thumb">
                                    @endif
                                    <span class="order-show__product-name">{{ $product->name }}</span>
                                    <span class="label label-default">{{ trans('order::orders.product_removed') }}</span>
                                @else
                                    @php
                                        $image = ($product->variant && $product->variant->base_image->id) ? $product->variant->base_image : $product->base_image;
                                    @endphp
                                    @if ($image && $image->id)
                                        <img src="{{ $image->path }}" alt="{{ $product->name }}" class="order-show__product-thumb">
                                    @endif
                                    <a href="{{ route('admin.products.edit', $product->product->id) }}" class="order-show__product-name">
                                        {{ $product->name }}
                                    </a>
                                @endif

                                @if ($product->hasAnyVariation() || $product->hasAnyOption())
                                    <div class="order-show__product-meta">
                                        @if ($product->hasAnyVariation())
                                            @foreach ($product->variations as $variation)
                                                <span>{{ $variation->name }}: {{ $variation->values()->first()?->label }}{{ $loop->last ? '' : ', ' }}</span>
                                            @endforeach
                                        @endif
                                        @if ($product->hasAnyOption())
                                            @foreach ($product->options as $option)
                                                <span>
                                                    {{ $option->name }}:
                                                    @if ($option->option->isFieldType())
                                                        {{ $option->value }}
                                                    @else
                                                        {{ $option->values->implode('label', ', ') }}
                                                    @endif
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="text-right">
                                @if ($discountPricing)
                                    <div class="order-show__price-stack">
                                        <del
                                            class="order-show__price-original"
                                            aria-label="{{ trans('order::orders.price_before_discount') }}"
                                        >{{ $discountPricing['original_unit_price']->format() }}</del>
                                        <strong
                                            class="order-show__price-paid"
                                            aria-label="{{ trans('order::orders.price_after_discount') }}"
                                        >{{ $discountPricing['discounted_unit_price']->format() }}</strong>
                                        <span class="order-show__save-badge">
                                            {{ trans('order::orders.save_percent', ['percent' => $savingsPercent]) }}
                                        </span>
                                    </div>
                                @else
                                    <span class="order-show__price-paid">{{ $product->unit_price->format() }}</span>
                                @endif
                            </td>
                            <td class="text-center"><span class="order-show__qty">{{ $product->qty }}</span></td>
                            <td class="text-right">
                                @if ($discountPricing)
                                    <div class="order-show__price-stack order-show__price-stack--line-total">
                                        <del
                                            class="order-show__price-original"
                                            aria-label="{{ trans('order::orders.line_total_before_discount') }}"
                                        >{{ $discountPricing['original_line_total']->format() }}</del>
                                        <strong
                                            class="order-show__price-paid"
                                            aria-label="{{ trans('order::orders.line_total_after_discount') }}"
                                        >{{ $discountPricing['discounted_line_total']->format() }}</strong>
                                    </div>
                                @else
                                    <strong>{{ $product->line_total->format() }}</strong>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('order::admin.orders.partials.order_show_payment_breakdown', [
            'order' => $order,
            'variant' => 'inline',
            'showTitle' => true,
        ])
    </div>
</section>
