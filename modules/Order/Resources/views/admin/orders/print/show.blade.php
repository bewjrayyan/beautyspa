<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('order::print.invoice') }} #{{ $order->id }}</title>
    @if ($forPdf ?? false)
        @if (! empty($printCssUrl))
            <link rel="stylesheet" href="{{ $printCssUrl }}">
        @endif
    @else
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        @vite(['modules/Order/Resources/assets/admin/sass/print.scss'])
    @endif
    <style>
        :root {
            --color-primary: {{ function_exists('storefront_theme_color') ? storefront_theme_color() : '#f274ac' }};
        }
    </style>
</head>
<body class="{{ is_rtl() ? 'rtl' : 'ltr' }}">
    <article class="order-invoice">
        <header class="order-invoice__header">
            <div class="order-invoice__brand">
                @if ($logo ?? null)
                    <img src="{{ $logo }}" alt="{{ setting('store_name') }}" class="order-invoice__logo">
                @endif

                <div class="order-invoice__company">
                    @if (setting('store_address_1') || setting('store_address_2'))
                        <p class="order-invoice__company-line">
                            {{ collect([setting('store_address_1'), setting('store_address_2')])->filter()->implode(', ') }}
                        </p>
                    @endif

                    <p class="order-invoice__company-contact">
                        @if (setting('store_phone'))
                            <span>{{ setting('store_phone') }}</span>
                        @endif
                        @if (setting('store_phone') && setting('store_email'))
                            <span class="order-invoice__contact-sep">·</span>
                        @endif
                        @if (setting('store_email'))
                            <span>{{ setting('store_email') }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="order-invoice__title-block">
                <h2 class="order-invoice__title">{{ trans('order::print.invoice') }}</h2>
                <div class="order-invoice__meta">
                    <span>
                        <strong>{{ trans('order::print.invoice_id') }}:</strong>
                        #{{ $order->id }}
                    </span>
                    <span>
                        <strong>{{ trans('order::print.order_id') }}:</strong>
                        #{{ $order->id }}
                    </span>
                    <span>
                        <strong>{{ trans('order::print.date') }}:</strong>
                        {{ $order->created_at->format('d M Y, h:i A') }}
                    </span>
                </div>
            </div>
        </header>

        <section class="order-invoice__section order-invoice__section--addresses">
            <div class="order-invoice__grid">
                <div>
                    <h3 class="order-invoice__section-title">{{ trans('order::print.billing_address') }}</h3>
                    <div class="order-invoice__card order-invoice__address">
                        <span class="order-invoice__address-name">{{ $order->billing_full_name }}</span>
                        <span>{{ $order->billing_address_1 }}</span>
                        @if ($order->billing_address_2)
                            <span>{{ $order->billing_address_2 }}</span>
                        @endif
                        <span>{{ $order->billing_city }}, {!! $order->billing_state_name !!} {{ $order->billing_zip }}</span>
                        <span>{{ $order->billing_country_name }}</span>
                    </div>
                </div>

                <div>
                    <h3 class="order-invoice__section-title">{{ trans('order::print.shipping_address') }}</h3>
                    <div class="order-invoice__card order-invoice__address">
                        <span class="order-invoice__address-name">{{ $order->shipping_full_name }}</span>
                        <span>{{ $order->shipping_address_1 }}</span>
                        @if ($order->shipping_address_2)
                            <span>{{ $order->shipping_address_2 }}</span>
                        @endif
                        <span>{{ $order->shipping_city }}, {!! $order->shipping_state_name !!} {{ $order->shipping_zip }}</span>
                        <span>{{ $order->shipping_country_name }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="order-invoice__section order-invoice__section--items">
            <h3 class="order-invoice__section-title order-invoice__section-title--items">{{ trans('order::print.items') }}</h3>
            <table class="order-invoice__table">
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
                                <span class="order-invoice__item-name">{{ $product->name }}</span>

                                @if ($product->hasAnyVariation())
                                    <span class="order-invoice__item-meta">
                                        @foreach ($product->variations as $variation)
                                            {{ $variation->name }}: {{ $variation->values()->first()?->label }}{{ $loop->last ? '' : ', ' }}
                                        @endforeach
                                    </span>
                                @endif

                                @if ($product->hasAnyOption())
                                    <span class="order-invoice__item-meta">
                                        @foreach ($product->options as $option)
                                            {{ $option->name }}:
                                            @if ($option->option->isFieldType())
                                                {{ $option->value }}
                                            @else
                                                {{ $option->values->implode('label', ', ') }}
                                            @endif
                                            {{ $loop->last ? '' : ' · ' }}
                                        @endforeach
                                    </span>
                                @endif
                            </td>
                            <td>
                                {{ $product->unit_price->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                            </td>
                            <td>{{ $product->qty }}</td>
                            <td>
                                {{ $product->line_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="order-invoice__totals-wrap">
                <div class="order-invoice__meta-column">
                    <div class="order-invoice__meta-panel">
                        <div class="order-invoice__meta-panel-head">
                            <h3 class="order-invoice__section-title">{{ trans('order::print.order_details') }}</h3>
                        </div>

                        <div class="order-invoice__meta-col">
                            <p class="order-invoice__meta-label">{{ trans('order::print.customer') }}</p>
                            <p class="order-invoice__meta-value order-invoice__meta-value--name">{{ $order->customer_full_name }}</p>
                            <dl class="order-invoice__meta-fact">
                                <dt>{{ trans('order::print.email') }}</dt>
                                <dd>{{ $order->customer_email }}</dd>
                            </dl>
                            <dl class="order-invoice__meta-fact">
                                <dt>{{ trans('order::print.phone') }}</dt>
                                <dd>{{ $order->customer_phone }}</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="order-invoice__meta-panel">
                        <div class="order-invoice__meta-panel-head">
                            <h3 class="order-invoice__section-title">{{ trans('order::print.payment_details') }}</h3>
                        </div>

                        <div class="order-invoice__meta-col">
                            <dl class="order-invoice__meta-fact">
                                <dt>{{ trans('order::print.order_status') }}</dt>
                                <dd>
                                    <span class="order-invoice__status order-invoice__status--{{ $order->status }}">
                                        {{ $order->status() }}
                                    </span>
                                </dd>
                            </dl>
                            <dl class="order-invoice__meta-fact">
                                <dt>{{ trans('order::print.payment_status') }}</dt>
                                <dd>
                                    <span class="order-invoice__status order-invoice__status--{{ $order->payment_status }}">
                                        {{ $order->paymentStatusLabel() }}
                                    </span>
                                </dd>
                            </dl>
                            <dl class="order-invoice__meta-fact">
                                <dt>{{ trans('order::print.payment_method') }}</dt>
                                <dd>{{ $order->payment_method }}</dd>
                            </dl>
                            @if ($order->transaction?->transaction_id)
                                <dl class="order-invoice__meta-fact">
                                    <dt>{{ trans('order::print.transaction_id') }}</dt>
                                    <dd class="order-invoice__mono">{{ $order->transaction->transaction_id }}</dd>
                                </dl>
                            @endif
                            @if ($order->shipping_method)
                                <dl class="order-invoice__meta-fact">
                                    <dt>{{ trans('order::print.shipping_method') }}</dt>
                                    <dd>{{ $order->shipping_method }}</dd>
                                </dl>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="order-invoice__totals-column">
                <div class="order-invoice__totals">
                    @include('order::partials.pricing_breakdown', ['order' => $order, 'style' => 'invoice'])

                    <dl class="order-invoice__row order-invoice__row--total">
                        <dt>{{ trans('order::print.total') }}</dt>
                        <dd>{{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}</dd>
                    </dl>
                </div>

                @if ($order->beautician || $order->spaBranch || $order->appointment_date || $order->appointment_time)
                    <div class="order-invoice__meta-box order-invoice__meta-appointment">
                        <p class="order-invoice__meta-label">{{ trans('order::print.appointment') }}</p>
                        <div class="order-invoice__meta-box-rows">
                            @if ($order->spaBranch)
                                <dl class="order-invoice__meta-fact">
                                    <dt>{{ trans('order::orders.spa_branch') }}</dt>
                                    <dd>{{ $order->spaBranch->name }}</dd>
                                </dl>
                            @endif
                            @if ($order->beautician)
                                <dl class="order-invoice__meta-fact">
                                    <dt>{{ trans('order::print.beautician') }}</dt>
                                    <dd>{{ $order->beautician->name }}</dd>
                                </dl>
                            @endif
                            @if ($order->appointment_date)
                                <dl class="order-invoice__meta-fact">
                                    <dt>{{ trans('order::print.appointment_date') }}</dt>
                                    <dd>{{ $order->appointment_date->format('d M Y') }}</dd>
                                </dl>
                            @endif
                            @if ($order->appointment_time)
                                <dl class="order-invoice__meta-fact">
                                    <dt>{{ trans('order::print.appointment_time') }}</dt>
                                    <dd>{{ $order->appointment_time }}</dd>
                                </dl>
                            @endif
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </section>

        <footer class="order-invoice__footer">
            <p class="order-invoice__thank-you">{{ trans('order::print.thank_you') }}</p>
            <p>{{ trans('order::print.footer_note') }}</p>
        </footer>
    </article>

    @include('order::admin.orders.print._print-actions')
</body>
</html>
