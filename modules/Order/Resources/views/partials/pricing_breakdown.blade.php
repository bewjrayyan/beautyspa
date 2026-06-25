@php
    $style = $style ?? 'invoice';
    $pricingLines = app(\Modules\Order\Services\OrderPricingBreakdown::class)->lines($order);
@endphp

@foreach ($pricingLines as $line)
    @switch($style)
        @case('payment-receipt')
            <div @class([
                'payment-receipt__summary-row',
                'payment-receipt__summary-row--meta' => ! empty($line['meta']),
            ])>
                <dt>{{ $line['label'] }}</dt>
                <dd>{!! $line['value'] !!}</dd>
            </div>
            @break

        @case('order-receipt')
            <div @class([
                'order-receipt__total-row',
                'order-receipt__total-row--discount' => ! empty($line['discount']),
                'order-receipt__total-row--meta' => ! empty($line['meta']),
            ])>
                <dt>{{ $line['label'] }}</dt>
                <dd>{!! $line['value'] !!}</dd>
            </div>
            @break

        @case('admin')
            <tr @class([
                'order-show__totals-discount' => ! empty($line['discount']),
                'order-show__totals-meta' => ! empty($line['meta']),
            ])>
                <td>{!! $line['label'] !!}</td>
                <td>{!! $line['value'] !!}</td>
            </tr>
            @break

        @default
            <dl @class([
                'order-invoice__row',
                'order-invoice__row--discount' => ! empty($line['discount']),
                'order-invoice__row--meta' => ! empty($line['meta']),
            ])>
                <dt>{{ $line['label'] }}</dt>
                <dd>{!! $line['value'] !!}</dd>
            </dl>
    @endswitch
@endforeach
