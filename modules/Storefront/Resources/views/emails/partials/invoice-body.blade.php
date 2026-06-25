@php
    $rtl = $rtl ?? false;
    $align = $rtl ? 'right' : 'left';
    $oppositeAlign = $rtl ? 'left' : 'right';
    $money = fn ($amount) => $amount->convert($order->currency, $order->currency_rate)->format($order->currency);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ trans('storefront::invoice.invoice') }} #{{ $order->id }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#334155;font-size:15px;line-height:1.5;-webkit-text-size-adjust:100%;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f4f6f8;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:680px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:{{ $themeColor }};padding:28px 32px;text-align:center;">
                            @if (! empty($logo))
                                <img src="{{ $logo }}" alt="{{ setting('store_name') }}" style="display:block;margin:0 auto 12px;max-height:56px;max-width:220px;border:0;">
                            @else
                                <p style="margin:0 0 8px;font-size:22px;font-weight:700;color:#ffffff;">{{ setting('store_name') }}</p>
                            @endif
                            <p style="margin:0;font-size:13px;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.92);font-weight:700;">
                                {{ trans('storefront::invoice.invoice') }}
                            </p>
                            <p style="margin:8px 0 0;font-size:14px;color:rgba(255,255,255,0.88);">
                                {{ trans('storefront::invoice.order_id') }} #{{ $order->id }}
                                &nbsp;·&nbsp;
                                {{ $order->created_at->format('d M Y, h:i A') }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 32px 8px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td width="50%" valign="top" style="padding:0 12px 16px 0;text-align:{{ $align }};">
                                        <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:{{ $themeColor }};">
                                            {{ trans('storefront::invoice.order_details') }}
                                        </p>
                                        <p style="margin:0 0 4px;"><strong>{{ trans('storefront::invoice.email') }}:</strong> {{ $order->customer_email }}</p>
                                        @if ($order->customer_phone)
                                            <p style="margin:0 0 4px;"><strong>{{ trans('storefront::invoice.phone') }}:</strong> {{ $order->customer_phone }}</p>
                                        @endif
                                        <p style="margin:0 0 4px;"><strong>{{ trans('storefront::invoice.payment_method') }}:</strong> {{ $order->payment_method }}</p>
                                        <p style="margin:0 0 4px;"><strong>{{ trans('storefront::invoice.payment_status') }}:</strong> {{ $order->paymentStatusLabel() }}</p>
                                        <p style="margin:0;"><strong>{{ trans('storefront::invoice.order_status') }}:</strong> {{ $order->status() }}</p>
                                    </td>
                                    <td width="50%" valign="top" style="padding:0 0 16px 12px;text-align:{{ $align }};">
                                        @if ($order->appointment_date)
                                            <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:{{ $themeColor }};">
                                                {{ trans('order::print.appointment') }}
                                            </p>
                                            <p style="margin:0 0 4px;"><strong>{{ trans('order::print.appointment_date') }}:</strong> {{ $order->appointment_date->format('d M Y') }}</p>
                                            @if ($order->appointment_time)
                                                <p style="margin:0 0 4px;"><strong>{{ trans('order::print.appointment_time') }}:</strong> {{ $order->appointment_time }}</p>
                                            @endif
                                            @if ($order->beautician?->name)
                                                <p style="margin:0;"><strong>{{ trans('order::print.beautician') }}:</strong> {{ $order->beautician->name }}</p>
                                            @endif
                                        @elseif ($order->hasPhysicalProducts())
                                            <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:{{ $themeColor }};">
                                                {{ trans('storefront::invoice.shipping_address') }}
                                            </p>
                                            <p style="margin:0;">{{ $order->shipping_full_name }}</p>
                                            <p style="margin:0;">{{ $order->shipping_address_1 }}</p>
                                            @if ($order->shipping_address_2)
                                                <p style="margin:0;">{{ $order->shipping_address_2 }}</p>
                                            @endif
                                            <p style="margin:0;">{{ $order->shipping_city }}, {!! $order->shipping_state_name !!} {{ $order->shipping_zip }}</p>
                                            <p style="margin:0;">{{ $order->shipping_country_name }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if ($order->hasPhysicalProducts())
                        <tr>
                            <td style="padding:0 32px 8px;">
                                <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:{{ $themeColor }};">
                                    {{ trans('storefront::invoice.billing_address') }}
                                </p>
                                <p style="margin:0;">{{ $order->billing_full_name }}, {{ $order->billing_address_1 }}@if ($order->billing_address_2), {{ $order->billing_address_2 }}@endif, {{ $order->billing_city }}, {!! $order->billing_state_name !!} {{ $order->billing_zip }}, {{ $order->billing_country_name }}</p>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:20px 32px 8px;">
                            <p style="margin:0 0 12px;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:{{ $themeColor }};">
                                {{ trans('order::print.items') }}
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th align="{{ $align }}" style="padding:10px 12px;background:#f8fafc;border-bottom:2px solid #e2e8f0;font-size:12px;text-transform:uppercase;color:#64748b;">{{ trans('storefront::invoice.product') }}</th>
                                        <th align="center" style="padding:10px 8px;background:#f8fafc;border-bottom:2px solid #e2e8f0;font-size:12px;text-transform:uppercase;color:#64748b;">{{ trans('storefront::invoice.quantity') }}</th>
                                        <th align="{{ $oppositeAlign }}" style="padding:10px 8px;background:#f8fafc;border-bottom:2px solid #e2e8f0;font-size:12px;text-transform:uppercase;color:#64748b;">{{ trans('storefront::invoice.unit_price') }}</th>
                                        <th align="{{ $oppositeAlign }}" style="padding:10px 12px;background:#f8fafc;border-bottom:2px solid #e2e8f0;font-size:12px;text-transform:uppercase;color:#64748b;">{{ trans('storefront::invoice.line_total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->products as $product)
                                        <tr>
                                            <td valign="top" style="padding:14px 12px;border-bottom:1px solid #eef2f7;text-align:{{ $align }};">
                                                <strong style="color:#0f172a;">{{ $product->name }}</strong>
                                                @if ($product->hasAnyVariation() || $product->hasAnyOption())
                                                    <div style="margin-top:6px;font-size:13px;color:#64748b;">
                                                        @if ($product->hasAnyVariation())
                                                            @foreach ($product->variations as $variation)
                                                                <div>{{ $variation->name }}: {{ $variation->values()->first()?->label ?? $variation->value }}</div>
                                                            @endforeach
                                                        @endif
                                                        @if ($product->hasAnyOption())
                                                            @foreach ($product->options as $option)
                                                                <div>
                                                                    {{ $option->name }}:
                                                                    @if ($option->option->isFieldType())
                                                                        {{ $option->value }}
                                                                    @else
                                                                        {{ $option->values->implode('label', ', ') }}
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td valign="top" align="center" style="padding:14px 8px;border-bottom:1px solid #eef2f7;">{{ $product->qty }}</td>
                                            <td valign="top" align="{{ $oppositeAlign }}" style="padding:14px 8px;border-bottom:1px solid #eef2f7;white-space:nowrap;">{{ $money($product->unit_price) }}</td>
                                            <td valign="top" align="{{ $oppositeAlign }}" style="padding:14px 12px;border-bottom:1px solid #eef2f7;white-space:nowrap;font-weight:600;">{{ $money($product->line_total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:8px 32px 28px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td></td>
                                    <td width="320" valign="top">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                                            <tr>
                                                <td colspan="2" style="padding:12px 16px;background:#f8fafc;border-bottom:1px solid #e2e8f0;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:{{ $themeColor }};">
                                                    {{ trans('storefront::invoice.payment_summary') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 16px;color:#64748b;">{{ trans('storefront::invoice.subtotal') }}</td>
                                                <td align="{{ $oppositeAlign }}" style="padding:10px 16px;font-weight:600;">{{ $money($order->sub_total) }}</td>
                                            </tr>
                                            @if ($order->hasShippingMethod())
                                                <tr>
                                                    <td style="padding:10px 16px;color:#64748b;">{{ $order->shipping_method }}</td>
                                                    <td align="{{ $oppositeAlign }}" style="padding:10px 16px;font-weight:600;">{{ $money($order->shipping_cost) }}</td>
                                                </tr>
                                            @endif
                                            @foreach ($order->taxes as $tax)
                                                <tr>
                                                    <td style="padding:10px 16px;color:#64748b;">{{ $tax->name }}</td>
                                                    <td align="{{ $oppositeAlign }}" style="padding:10px 16px;font-weight:600;">{{ $money($tax->order_tax->amount) }}</td>
                                                </tr>
                                            @endforeach
                                            @if ($order->hasCoupon())
                                                <tr>
                                                    <td style="padding:10px 16px;color:#64748b;">{{ trans('storefront::invoice.coupon') }} ({{ $order->coupon->code }})</td>
                                                    <td align="{{ $oppositeAlign }}" style="padding:10px 16px;font-weight:600;color:#dc2626;">&minus;{{ $money($order->discount) }}</td>
                                                </tr>
                                            @endif
                                            @if ($order->hasLoyaltyRedemption())
                                                <tr>
                                                    <td style="padding:10px 16px;color:#64748b;">
                                                        {{ trans('storefront::invoice.loyalty_discount') }}
                                                        ({{ number_format((int) $order->loyalty_points_redeemed) }} {{ trans('order::orders.loyalty_pts') }})
                                                    </td>
                                                    <td align="{{ $oppositeAlign }}" style="padding:10px 16px;font-weight:600;color:#dc2626;">&minus;{{ $money($order->loyaltyDiscountAmount()) }}</td>
                                                </tr>
                                            @endif
                                            @if ($order->hasPaymentProcessingFee())
                                                <tr>
                                                    <td style="padding:10px 16px;color:#64748b;">{{ trans('order::print.payment_processing_fee') }}</td>
                                                    <td align="{{ $oppositeAlign }}" style="padding:10px 16px;font-weight:600;">{{ $money($order->paymentProcessingFee()) }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td style="padding:14px 16px;background:{{ $themeColor }};color:#ffffff;font-size:16px;font-weight:700;">{{ trans('storefront::invoice.total') }}</td>
                                                <td align="{{ $oppositeAlign }}" style="padding:14px 16px;background:{{ $themeColor }};color:#ffffff;font-size:16px;font-weight:700;">{{ $money($order->total) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if ($order->payment_method === 'Bank Transfer' && setting('bank_transfer_instructions'))
                        <tr>
                            <td style="padding:0 32px 20px;">
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;">
                                    <tr>
                                        <td style="padding:14px 16px;font-size:14px;color:#92400e;">
                                            {!! setting('bank_transfer_instructions') !!}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:0 32px 28px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                                <tr>
                                    <td style="padding:16px 18px;text-align:{{ $align }};">
                                        <p style="margin:0 0 6px;font-weight:700;color:#0f172a;">{{ trans('storefront::invoice.attachments_note') }}</p>
                                        <p style="margin:0;font-size:14px;color:#64748b;">{{ trans('storefront::invoice.thank_you') }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <p style="margin:16px 0 0;font-size:12px;color:#94a3b8;text-align:center;">
                    {{ setting('store_name') }} · {{ trans('order::print.footer_note') }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
