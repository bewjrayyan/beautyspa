<div class="order-show__section">
    <h4 class="order-show__section-title">
        <span class="order-show__section-title-text">{{ trans('order::orders.items_ordered') }}</span>
        <span class="order-show__section-count">{{ $order->products->count() }}</span>
    </h4>

    <div class="order-show__card order-show__card--flush">
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
                                    <span class="order-show__product-name">{{ $product->name }}</span>
                                    <span class="label label-default">{{ trans('order::orders.product_removed') }}</span>
                                @else
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
    </div>
</div>
