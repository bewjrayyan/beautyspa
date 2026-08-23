@php
    $formatMoney = $formatOrderMoney
        ?? fn ($amount) => $amount->convert($order->currency, $order->currency_rate)->format($order->currency);
    $pricedOptions = $product->pricedOptionLines();
    $variationCount = $product->variations->count();
    $discountPricing = $orderProductDiscounts[$product->getKey()] ?? null;
@endphp

<div class="account-order-item-pricing">
    @if ($tablePriceOnly ?? false)
        <div class="account-order-item-pricing__table-stack">
            @if ($discountPricing)
                <span class="account-order-item-pricing__comparison">
                    <del
                        class="account-order-item-pricing__original"
                        aria-label="{{ trans('storefront::account.view_order.price_before_discount') }}"
                    >{{ $formatMoney($discountPricing['original_unit_price']) }}</del>
                    <strong
                        class="account-order-item-pricing__discounted"
                        aria-label="{{ trans('storefront::account.view_order.price_after_discount') }}"
                    >{{ $formatMoney($discountPricing['discounted_unit_price']) }}</strong>
                    <small>{{ trans('storefront::account.view_order.after_discount') }}</small>
                </span>
            @else
                <strong class="account-order-item-pricing__amount account-order-item-pricing__amount--primary">
                    {{ $formatMoney($product->hasPricedOptions() ? $product->baseUnitPrice() : $product->unit_price) }}
                </strong>
            @endif

            @unless ($discountPricing)
                @foreach ($pricedOptions as $optionLine)
                    <span class="account-order-item-pricing__table-addon">
                        +{{ $formatMoney(\Modules\Support\Money::inDefaultCurrency($optionLine['price'])) }}
                        <small>{{ $optionLine['name'] }}</small>
                    </span>
                @endforeach
            @endunless
        </div>
    @elseif ($product->hasAnyVariation())
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
                    @if ($discountPricing)
                        <span class="account-order-item-pricing__comparison account-order-item-pricing__comparison--inline">
                            <del>{{ $formatMoney($discountPricing['original_unit_price']) }}</del>
                            <strong>{{ $formatMoney($discountPricing['discounted_unit_price']) }}</strong>
                        </span>
                    @else
                        <span class="account-order-item-pricing__amount">{{ $formatMoney($product->unit_price) }}</span>
                    @endif
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
            @if ($discountPricing)
                <span class="account-order-item-pricing__amount">{{ $formatMoney($discountPricing['discounted_line_total']) }}</span>
            @else
                <span class="account-order-item-pricing__amount">{{ $formatMoney($product->line_total) }}</span>
            @endif
        </div>
    @endif
</div>
