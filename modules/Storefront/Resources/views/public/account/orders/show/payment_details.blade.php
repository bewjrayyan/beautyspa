@if (($variant ?? 'full') === 'compact')
    <div class="account-order-payment-simple">
        <div class="account-order-payment-simple__row">
            <span class="account-order-payment-simple__label">{{ trans('storefront::account.view_order.payment_method') }}</span>
            <span class="account-order-payment-simple__value">{{ $order->payment_method }}</span>
        </div>

        <div class="account-order-payment-simple__row">
            <span class="account-order-payment-simple__label">{{ trans('storefront::account.view_order.payment_status') }}</span>
            <span class="account-order-payment-simple__value">
                <span class="badge {{ payment_status_badge_class($order->payment_status) }}">
                    {{ $order->paymentStatusLabel() }}
                </span>
            </span>
        </div>

        @if ($order->transaction?->transaction_id)
            <div class="account-order-payment-simple__row account-order-payment-simple__row--transaction" x-data="{ copied: false }">
                <span class="account-order-payment-simple__label">{{ trans('storefront::account.view_order.transaction_id') }}</span>

                <div class="account-order-payment-simple__transaction">
                    <span class="account-order-payment-simple__value account-order-payment-simple__value--mono">
                        {{ $order->transaction->transaction_id }}
                    </span>

                    <button
                        type="button"
                        class="account-order-payment-simple__copy"
                        title="{{ trans('storefront::account.view_order.copy') }}"
                        @click="
                            navigator.clipboard.writeText('{{ $order->transaction->transaction_id }}').then(() => {
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            });
                        "
                    >
                        <i class="lar la-copy"></i>
                        <span x-show="copied" x-cloak>{{ trans('storefront::account.view_order.copied') }}</span>
                    </button>
                </div>
            </div>
        @endif

        @include('storefront::public.account.orders.show.payment_proof')
    </div>
@else
    <ul class="account-order-sidebar__list">
        <li class="account-order-sidebar__li--payment-method">
            <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.payment_method') }}</span>
            <span class="account-order-sidebar__value">{{ $order->payment_method }}</span>

            @if ($order->transaction?->transaction_id || str_starts_with((string) $order->payment_method, 'chip') || strtolower($order->payment_method) === strtolower((string) setting('chip_label')))
                <div class="account-order-sidebar__payment-banner">
                    <img
                        src="{{ \Modules\Payment\Services\ChipCheckoutLogo::urlForOrder($order) ?? asset('images/payments/pay-with-chip-all.png') }}"
                        alt="{{ trans('storefront::account.view_order.pay_with_chip_alt') }}"
                        class="account-order-sidebar__payment-banner-img"
                        width="560"
                        height="200"
                        loading="lazy"
                    >
                </div>
            @endif
        </li>
        <li>
            <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.payment_status') }}</span>
            <span class="account-order-sidebar__value">
                <span class="badge {{ payment_status_badge_class($order->payment_status) }}">
                    {{ $order->paymentStatusLabel() }}
                </span>
            </span>
        </li>
        @if ($order->transaction?->transaction_id)
            <li class="account-order-sidebar__li--transaction" x-data="{ copied: false }">
                <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.transaction_id') }}</span>
                <span class="account-order-sidebar__value account-order-sidebar__value--mono">
                    {{ $order->transaction->transaction_id }}
                </span>
                <button
                    type="button"
                    class="account-order-sidebar__copy"
                    title="{{ trans('storefront::account.view_order.copy') }}"
                    @click="
                        navigator.clipboard.writeText('{{ $order->transaction->transaction_id }}').then(() => {
                            copied = true;
                            setTimeout(() => copied = false, 2000);
                        });
                    "
                >
                    <i class="lar la-copy"></i>
                    <span x-show="copied" x-cloak>{{ trans('storefront::account.view_order.copied') }}</span>
                </button>
            </li>
        @endif
    </ul>

    @include('storefront::public.account.orders.show.payment_proof')
@endif
