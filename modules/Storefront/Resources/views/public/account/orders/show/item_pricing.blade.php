@php
    $formatMoney = fn ($amount) => $amount->convert($order->currency, $order->currency_rate)->format($order->currency);
    $pricedOptions = $product->pricedOptionLines();
    $variationCount = $product->variations->count();
@endphp

<div class="account-order-item-pricing">
    @if ($product->hasAnyVariation())
        @foreach ($product->variations as $variation)
            @php
                $valueLabel = $variation->values->first()?->label ?? $variation->value ?? '';
                $showVariantPrice = $variationCount === 1 && $pricedOptions->isEmpty();
            @endphp

            <div class="account-order-item-pricing__row account-order-item-pricing__row--selection">
                <p class="account-order-item-pricing__selection">
                    <span class="account-order-item-pricing__name">{{ $variation->name }}@if (filled($valueLabel)):@endif</span>

                    @if (filled($valueLabel))
                        <span class="account-order-item-pricing__value">{{ $valueLabel }}</span>
                    @endif
                </p>

                @if ($showVariantPrice)
                    <span class="account-order-item-pricing__amount">{{ $formatMoney($product->unit_price) }}</span>
                @endif
            </div>
        @endforeach
    @endif

    @if (! $product->hasAnyVariation() || $product->hasPricedOptions())
        <div class="account-order-item-pricing__row">
            <span class="account-order-item-pricing__name">
                {{ trans('storefront::account.view_order.unit_price') }}
            </span>

            <span class="account-order-item-pricing__amount">
                {{ $formatMoney($product->hasPricedOptions() ? $product->baseUnitPrice() : $product->unit_price) }}
            </span>
        </div>
    @endif

    @foreach ($pricedOptions as $optionLine)
        <div class="account-order-item-pricing__row account-order-item-pricing__row--selection account-order-item-pricing__row--option">
            <p class="account-order-item-pricing__selection">
                <span class="account-order-item-pricing__name">{{ $optionLine['name'] }}@if (filled($optionLine['value'])):@endif</span>

                @if (filled($optionLine['value']))
                    <span class="account-order-item-pricing__value">{{ $optionLine['value'] }}</span>
                @endif
            </p>

            <span class="account-order-item-pricing__amount account-order-item-pricing__amount--addon">
                +{{ $formatMoney(\Modules\Support\Money::inDefaultCurrency($optionLine['price'])) }}
            </span>
        </div>
    @endforeach

    @if (! ($compact ?? false))
        <div class="account-order-item-pricing__row">
            <span class="account-order-item-pricing__name">{{ trans('storefront::account.view_order.quantity') }}</span>
            <span class="account-order-item-pricing__amount">{{ $product->qty }}</span>
        </div>

        <div class="account-order-item-pricing__row account-order-item-pricing__row--total">
            <span class="account-order-item-pricing__name">{{ trans('storefront::account.view_order.line_total') }}</span>
            <span class="account-order-item-pricing__amount">{{ $formatMoney($product->line_total) }}</span>
        </div>
    @endif
</div>
