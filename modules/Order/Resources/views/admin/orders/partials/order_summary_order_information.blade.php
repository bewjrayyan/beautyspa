{{-- Order Information rows — rendered in sidebar summary after Points earned. --}}
<tr class="order-show__totals-info">
    <td>{{ trans('order::orders.payment_method') }}</td>
    <td>{{ $order->payment_method ?: '—' }}</td>
</tr>

<tr class="order-show__totals-info">
    <td>{{ trans('order::orders.transaction_id') }}</td>
    <td id="order-transaction-id-display">
        @if ($order->transaction?->transaction_id)
            <code class="order-show__mono">{{ $order->transaction->transaction_id }}</code>
        @else
            —
        @endif
    </td>
</tr>

@if (filled($order->transaction?->admin_note))
    <tr class="order-show__totals-info" id="order-payment-admin-note-row">
        <td>{{ trans('order::orders.admin_note') }}</td>
        <td id="order-payment-admin-note-display">{{ $order->transaction->admin_note }}</td>
    </tr>
@else
    <tr class="order-show__totals-info" id="order-payment-admin-note-row" hidden>
        <td>{{ trans('order::orders.admin_note') }}</td>
        <td id="order-payment-admin-note-display"></td>
    </tr>
@endif

@if ($order->hasCoupon())
    <tr class="order-show__totals-info">
        <td>{{ trans('order::orders.coupon') }}</td>
        <td><span class="order-show__coupon-code">{{ $order->coupon->code }}</span></td>
    </tr>
@endif

@if ($order->shipping_method)
    <tr class="order-show__totals-info">
        <td>{{ trans('order::orders.shipping_method') }}</td>
        <td>{{ $order->shipping_method }}</td>
    </tr>
@endif

@if (app('modules')->isEnabled('Loyalty'))
    @if ($order->loyalty_points_redeemed > 0)
        <tr class="order-show__totals-info">
            <td>{{ trans('loyalty::orders.points_redeemed') }}</td>
            <td>
                {{ number_format($order->loyalty_points_redeemed) }} {{ trans('order::orders.loyalty_pts') }}
                @if ($order->loyalty_discount_amount > 0)
                    <span class="order-show__hint">
                        (&minus;{{ \Modules\Support\Money::inDefaultCurrency($order->loyalty_discount_amount)->format() }})
                    </span>
                @endif
            </td>
        </tr>
    @endif

    <tr class="order-show__totals-info">
        <td>{{ trans('order::orders.loyalty_points_balance') }}</td>
        <td>
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
        </td>
    </tr>
@endif
