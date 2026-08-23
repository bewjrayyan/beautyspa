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
                            <td class="text-right">{{ $product->unit_price->format() }}</td>
                            <td class="text-center"><span class="order-show__qty">{{ $product->qty }}</span></td>
                            <td class="text-right"><strong>{{ $product->line_total->format() }}</strong></td>
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
