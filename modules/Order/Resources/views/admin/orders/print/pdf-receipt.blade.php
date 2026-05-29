<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ trans('order::print.receipt') }} #{{ $order->id }}</title>
    <style>
        body { font-family: helvetica, sans-serif; font-size: 12px; color: #1a202c; margin: 24px; }
        h1 { font-size: 16px; margin: 0 0 4px; text-align: center; }
        h2 { font-size: 13px; margin: 12px 0 6px; text-align: center; color: #0068e1; }
        .muted { color: #64748b; font-size: 11px; text-align: center; }
        .logo { display: block; max-height: 48px; max-width: 160px; margin: 0 auto 8px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f8fafc; font-size: 11px; }
        .totals td { border: none; padding: 4px 0; }
        .totals .label { color: #64748b; }
        .totals .amount { text-align: right; font-weight: bold; }
        .totals .grand td { border-top: 2px dashed #e2e8f0; padding-top: 8px; font-size: 14px; }
    </style>
</head>
<body>
    @if ($logo ?? null)
        <img src="{{ $logo }}" alt="" class="logo">
    @endif
    <h1>{{ setting('store_name') }}</h1>
    <h2>{{ trans('order::print.receipt') }}</h2>
    <p class="muted">#{{ $order->id }} · {{ $order->created_at->format('d M Y, h:i A') }}</p>

    <p><strong>{{ trans('order::print.customer') }}:</strong> {{ $order->customer_full_name }}<br>
    @if ($order->customer_phone)<span class="muted">{{ $order->customer_phone }}</span>@endif</p>

    <table>
        <thead>
            <tr>
                <th>{{ trans('order::print.description') }}</th>
                <th>{{ trans('order::print.quantity') }}</th>
                <th>{{ trans('order::print.line_total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->products as $product)
                <tr>
                    <td>
                        {{ $product->name }}<br>
                        <span class="muted">{{ $product->unit_price->convert($order->currency, $order->currency_rate)->format($order->currency) }}</span>
                    </td>
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
        <strong>{{ trans('order::print.payment_status') }}:</strong> {{ $order->paymentStatusLabel() }}
    </p>

    <p class="muted" style="text-align:center;">{{ trans('order::print.thank_you') }}</p>
</body>
</html>
