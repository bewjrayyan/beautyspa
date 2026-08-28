@php
    $style = $style ?? 'invoice';
    $appointmentSummary = app(\Modules\Order\Services\OrderTreatmentAppointmentSummary::class);
    $appointmentLines = $appointmentSummary->lines($order);
@endphp

@if ($appointmentLines !== [])
    @switch($style)
        @case('payment-receipt')
            <section class="payment-receipt__block payment-receipt__appointment-summary">
                <p class="payment-receipt__block-label">{{ trans('storefront::checkout.appointment_details') }}</p>
                <dl class="payment-receipt__summary payment-receipt__summary--appointments">
                    @foreach ($appointmentLines as $line)
                        @if ($line['type'] === 'product')
                            <div @class([
                                'payment-receipt__summary-row',
                                'payment-receipt__summary-row--product',
                                'payment-receipt__summary-row--spaced' => ! empty($line['spaced']),
                            ])>
                                <dd>{{ $line['value'] }}</dd>
                            </div>
                        @else
                            <div class="payment-receipt__summary-row">
                                <dt>{{ $line['label'] }}</dt>
                                <dd>{{ $line['value'] }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </section>
            @break

        @case('order-receipt')
            <section class="order-receipt__appointment-summary">
                <p class="order-receipt__label">{{ trans('storefront::checkout.appointment_details') }}</p>
                <dl class="order-receipt__totals order-receipt__totals--appointments">
                    @foreach ($appointmentLines as $line)
                        @if ($line['type'] === 'product')
                            <div @class([
                                'order-receipt__appointment-product',
                                'order-receipt__appointment-product--spaced' => ! empty($line['spaced']),
                            ])>{{ $line['value'] }}</div>
                        @else
                            <div class="order-receipt__meta-row">
                                <dt>{{ $line['label'] }}</dt>
                                <dd>{{ $line['value'] }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </section>
            @break

        @case('email')
            <tr>
                <td colspan="2" style="padding:0 32px 12px;">
                    <p style="margin:0 0 10px;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:{{ $themeColor ?? '#0068e1' }};">
                        {{ trans('storefront::checkout.appointment_details') }}
                    </p>
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                        @foreach ($appointmentLines as $line)
                            @if ($line['type'] === 'product')
                                <tr>
                                    <td colspan="2" style="padding:12px 16px 6px;font-size:13px;font-weight:700;color:#6f2948;@if(!empty($line['spaced']))border-top:1px dashed #e5e7eb;@endif">
                                        {{ $line['value'] }}
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td style="padding:6px 16px;color:#64748b;font-size:13px;">{{ $line['label'] }}</td>
                                    <td align="right" style="padding:6px 16px;font-size:13px;font-weight:600;color:#334155;">{{ $line['value'] }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                </td>
            </tr>
            @break

        @case('pdf')
            <h2>{{ trans('storefront::checkout.appointment_details') }}</h2>
            <table class="totals appointment-summary">
                @foreach ($appointmentLines as $line)
                    @if ($line['type'] === 'product')
                        <tr class="appointment-product">
                            <td colspan="2" style="font-weight:bold;color:#6f2948;padding-top:{{ ! empty($line['spaced']) ? '10px' : '4px' }};">{{ $line['value'] }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="label">{{ $line['label'] }}</td>
                            <td class="amount">{{ $line['value'] }}</td>
                        </tr>
                    @endif
                @endforeach
            </table>
            @break

        @default
            <div class="order-invoice__appointment-summary">
                <p class="order-invoice__meta-label">{{ trans('storefront::checkout.appointment_details') }}</p>
                <div class="order-invoice__appointment-lines">
                    @foreach ($appointmentLines as $line)
                        @if ($line['type'] === 'product')
                            <p @class([
                                'order-invoice__appointment-product',
                                'order-invoice__appointment-product--spaced' => ! empty($line['spaced']),
                            ])>{{ $line['value'] }}</p>
                        @else
                            <dl class="order-invoice__row order-invoice__row--appointment">
                                <dt>{{ $line['label'] }}</dt>
                                <dd>{{ $line['value'] }}</dd>
                            </dl>
                        @endif
                    @endforeach
                </div>
            </div>
    @endswitch
@endif
