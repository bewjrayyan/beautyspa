@php
    $hasAppointmentCard = $order->hasAppointmentDetails()
        || $order->beautician
        || $order->spaBranch
        || ! empty($treatmentBooking?->beautician_notes);
@endphp

<div class="order-show__section">
    <div @class(['order-show__grid', 'order-show__grid--2' => $hasAppointmentCard])>
        <div class="order-show__card">
            <div class="order-show__card-head">
                <h5><i class="fa fa-file-text-o" aria-hidden="true"></i> {{ trans('order::orders.order_information') }}</h5>
            </div>
            <dl class="order-show__dl">
                <div class="order-show__dl-row order-show__dl-row--payment-method">
                    <dt>{{ trans('order::orders.payment_method') }}</dt>
                    <dd>
                        {{ $order->payment_method ?: '—' }}
                        @if ($order->getRawOriginal('payment_method') === 'bank_transfer')
                            @include('order::admin.orders.partials.order_payment_proof_inline')
                        @endif
                    </dd>
                </div>
                <div class="order-show__dl-row">
                    <dt>{{ trans('order::orders.transaction_id') }}</dt>
                    <dd>
                        @if ($order->transaction?->transaction_id)
                            <code class="order-show__mono">{{ $order->transaction->transaction_id }}</code>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                @if ($order->hasCoupon())
                    <div class="order-show__dl-row">
                        <dt>{{ trans('order::orders.coupon') }}</dt>
                        <dd><span class="order-show__coupon-code">{{ $order->coupon->code }}</span></dd>
                    </div>
                @endif
                @if ($order->shipping_method)
                    <div class="order-show__dl-row">
                        <dt>{{ trans('order::orders.shipping_method') }}</dt>
                        <dd>{{ $order->shipping_method }}</dd>
                    </div>
                @endif
                @if (app('modules')->isEnabled('Loyalty'))
                    <div class="order-show__dl-row">
                        <dt>{{ trans('loyalty::orders.points_earned') }}</dt>
                        <dd>
                            @if ($order->loyalty_points_earned > 0)
                                {{ number_format($order->loyalty_points_earned) }} {{ trans('order::orders.loyalty_pts') }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    @if ($order->loyalty_points_redeemed > 0)
                        <div class="order-show__dl-row">
                            <dt>{{ trans('loyalty::orders.points_redeemed') }}</dt>
                            <dd>
                                {{ number_format($order->loyalty_points_redeemed) }} {{ trans('order::orders.loyalty_pts') }}
                                @if ($order->loyalty_discount_amount > 0)
                                    <span class="order-show__hint">
                                        (&minus;{{ \Modules\Support\Money::inDefaultCurrency($order->loyalty_discount_amount)->format() }})
                                    </span>
                                @endif
                            </dd>
                        </div>
                    @endif
                    <div class="order-show__dl-row">
                        <dt>{{ trans('order::orders.loyalty_points_balance') }}</dt>
                        <dd>
                            @if ($loyaltyWallet)
                                <strong>{{ number_format($loyaltyWallet->balance) }}</strong> {{ trans('order::orders.loyalty_pts') }}
                                @if ($loyaltyWallet->tier?->name)
                                    <span class="order-show__hint">({{ $loyaltyWallet->tier->name }})</span>
                                @endif
                                @hasAccess('admin.loyalty.members.show')
                                    <div class="order-show__hint">
                                        <a href="{{ route('admin.loyalty.members.show', $loyaltyWallet) }}">{{ trans('order::orders.view_loyalty_member') }}</a>
                                    </div>
                                @endHasAccess
                            @elseif ($order->customer_id)
                                {{ trans('order::orders.loyalty_no_wallet') }}
                            @else
                                {{ trans('order::orders.loyalty_guest') }}
                            @endif
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        @if ($hasAppointmentCard)
            @include('order::admin.orders.partials.appointment_information')
        @endif
    </div>
</div>
