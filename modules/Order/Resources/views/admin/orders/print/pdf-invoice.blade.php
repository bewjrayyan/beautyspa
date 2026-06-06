<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ trans('order::print.invoice') }} #{{ $order->id }}</title>
    <style>
        body { font-family: helvetica, sans-serif; font-size: 12px; color: #1a202c; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #0068e1; }
        h2 { font-size: 13px; margin: 16px 0 8px; color: #0068e1; }
        .muted { color: #64748b; font-size: 11px; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #0068e1; padding-bottom: 12px; }
        .logo { max-height: 48px; max-width: 180px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0 16px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 11px; text-transform: uppercase; }
        .totals td { border: none; padding: 4px 8px; }
        .totals .label { color: #64748b; width: 70%; }
        .totals .amount { text-align: right; font-weight: bold; width: 30%; }
        .totals .grand td { border-top: 2px solid #0068e1; font-size: 14px; padding-top: 8px; }
        .item-meta { display: block; font-size: 10px; color: #64748b; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="header">
        @if ($logo ?? null)
            <img src="{{ $logo }}" alt="" class="logo">
        @endif
        <h1>{{ trans('order::print.invoice') }}</h1>
        <p class="muted">{{ setting('store_name') }}</p>
        <p class="muted">
            #{{ $order->id }} · {{ $order->created_at->format('d M Y, h:i A') }}
        </p>
    </div>

    <table>
        <tr>
            <td style="width:50%;">
                <strong>{{ trans('order::print.billing_address') }}</strong><br>
                {{ $order->billing_full_name }}<br>
                {{ $order->billing_address_1 }}<br>
                @if ($order->billing_address_2){{ $order->billing_address_2 }}<br>@endif
                {{ $order->billing_city }}, {!! $order->billing_state_name !!} {{ $order->billing_zip }}<br>
                {{ $order->billing_country_name }}
            </td>
            <td style="width:50%;">
                <strong>{{ trans('order::print.shipping_address') }}</strong><br>
                {{ $order->shipping_full_name }}<br>
                {{ $order->shipping_address_1 }}<br>
                @if ($order->shipping_address_2){{ $order->shipping_address_2 }}<br>@endif
                {{ $order->shipping_city }}, {!! $order->shipping_state_name !!} {{ $order->shipping_zip }}<br>
                {{ $order->shipping_country_name }}
            </td>
        </tr>
    </table>

    <h2>{{ trans('order::print.items') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ trans('order::print.description') }}</th>
                <th>{{ trans('order::print.unit_price') }}</th>
                <th>{{ trans('order::print.quantity') }}</th>
                <th>{{ trans('order::print.line_total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->products as $product)
                <tr>
                    <td>
                        {{ $product->name }}
                        @if ($product->hasAnyVariation())
                            <span class="item-meta">
                                @foreach ($product->variations as $variation)
                                    {{ $variation->name }}: {{ $variation->values()->first()?->label }}{{ $loop->last ? '' : ', ' }}
                                @endforeach
                            </span>
                        @endif
                    </td>
                    <td>{{ $product->unit_price->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                    <td>{{ $product->qty }}</td>
                    <td>{{ $product->line_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">{{ trans('order::print.subtotal') }}</td>
            <td class="amount">{{ $order->sub_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
        </tr>
        @if ($order->hasShippingMethod())
            <tr>
                <td class="label">{{ $order->shipping_method }}</td>
                <td class="amount">{{ $order->shipping_cost->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
            </tr>
        @endif
        @if ($order->hasCoupon())
            <tr>
                <td class="label">{{ trans('order::print.discount') }} ({{ $order->coupon->code }})</td>
                <td class="amount">&minus;{{ $order->discount->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
            </tr>
        @endif
        @foreach ($order->taxes as $tax)
            <tr>
                <td class="label">{{ $tax->name }}</td>
                <td class="amount">{{ $tax->order_tax->amount->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
            </tr>
        @endforeach
        <tr class="grand">
            <td class="label">{{ trans('order::print.total') }}</td>
            <td class="amount">{{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
        </tr>
    </table>

    <p>
        <strong>{{ trans('order::print.payment_method') }}:</strong> {{ $order->payment_method }}<br>
        <strong>{{ trans('order::print.payment_status') }}:</strong> {{ $order->paymentStatusLabel() }}<br>
        <strong>{{ trans('order::print.order_status') }}:</strong> {{ $order->status() }}
    </p>

    @if ($order->beautician || $order->spaBranch || $order->appointment_date || $order->appointment_time)
        <p>
            <strong>{{ trans('order::print.appointment') }}</strong><br>
            @if ($order->spaBranch){{ trans('order::orders.spa_branch') }}: {{ $order->spaBranch->name }}<br>@endif
            @if ($order->beautician){{ trans('order::print.beautician') }}: {{ $order->beautician->name }}<br>@endif
            @if ($order->appointment_date){{ trans('order::print.appointment_date') }}: {{ $order->appointment_date->format('d M Y') }}<br>@endif
            @if ($order->appointment_time){{ trans('order::print.appointment_time') }}: {{ $order->appointment_time }}@endif
        </p>
    @endif

    <p class="muted">{{ trans('order::print.thank_you') }}</p>
</body>
</html>
