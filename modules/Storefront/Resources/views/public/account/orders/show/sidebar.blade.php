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

    @include('storefront::public.account.orders.show.order_rewards', [
        'wrapperClass' => 'account-order-sidebar__rewards d-none d-lg-block',
    ])

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
                    : trans('storefront::account.view_order.appointment_details') }}
            </h3>

            @if ($order->spaBranch)
                <div class="account-order-sidebar__appointment-location">
                    <span class="account-order-sidebar__appointment-icon" aria-hidden="true">
                        <i class="las la-map-marker"></i>
                    </span>
                    <span>
                        <span class="account-order-sidebar__label">{{ trans('storefront::account.view_order.spa_branch') }}</span>
                        <strong class="account-order-sidebar__appointment-location-name">{{ $order->spaBranch->name }}</strong>
                    </span>
                </div>
            @endif

            <div class="account-order-sidebar__bookings">
                @foreach ($treatmentBookings as $booking)
                    @php
                        $appointmentBeautician = $booking->beautician ?? $order->beautician;
                        $appointmentAvatarUrl = $appointmentBeautician?->displayAvatarUrl();
                    @endphp

                    <article class="account-order-sidebar__booking">
                        <div class="account-order-sidebar__booking-head">
                            @if ($appointmentBeautician)
                                <span @class([
                                    'account-order-sidebar__avatar',
                                    'account-order-sidebar__avatar--photo' => $appointmentAvatarUrl,
                                    'account-order-sidebar__avatar--initial' => ! $appointmentAvatarUrl,
                                ]) @unless($appointmentAvatarUrl) style="background-color: {{ $appointmentBeautician->profile_color ?? 'var(--color-primary, #f274ac)' }}" @endunless>
                                    @if ($appointmentAvatarUrl)
                                        <img
                                            src="{{ $appointmentAvatarUrl }}"
                                            alt="{{ $appointmentBeautician->name }}"
                                            width="48"
                                            height="48"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    @else
                                        {{ $appointmentBeautician->initials }}
                                    @endif
                                </span>

                                <span class="account-order-sidebar__booking-person">
                                    <strong class="account-order-sidebar__beautician-name">{{ $appointmentBeautician->name }}</strong>
                                    @if ($appointmentBeautician->job_title)
                                        <span class="account-order-sidebar__beautician-role">{{ $appointmentBeautician->job_title }}</span>
                                    @endif
                                </span>
                            @endif

                            <span class="badge {{ treatment_status_badge_class($booking->status) }}">
                                {{ $booking->treatmentStatusLabel() }}
                            </span>
                        </div>

                        <div class="account-order-sidebar__booking-treatment">
                            <i class="las la-spa" aria-hidden="true"></i>
                            <strong>{{ $booking->product?->name ?? trans('storefront::account.view_order.treatment_line') }}</strong>
                        </div>

                        <dl class="account-order-sidebar__schedule">
                            @if ($booking->isTbaSchedule())
                                <div class="account-order-sidebar__schedule-item account-order-sidebar__schedule-item--wide">
                                    <dt><i class="las la-calendar" aria-hidden="true"></i>{{ trans('storefront::account.view_order.appointment_date') }}</dt>
                                    <dd>{{ trans('treatmentreservation::admin.tba.badge') }}</dd>
                                </div>
                            @else
                                @if ($booking->appointment_date)
                                    <div class="account-order-sidebar__schedule-item">
                                        <dt><i class="las la-calendar" aria-hidden="true"></i>{{ trans('storefront::account.view_order.appointment_date') }}</dt>
                                        <dd>{{ $booking->appointment_date->format('l, d M Y') }}</dd>
                                    </div>
                                @endif
                                @if ($booking->appointment_time)
                                    <div class="account-order-sidebar__schedule-item">
                                        <dt><i class="las la-clock" aria-hidden="true"></i>{{ trans('storefront::account.view_order.appointment_time') }}</dt>
                                        <dd>{{ $booking->displayAppointmentTime() }}</dd>
                                    </div>
                                @endif
                            @endif
                        </dl>
                    </article>
                @endforeach
            </div>

            @if ($canNotifyBeautician)
                <form
                    action="{{ route('account.orders.notify_beautician', $order->id) }}"
                    method="POST"
                    class="account-order-sidebar__notify-form account-order-sidebar__notify-form--standalone"
                >
                    @csrf
                    <button
                        type="submit"
                        class="account-order-sidebar__notify-btn account-order-sidebar__notify-btn--wide"
                        title="{{ trans('storefront::account.view_order.notify_beautician_hint') }}"
                    >
                        <i class="lab la-whatsapp" aria-hidden="true"></i>
                        {{ trans('storefront::account.view_order.remind_beautician') }}
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>
