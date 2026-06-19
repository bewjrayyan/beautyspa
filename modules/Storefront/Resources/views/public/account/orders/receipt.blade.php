<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('storefront::receipt.title') }} #{{ $order->id }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite([
        'modules/Storefront/Resources/assets/public/sass/vendors/_line-awesome.scss',
        'modules/Storefront/Resources/assets/public/sass/pages/account/orders/receipt/main.scss',
    ])
</head>
<body class="{{ is_rtl() ? 'rtl' : 'ltr' }}">
    <article class="payment-receipt">
        <header class="payment-receipt__header">
            <div class="payment-receipt__header-top">
                @if ($logo ?? null)
                    <img src="{{ $logo }}" alt="{{ setting('store_name') }}" class="payment-receipt__logo">
                @else
                    <span class="payment-receipt__store">{{ setting('store_name') }}</span>
                @endif
                <span class="payment-receipt__doc-type">{{ trans('storefront::receipt.title') }}</span>
            </div>
            <div class="payment-receipt__header-meta">
                <span>#{{ $order->id }}</span>
                <span class="payment-receipt__dot" aria-hidden="true"></span>
                <span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
            </div>
        </header>

        <section class="payment-receipt__block">
            <dl class="payment-receipt__facts">
                <div class="payment-receipt__fact">
                    <dt>{{ trans('storefront::receipt.customer') }}</dt>
                    <dd>{{ $order->customer_full_name }}</dd>
                </div>
                <div class="payment-receipt__fact">
                    <dt>{{ trans('storefront::receipt.email') }}</dt>
                    <dd>{{ $order->customer_email }}</dd>
                </div>
                <div class="payment-receipt__fact">
                    <dt>{{ trans('storefront::receipt.phone') }}</dt>
                    <dd>{{ $order->customer_phone }}</dd>
                </div>
                <div class="payment-receipt__fact">
                    <dt>{{ trans('storefront::receipt.payment_method') }}</dt>
                    <dd>{{ $order->payment_method }}</dd>
                </div>
                <div class="payment-receipt__fact">
                    <dt>{{ trans('storefront::receipt.payment_status') }}</dt>
                    <dd>
                        <span class="payment-receipt__status payment-receipt__status--{{ $order->payment_status }}">
                            {{ $order->paymentStatusLabel() }}
                        </span>
                    </dd>
                </div>
                @if ($order->transaction?->transaction_id)
                    <div class="payment-receipt__fact payment-receipt__fact--full">
                        <dt>{{ trans('storefront::receipt.transaction_id') }}</dt>
                        <dd class="payment-receipt__mono">{{ $order->transaction->transaction_id }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        @if ($order->beautician || $order->spaBranch || $order->appointment_date || $order->appointment_time)
            <section class="payment-receipt__block payment-receipt__block--muted">
                <p class="payment-receipt__block-label">{{ trans('storefront::receipt.appointment') }}</p>
                <dl class="payment-receipt__facts payment-receipt__facts--inline">
                    @if ($order->spaBranch)
                        <div class="payment-receipt__fact">
                            <dt>{{ trans('storefront::account.view_order.spa_branch') }}</dt>
                            <dd>{{ $order->spaBranch->name }}</dd>
                        </div>
                    @endif
                    @if ($order->beautician)
                        <div class="payment-receipt__fact">
                            <dt>{{ trans('storefront::receipt.beautician') }}</dt>
                            <dd>{{ $order->beautician->name }}</dd>
                        </div>
                    @endif
                    @if ($order->appointment_date)
                        <div class="payment-receipt__fact">
                            <dt>{{ trans('storefront::receipt.appointment_date') }}</dt>
                            <dd>{{ $order->appointment_date->format('d M Y') }}</dd>
                        </div>
                    @endif
                    @if ($order->appointment_time)
                        <div class="payment-receipt__fact">
                            <dt>{{ trans('storefront::receipt.appointment_time') }}</dt>
                            <dd>{{ $order->appointment_time }}</dd>
                        </div>
                    @endif
                </dl>
            </section>
        @endif

        <section class="payment-receipt__block">
            <table class="payment-receipt__lines">
                <thead>
                    <tr>
                        <th>{{ trans('storefront::receipt.description') }}</th>
                        <th>{{ trans('storefront::receipt.qty') }}</th>
                        <th>{{ trans('storefront::receipt.amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->products as $product)
                        <tr>
                            <td>
                                <span class="payment-receipt__line-name">{{ $product->name }}</span>
                                @if ($product->hasAnyVariation())
                                    <span class="payment-receipt__line-meta">
                                        @foreach ($product->variations as $variation)
                                            {{ $variation->values()->first()?->label }}{{ $loop->last ? '' : ', ' }}
                                        @endforeach
                                    </span>
                                @endif
                            </td>
                            <td>{{ $product->qty }}</td>
                            <td>{{ $product->line_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <dl class="payment-receipt__summary">
                <div class="payment-receipt__summary-row">
                    <dt>{{ trans('storefront::receipt.subtotal') }}</dt>
                    <dd>{{ $order->sub_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</dd>
                </div>
                @if ($order->hasCoupon())
                    <div class="payment-receipt__summary-row">
                        <dt>{{ trans('storefront::receipt.discount') }} ({{ $order->coupon->code }})</dt>
                        <dd>&minus;{{ $order->discount->convert($order->currency, $order->currency_rate)->format($order->currency) }}</dd>
                    </div>
                @endif
                @foreach ($order->taxes as $tax)
                    <div class="payment-receipt__summary-row">
                        <dt>{{ $tax->name }}</dt>
                        <dd>{{ $tax->order_tax->amount->convert($order->currency, $order->currency_rate)->format($order->currency) }}</dd>
                    </div>
                @endforeach
                <div class="payment-receipt__summary-row payment-receipt__summary-row--total">
                    <dt>{{ trans('storefront::receipt.total') }}</dt>
                    <dd>{{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</dd>
                </div>
            </dl>
        </section>

        @if (! empty($orderRewards))
            <section class="payment-receipt__block payment-receipt__block--rewards">
                @include('loyalty::public.order_complete.rewards', ['orderRewards' => $orderRewards])
            </section>
        @endif

        <footer class="payment-receipt__footer">
            <p>{{ trans('storefront::receipt.thank_you') }}</p>
            <p class="payment-receipt__footer-note">{{ trans('storefront::receipt.footer_note') }}</p>
        </footer>
    </article>

    <script type="module">
        window.print();
    </script>
</body>
</html>
