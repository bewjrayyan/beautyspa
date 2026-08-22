<div class="account-order-sidebar">
    <div class="account-order-sidebar__card account-order-sidebar__card--actions d-none d-lg-block">
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
                <a
                    href="{{ route('account.orders.invoice', $order->id) }}"
                    class="account-order-action-dropdown__item"
                    target="_blank"
                    rel="noopener"
                    @click="open = false"
                >
                    <i class="las la-file-invoice"></i>
                    {{ trans('storefront::account.view_order.download_invoice') }}
                </a>

                <a
                    href="{{ route('account.orders.receipt', $order->id) }}"
                    class="account-order-action-dropdown__item"
                    target="_blank"
                    rel="noopener"
                    @click="open = false"
                >
                    <i class="las la-receipt"></i>
                    {{ trans('storefront::account.view_order.download_receipt') }}
                </a>

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

    <div class="account-order-sidebar__mobile-actions d-lg-none">
        <a
            href="{{ route('account.orders.invoice', $order->id) }}"
            class="account-order-sidebar__mobile-action"
            target="_blank"
            rel="noopener"
        >
            <i class="las la-file-invoice"></i>
            <span>{{ trans('storefront::account.view_order.download_invoice') }}</span>
        </a>

        <a
            href="{{ route('account.orders.receipt', $order->id) }}"
            class="account-order-sidebar__mobile-action"
            target="_blank"
            rel="noopener"
        >
            <i class="las la-receipt"></i>
            <span>{{ trans('storefront::account.view_order.download_receipt') }}</span>
        </a>

        @if ($canNotifyBeautician)
            <button
                type="button"
                class="account-order-sidebar__mobile-action account-order-sidebar__mobile-action--whatsapp"
                onclick="this.nextElementSibling.submit()"
            >
                <i class="lab la-whatsapp"></i>
                <span>{{ trans('storefront::account.view_order.remind_beautician') }}</span>
            </button>

            <form
                action="{{ route('account.orders.notify_beautician', $order->id) }}"
                method="POST"
                class="d-none"
            >
                @csrf
            </form>
        @endif

        @if ($googleCalendarUrl ?? null)
            <button
                type="button"
                class="account-order-sidebar__mobile-action"
                onclick="window.open(@js($googleCalendarUrl), '_blank', 'noopener')"
            >
                <i class="lab la-google"></i>
                <span>{{ trans('storefront::order_complete.add_to_google_calendar') }}</span>
            </button>
        @endif
    </div>

    <div class="account-order-sidebar__card account-order-sidebar__card--payment d-none d-lg-block">
        <h3 class="account-order-sidebar__title">
            <i class="las la-credit-card"></i>
            {{ trans('storefront::account.view_order.payment_details') }}
        </h3>

        @include('storefront::public.account.orders.show.payment_details', ['variant' => 'full'])
    </div>

    @if ($hasTreatmentBooking)
        @php
            $treatmentBookings = $order->relationLoaded('treatmentBookings')
                ? $order->treatmentBookings
                : collect($order->treatmentBooking ? [$order->treatmentBooking] : []);
            $multipleAppointments = $treatmentBookings->count() > 1;
        @endphp

        <div class="account-order-sidebar__card account-order-sidebar__card--appointment">
            <h3 class="account-order-sidebar__title">
                <i class="las la-spa"></i>
                {{ $multipleAppointments
                    ? trans('storefront::account.view_order.appointments')
                    : trans('storefront::account.view_order.beautician') }}
            </h3>

            @if ($order->beautician && ! $multipleAppointments)
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

            @if ($multipleAppointments)
                <ul class="account-order-sidebar__list">
                    @if ($order->spaBranch)
                        <li>
                            <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.spa_branch') }}</span>
                            <span class="account-order-sidebar__value">{{ $order->spaBranch->name }}</span>
                        </li>
                    @endif
                </ul>

                @foreach ($treatmentBookings as $booking)
                    <div class="account-order-sidebar__booking" style="margin-top: 14px; padding-top: 14px; border-top: 1px solid rgba(0,0,0,.08);">
                        <strong class="account-order-sidebar__booking-title">{{ $booking->product?->name ?? trans('storefront::account.view_order.treatment_line') }}</strong>

                        @if ($booking->beautician)
                            <p class="account-order-sidebar__booking-meta">{{ $booking->beautician->name }}</p>
                        @endif

                        <ul class="account-order-sidebar__list">
                            @if ($booking->isTbaSchedule())
                                <li>
                                    <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.appointment_date') }}</span>
                                    <span class="account-order-sidebar__value">{{ trans('treatmentreservation::admin.tba.badge') }}</span>
                                </li>
                            @else
                                @if ($booking->appointment_date)
                                    <li>
                                        <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.appointment_date') }}</span>
                                        <span class="account-order-sidebar__value">{{ $booking->appointment_date->format('l, d M Y') }}</span>
                                    </li>
                                @endif
                                @if ($booking->appointment_time)
                                    <li>
                                        <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.appointment_time') }}</span>
                                        <span class="account-order-sidebar__value">{{ $booking->displayAppointmentTime() }}</span>
                                    </li>
                                @endif
                            @endif
                            <li>
                                <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.treatment_status') }}</span>
                                <span class="account-order-sidebar__value">
                                    <span class="badge {{ treatment_status_badge_class($booking->status) }}">
                                        {{ $booking->treatmentStatusLabel() }}
                                    </span>
                                </span>
                            </li>
                        </ul>
                    </div>
                @endforeach

                @if ($canNotifyBeautician)
                    <form
                        action="{{ route('account.orders.notify_beautician', $order->id) }}"
                        method="POST"
                        class="account-order-sidebar__notify-form"
                        style="margin-top: 12px;"
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
            @else
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
                                <span class="account-order-sidebar__value">{{ $order->displayAppointmentTime() }}</span>

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
                    @if (is_module_enabled('TreatmentReservation'))
                        <li>
                            <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.treatment_status') }}</span>
                            <span class="account-order-sidebar__value">
                                @if ($order->treatmentBooking)
                                    <span class="badge {{ treatment_status_badge_class($order->treatmentBooking->status) }}">
                                        {{ $order->treatmentBooking->treatmentStatusLabel() }}
                                    </span>
                                @else
                                    —
                                @endif
                            </span>
                        </li>
                    @endif
                </ul>
            @endif
        </div>
    @endif
</div>
