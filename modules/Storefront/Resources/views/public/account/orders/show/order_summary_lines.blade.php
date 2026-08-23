@php
    $formatMoney = $formatOrderMoney
        ?? fn ($amount) => $amount->convert($order->currency, $order->currency_rate)->format($order->currency);
    $pricingLines = app(\Modules\Order\Services\OrderPricingBreakdown::class)
        ->lines($order, true, $formatMoney);
@endphp

@foreach ($pricingLines as $line)
    <li @class([
        'account-order-summary-line--discount' => ! empty($line['discount']),
        'account-order-summary-line--meta' => ! empty($line['meta']),
    ])>
        <label>{{ $line['label'] }}</label>
        <span>{{ $line['value'] }}</span>
    </li>
@endforeach
