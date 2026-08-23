@php
    $isCompactPayment = ($variant ?? 'full') === 'compact';
    $paymentTransactionId = $order->transaction?->transaction_id;
    $showPaymentBanner = $paymentTransactionId
        || str_starts_with((string) $order->payment_method, 'chip')
        || strtolower((string) $order->payment_method) === strtolower((string) setting('chip_label'));
@endphp

<div @class([
    'account-order-payment',
    'account-order-payment--compact' => $isCompactPayment,
])>
    <div class="account-order-payment__summary">
        <span class="account-order-payment__method-icon" aria-hidden="true">
            <i class="las la-wallet"></i>
        </span>

        <span class="account-order-payment__method">
            <span class="account-order-payment__label">{{ trans('storefront::account.view_order.payment_method') }}</span>
            <strong class="account-order-payment__method-name">{{ $order->payment_method }}</strong>
        </span>

        <span class="badge account-order-payment__status {{ payment_status_badge_class($order->payment_status) }}">
            {{ $order->paymentStatusLabel() }}
        </span>
    </div>

    @if ($showPaymentBanner)
        <div class="account-order-payment__banner">
            <img
                src="{{ \Modules\Payment\Services\ChipCheckoutLogo::urlForOrder($order) ?? asset('images/payments/pay-with-chip-all.png') }}"
                alt="{{ trans('storefront::account.view_order.pay_with_chip_alt') }}"
                class="account-order-payment__banner-img"
                width="560"
                height="200"
                loading="lazy"
                decoding="async"
            >
        </div>
    @endif

    @if ($paymentTransactionId)
        <div class="account-order-payment__transaction" x-data="{ copied: false }">
            <span class="account-order-payment__transaction-main">
                <span class="account-order-payment__label">{{ trans('storefront::account.view_order.transaction_id') }}</span>
                <code class="account-order-payment__transaction-id">{{ $paymentTransactionId }}</code>
            </span>

            <button
                type="button"
                class="account-order-payment__copy"
                title="{{ trans('storefront::account.view_order.copy') }}"
                aria-label="{{ trans('storefront::account.view_order.copy') }}"
                data-transaction-id="{{ $paymentTransactionId }}"
                @click="
                    navigator.clipboard.writeText($el.dataset.transactionId).then(() => {
                        copied = true;
                        setTimeout(() => copied = false, 2000);
                    });
                "
            >
                <i class="lar la-copy" aria-hidden="true"></i>
                <span x-show="!copied">{{ trans('storefront::account.view_order.copy') }}</span>
                <span x-show="copied" x-cloak role="status">{{ trans('storefront::account.view_order.copied') }}</span>
            </button>
        </div>
    @endif

    @include('storefront::public.account.orders.show.payment_proof')
</div>
