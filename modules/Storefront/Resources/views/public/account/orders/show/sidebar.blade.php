<div class="account-order-sidebar">
    <div class="account-order-sidebar__card account-order-sidebar__card--actions">
        <div
            class="account-order-action-dropdown"
            x-data="{ open: false }"
            @click.outside="open = false"
        >
            <button
                type="button"
                class="account-order-action-dropdown__toggle"
                :class="{ 'is-open': open }"
                @click="open = ! open"
                aria-haspopup="true"
                :aria-expanded="open"
            >
                <span class="account-order-action-dropdown__grid" aria-hidden="true">
                    <span></span><span></span><span></span><span></span>
                </span>
                <span class="account-order-action-dropdown__label">
                    {{ trans('storefront::account.view_order.select_action') }}
                </span>
                <i class="las la-angle-down account-order-action-dropdown__chevron"></i>
            </button>

            <div class="account-order-action-dropdown__menu" x-show="open" x-cloak x-transition>
                <button
                    type="button"
                    class="account-order-action-dropdown__item"
                    @click="window.open('{{ route('account.orders.invoice', $order->id) }}', '_blank', 'noopener'); open = false"
                >
                    <i class="las la-file-invoice"></i>
                    {{ trans('storefront::account.view_order.download_invoice') }}
                </button>

                <button
                    type="button"
                    class="account-order-action-dropdown__item"
                    @click="window.open('{{ route('account.orders.receipt', $order->id) }}', '_blank', 'noopener'); open = false"
                >
                    <i class="las la-receipt"></i>
                    {{ trans('storefront::account.view_order.download_receipt') }}
                </button>

                @if ($canNotifyBeautician)
                    <button
                        type="button"
                        class="account-order-action-dropdown__item account-order-action-dropdown__item--whatsapp"
                        @click="$refs.notifyForm.submit(); open = false"
                    >
                        <i class="lab la-whatsapp"></i>
                        {{ trans('storefront::account.view_order.remind_beautician') }}
                    </button>
                @endif

                @if ($googleCalendarUrl ?? null)
                    <button
                        type="button"
                        class="account-order-action-dropdown__item"
                        @click="window.open(@js($googleCalendarUrl), '_blank', 'noopener'); open = false"
                    >
                        <i class="lab la-google"></i>
                        {{ trans('storefront::order_complete.add_to_google_calendar') }}
                    </button>
                @endif
            </div>

            @if ($canNotifyBeautician)
                <form
                    x-ref="notifyForm"
                    action="{{ route('account.orders.notify_beautician', $order->id) }}"
                    method="POST"
                    class="d-none"
                >
                    @csrf
                </form>
            @endif
        </div>
    </div>

    <div class="account-order-sidebar__card account-order-sidebar__card--payment">
        <h3 class="account-order-sidebar__title">
            <i class="las la-credit-card"></i>
            {{ trans('storefront::account.view_order.payment_details') }}
        </h3>

        <ul class="account-order-sidebar__list">
            <li class="account-order-sidebar__li--payment-method">
                <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.payment_method') }}</span>
                <span class="account-order-sidebar__value">{{ $order->payment_method }}</span>

                @if ($order->transaction?->transaction_id || str_starts_with((string) $order->payment_method, 'chip') || strtolower($order->payment_method) === strtolower((string) setting('chip_label')))
                    <div class="account-order-sidebar__payment-banner">
                        <img
                            src="{{ asset('images/payments/pay-with-chip-all.png') }}"
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
    </div>

    @if ($hasTreatmentBooking)
        <div class="account-order-sidebar__card account-order-sidebar__card--appointment">
            <h3 class="account-order-sidebar__title">
                <i class="las la-spa"></i>
                {{ trans('storefront::account.view_order.beautician') }}
            </h3>

            @if ($order->beautician)
                <div class="account-order-sidebar__beautician">
                    @if ($order->beautician->profile_image->exists)
                        <img
                            src="{{ $order->beautician->profile_image->path }}"
                            alt=""
                            class="account-order-sidebar__avatar"
                        >
                    @else
                        <span
                            class="account-order-sidebar__avatar account-order-sidebar__avatar--initial"
                            style="background-color: {{ $order->beautician->profile_color ?? 'var(--color-primary, #f274ac)' }}"
                        >{{ strtoupper(mb_substr($order->beautician->name, 0, 1)) }}</span>
                    @endif

                    <div>
                        <span class="account-order-sidebar__beautician-name">{{ $order->beautician->name }}</span>
                        @if ($order->beautician->job_title)
                            <span class="account-order-sidebar__beautician-role">{{ $order->beautician->job_title }}</span>
                        @endif
                    </div>
                </div>
            @endif

            <ul class="account-order-sidebar__list">
                @if ($order->spaBranch)
                    <li>
                        <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.spa_branch') }}</span>
                        <span class="account-order-sidebar__value">{{ $order->spaBranch->name }}</span>
                    </li>
                @endif
                @if ($order->appointment_date)
                    <li>
                        <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.appointment_date') }}</span>
                        <span class="account-order-sidebar__value">{{ $order->appointment_date->format('l, d M Y') }}</span>
                    </li>
                @endif
                @if ($order->appointment_time)
                    <li class="account-order-sidebar__li--appointment-time">
                        <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.appointment_time') }}</span>
                        <div class="account-order-sidebar__value-row">
                            <span class="account-order-sidebar__value">{{ $order->appointment_time }}</span>

                            @if ($canNotifyBeautician)
                                <form
                                    action="{{ route('account.orders.notify_beautician', $order->id) }}"
                                    method="POST"
                                    class="account-order-sidebar__notify-form"
                                >
                                    @csrf
                                    <button
                                        type="submit"
                                        class="account-order-sidebar__notify-btn"
                                        title="{{ trans('storefront::account.view_order.notify_beautician_hint') }}"
                                    >
                                        <i class="lab la-whatsapp" aria-hidden="true"></i>
                                        {{ trans('storefront::account.view_order.notify') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    @endif
</div>
