<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('order::print.receipt') }} #{{ $order->id }}</title>
    @if ($forPdf ?? false)
        @if (! empty($printCssUrl))
            <link rel="stylesheet" href="{{ $printCssUrl }}">
        @endif
    @else
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        @vite(['modules/Order/Resources/assets/admin/sass/receipt.scss'])
    @endif
    <style>
        :root {
            --color-primary: {{ function_exists('storefront_theme_color') ? storefront_theme_color() : '#0068e1' }};
        }
    </style>
</head>
<body class="{{ is_rtl() ? 'rtl' : 'ltr' }}">
    <article class="order-receipt">
        <header class="order-receipt__header">
            @if ($logo ?? null)
                <img src="{{ $logo }}" alt="{{ setting('store_name') }}" class="order-receipt__logo">
            @endif

            <h1 class="order-receipt__store">{{ setting('store_name') }}</h1>

            @if (setting('store_address_1') || setting('store_address_2'))
                <p class="order-receipt__store-meta">
                    {{ collect([setting('store_address_1'), setting('store_address_2')])->filter()->implode(', ') }}
                </p>
            @endif

            @if (setting('store_phone') || setting('store_email'))
                <p class="order-receipt__store-meta">
                    {{ collect([setting('store_phone'), setting('store_email')])->filter()->implode(' · ') }}
                </p>
            @endif
        </header>

        <div class="order-receipt__title">{{ trans('order::print.receipt') }}</div>

        <dl class="order-receipt__meta">
            <div class="order-receipt__meta-row">
                <dt>{{ trans('order::print.receipt_no') }}</dt>
                <dd>#{{ $order->id }}</dd>
            </div>
            <div class="order-receipt__meta-row">
                <dt>{{ trans('order::print.date') }}</dt>
                <dd>{{ $order->created_at->format('d M Y, h:i A') }}</dd>
            </div>
        </dl>

        <div class="order-receipt__divider"></div>

        <section class="order-receipt__customer">
            <p class="order-receipt__label">{{ trans('order::print.customer') }}</p>
            <p class="order-receipt__value">{{ $order->customer_full_name }}</p>
            @if ($order->customer_phone)
                <p class="order-receipt__value order-receipt__value--muted">{{ $order->customer_phone }}</p>
            @endif
        </section>

        <div class="order-receipt__divider"></div>

        <table class="order-receipt__items">
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
                            <span class="order-receipt__item-name">{{ $product->name }}</span>
                            <span class="order-receipt__item-price">
                                {{ $product->unit_price->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                            </span>
                        </td>
                        <td>{{ $product->qty }}</td>
                        <td>
                            {{ $product->line_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="order-receipt__divider"></div>

        <dl class="order-receipt__totals">
            @include('order::partials.pricing_breakdown', ['order' => $order, 'style' => 'order-receipt'])

            <div class="order-receipt__total-row order-receipt__total-row--grand">
                <dt>{{ trans('order::print.total') }}</dt>
                <dd>{{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</dd>
            </div>
        </dl>

        <div class="order-receipt__divider"></div>

        <dl class="order-receipt__payment">
            <div class="order-receipt__meta-row">
                <dt>{{ trans('order::print.payment_method') }}</dt>
                <dd>{{ $order->payment_method }}</dd>
            </div>
            <div class="order-receipt__meta-row">
                <dt>{{ trans('order::print.payment_status') }}</dt>
                <dd>{{ $order->paymentStatusLabel() }}</dd>
            </div>
            @if ($order->transaction?->transaction_id)
                <div class="order-receipt__meta-row">
                    <dt>{{ trans('order::print.transaction_id') }}</dt>
                    <dd class="order-receipt__mono">{{ $order->transaction->transaction_id }}</dd>
                </div>
            @endif
        </dl>

        @if ($order->beautician || $order->spaBranch || $order->appointment_date || $order->appointment_time)
            <div class="order-receipt__divider"></div>
            <section class="order-receipt__appointment">
                <p class="order-receipt__label">{{ trans('order::print.appointment') }}</p>
                @if ($order->spaBranch)
                    <p class="order-receipt__value">{{ trans('order::orders.spa_branch') }}: {{ $order->spaBranch->name }}</p>
                @endif
                @if ($order->beautician)
                    <p class="order-receipt__value">{{ trans('order::print.beautician') }}: {{ $order->beautician->name }}</p>
                @endif
                @if ($order->appointment_date)
                    <p class="order-receipt__value">{{ trans('order::print.appointment_date') }}: {{ $order->appointment_date->format('d M Y') }}</p>
                @endif
                @if ($order->appointment_time)
                    <p class="order-receipt__value">{{ trans('order::print.appointment_time') }}: {{ $order->appointment_time }}</p>
                @endif
            </section>
        @endif

        <footer class="order-receipt__footer">
            <p>{{ trans('order::print.thank_you') }}</p>
            <p class="order-receipt__footer-note">{{ trans('order::print.receipt_footer_note') }}</p>
        </footer>
    </article>

    @include('order::admin.orders.print._print-actions')
</body>
</html>
