<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('order::print.receipt') }} #{{ $order->id }}</title>
    @if ($forPdf ?? false)
        @if (! empty($inlineReceiptCss))
            <style>{!! $inlineReceiptCss !!}</style>
        @elseif (! empty($printCssUrl))
            <link rel="stylesheet" href="{{ $printCssUrl }}">
        @endif
        <style>
            :root {
                --color-primary: {{ function_exists('storefront_theme_color') ? storefront_theme_color() : '#0068e1' }};
            }
        </style>
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
        @if (($receiptActions ?? false) && ! ($forPdf ?? false))
        .order-receipt-wrap { max-width: 360px; margin: 16px auto 24px; padding: 0 16px; }
        .order-receipt-wrap .order-receipt { margin: 0; }
        .order-receipt-flash { margin-bottom: 12px; padding: 10px 14px; font-size: 13px; font-weight: 600; border-radius: 10px; }
        .order-receipt-flash--success { color: #065f46; background: #ecfdf5; border: 1px solid #a7f3d0; }
        .order-receipt-flash--error { color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; }
        .order-receipt-toolbar { display: flex; flex-direction: row; flex-wrap: nowrap; gap: 8px; margin-top: 12px; }
        .order-receipt-toolbar__form { margin: 0; flex: 1 1 0; min-width: 0; }
        .order-receipt-toolbar__btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 5px;
            width: 100%; flex: 1 1 0; min-width: 0; min-height: 38px; padding: 8px 8px;
            font-family: inherit; font-size: 11px; font-weight: 600; line-height: 1.2;
            white-space: nowrap; text-decoration: none; border-radius: 8px; cursor: pointer;
            box-sizing: border-box; transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .order-receipt-toolbar__btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12); }
        .order-receipt-toolbar__btn--whatsapp { color: #166534; background: #f0fdf4; border: 1px solid #bbf7d0; }
        .order-receipt-toolbar__btn--download { color: #fff; background: var(--color-primary, #0068e1); border: 1px solid transparent; }
        .order-receipt-toolbar__icon { width: 14px; height: 14px; flex-shrink: 0; }
        @media print { .order-receipt-toolbar, .order-receipt-flash { display: none !important; } }
        @endif
    </style>
</head>
<body class="{{ is_rtl() ? 'rtl' : 'ltr' }}">
    @if (($receiptActions ?? false) && ! ($forPdf ?? false))
        <div class="order-receipt-wrap">
            @if (session('success'))
                <div class="order-receipt-flash order-receipt-flash--success" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="order-receipt-flash order-receipt-flash--error" role="alert">
                    {{ session('error') }}
                </div>
            @endif
    @endif

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

        @include('order::partials.treatment_appointment_summary', ['order' => $order, 'style' => 'order-receipt'])

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

        <footer class="order-receipt__footer">
            <p>{{ trans('order::print.thank_you') }}</p>
            <p class="order-receipt__footer-note">{{ trans('order::print.receipt_footer_note') }}</p>
        </footer>
    </article>

    @unless ($forPdf ?? false)
        @if ($receiptActions ?? false)
            @include('order::admin.orders.print._receipt-actions', [
                'canSendReceiptWhatsApp' => $canSendReceiptWhatsApp ?? false,
                'receiptWhatsAppUrl' => $receiptWhatsAppUrl,
                'receiptDownloadUrl' => $receiptDownloadUrl,
            ])
        </div>
        @else
            @include('order::admin.orders.print._print-actions')
        @endif
    @endunless
</body>
</html>
