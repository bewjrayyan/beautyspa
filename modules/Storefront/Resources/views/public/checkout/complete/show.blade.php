@extends('storefront::public.layout')

@section('content')
    @php
        $isBankTransferPending = $order->getRawOriginal('payment_method') === 'bank_transfer'
            && $order->payment_status === \Modules\Order\Entities\Order::PAYMENT_PENDING;
    @endphp

    <section class="order-complete-wrap">
        <div class="container">
            @if (session('error'))
                <div class="order-complete-alert order-complete-alert-error">
                    {{ session('error') }}
                </div>
            @endif

            <div class="order-complete-card">
                <div @class([
                    'order-complete-hero',
                    'order-complete-hero--pending' => $isBankTransferPending,
                ])>
                    <div class="order-complete-icon-wrap">
                        @if ($isBankTransferPending)
                            <span class="order-complete-pending-icon" aria-hidden="true">
                                <i class="las la-clock"></i>
                            </span>
                        @else
                            <svg class="checkmark checkmark--success" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52" aria-hidden="true">
                                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                            </svg>
                        @endif
                    </div>

                    <h1 class="order-complete-title">
                        {{ $isBankTransferPending
                            ? trans('storefront::order_complete.booking_pending')
                            : trans('storefront::order_complete.order_placed') }}
                    </h1>
                    <p class="order-complete-subtitle">
                        {{ $isBankTransferPending
                            ? trans('storefront::order_complete.booking_pending_subtitle')
                            : trans('storefront::order_complete.booking_confirmed_subtitle') }}
                    </p>
                    <p class="order-complete-order-id">{!! trans('storefront::order_complete.your_order_has_been_placed', ['id' => $order->id]) !!}</p>
                </div>

                @if ($hasTreatmentBooking)
                    @php
                        $treatmentBookings = $order->relationLoaded('treatmentBookings')
                            ? $order->treatmentBookings
                            : collect($order->treatmentBooking ? [$order->treatmentBooking] : []);
                        $multipleAppointments = $treatmentBookings->count() > 1;
                        $primaryBooking = $treatmentBookings->first();
                    @endphp

                    <div class="order-complete-section" id="booking-details">
                        <h2 class="order-complete-section-title">
                            <i class="las la-spa"></i>
                            {{ $multipleAppointments
                                ? trans('storefront::order_complete.appointments')
                                : trans('storefront::order_complete.booking_details') }}
                        </h2>

                        @if ($multipleAppointments)
                            @foreach ($treatmentBookings as $booking)
                                <div class="order-complete-booking">
                                    <h3 class="order-complete-booking-title">{{ $booking->product?->name ?? trans('storefront::order_complete.treatment_line') }}</h3>

                                    <div class="order-complete-details-grid order-complete-details-grid--two">
                                        @if ($booking->beautician)
                                            <div class="order-complete-detail">
                                                <span class="order-complete-detail-label">{{ trans('storefront::order_complete.beautician') }}</span>
                                                <span class="order-complete-detail-value">{{ $booking->beautician->name }}</span>
                                            </div>
                                        @endif

                                        <div class="order-complete-detail">
                                            <span class="order-complete-detail-label">{{ trans('storefront::order_complete.customer') }}</span>
                                            <span class="order-complete-detail-value">
                                                {{ $order->customer_full_name }}<br>
                                                <small>{{ $order->customer_email }} · {{ $order->customer_phone }}</small>
                                            </span>
                                        </div>

                                        @if ($booking->isTbaSchedule())
                                            <div class="order-complete-detail order-complete-detail--span">
                                                <span class="order-complete-detail-label">{{ trans('storefront::order_complete.appointment_date') }}</span>
                                                <span class="order-complete-detail-value">{{ trans('treatmentreservation::admin.tba.badge') }}</span>
                                            </div>
                                        @else
                                            @if ($booking->appointment_date)
                                                <div class="order-complete-detail">
                                                    <span class="order-complete-detail-label">{{ trans('storefront::order_complete.appointment_date') }}</span>
                                                    <span class="order-complete-detail-value">{{ $booking->appointment_date->format('l, d M Y') }}</span>
                                                </div>
                                            @endif
                                            @if ($booking->appointment_time)
                                                <div class="order-complete-detail">
                                                    <span class="order-complete-detail-label">{{ trans('storefront::order_complete.appointment_time') }}</span>
                                                    <span class="order-complete-detail-value">{{ $booking->displayAppointmentTime() }}</span>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            @php
                                $beautician = $primaryBooking?->beautician ?? $order->beautician;
                                $isTba = $primaryBooking
                                    ? $primaryBooking->isTbaSchedule()
                                    : (! $order->appointment_date && ! $order->appointment_time);
                                $appointmentDate = $primaryBooking?->appointment_date ?? $order->appointment_date;
                                $appointmentTime = $primaryBooking
                                    ? ($primaryBooking->appointment_time ? $primaryBooking->displayAppointmentTime() : null)
                                    : ($order->appointment_time ? $order->displayAppointmentTime() : null);
                            @endphp

                            <div class="order-complete-details-grid order-complete-details-grid--two">
                                @if ($beautician)
                                    <div class="order-complete-detail">
                                        <span class="order-complete-detail-label">{{ trans('storefront::order_complete.beautician') }}</span>
                                        <span class="order-complete-detail-value">
                                            @if ($beautician->profile_image->exists)
                                                <img
                                                    src="{{ $beautician->profile_image->path }}"
                                                    alt=""
                                                    class="order-complete-beautician-avatar"
                                                >
                                            @else
                                                <span
                                                    class="order-complete-beautician-initial"
                                                    style="background-color: {{ $beautician->profile_color ?? '#22c55e' }}"
                                                >{{ strtoupper(mb_substr($beautician->name, 0, 1)) }}</span>
                                            @endif
                                            {{ $beautician->name }}
                                            @if ($beautician->job_title)
                                                <small>{{ $beautician->job_title }}</small>
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                <div class="order-complete-detail">
                                    <span class="order-complete-detail-label">{{ trans('storefront::order_complete.customer') }}</span>
                                    <span class="order-complete-detail-value">
                                        {{ $order->customer_full_name }}<br>
                                        <small>{{ $order->customer_email }} · {{ $order->customer_phone }}</small>
                                    </span>
                                </div>

                                @if ($isTba)
                                    <div class="order-complete-detail order-complete-detail--span">
                                        <span class="order-complete-detail-label">{{ trans('storefront::order_complete.appointment_date') }}</span>
                                        <span class="order-complete-detail-value">{{ trans('treatmentreservation::admin.tba.badge') }}</span>
                                    </div>
                                @else
                                    @if ($appointmentDate)
                                        <div class="order-complete-detail">
                                            <span class="order-complete-detail-label">{{ trans('storefront::order_complete.appointment_date') }}</span>
                                            <span class="order-complete-detail-value">{{ $appointmentDate->format('l, d M Y') }}</span>
                                        </div>
                                    @endif
                                    @if ($appointmentTime)
                                        <div class="order-complete-detail">
                                            <span class="order-complete-detail-label">{{ trans('storefront::order_complete.appointment_time') }}</span>
                                            <span class="order-complete-detail-value">{{ $appointmentTime }}</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                <div class="order-complete-section" id="order-details">
                    <h2 class="order-complete-section-title">
                        <i class="las la-receipt"></i>
                        {{ trans('storefront::order_complete.order_summary') }}
                    </h2>

                    @include('storefront::public.checkout.complete.partials.order_items', ['order' => $order])
                    @include('storefront::public.checkout.complete.partials.order_totals', ['order' => $order])
                </div>

                @if (! empty($orderRewards))
                    @include('loyalty::public.order_complete.rewards', ['orderRewards' => $orderRewards])
                @endif

                <div class="order-complete-actions">
                    <div class="order-complete-actions__primary">
                        <a
                            href="{{ route('checkout.complete.invoice') }}"
                            class="btn btn-primary order-complete-btn"
                            target="_blank"
                            rel="noopener"
                        >
                            <i class="las la-file-invoice"></i>
                            {{ trans('storefront::order_complete.view_invoice') }}
                        </a>
                        <a
                            href="{{ route('checkout.complete.invoice', ['print' => 1]) }}"
                            class="btn btn-default order-complete-btn order-complete-btn--print"
                            target="_blank"
                            rel="noopener"
                        >
                            <i class="las la-print"></i>
                            {{ trans('storefront::order_complete.print_invoice') }}
                        </a>
                    </div>

                    <div class="order-complete-actions__secondary">
                        @auth
                            <a
                                href="{{ route('account.orders.show', $order->id) }}"
                                class="btn btn-default order-complete-btn"
                            >
                                <i class="las la-list-alt"></i>
                                {{ trans('storefront::order_complete.view_order_details') }}
                            </a>
                        @else
                            <a href="#order-details" class="btn btn-default order-complete-btn">
                                <i class="las la-list-alt"></i>
                                {{ trans('storefront::order_complete.view_order_details') }}
                            </a>
                        @endauth

                        @if ($hasTreatmentBooking && app('modules')->isEnabled('TreatmentReservation'))
                            <a
                                href="{{ route('treatment_reservations.booking.lookup') }}"
                                class="btn btn-default order-complete-btn"
                            >
                                <i class="las la-calendar-check"></i>
                                {{ trans('storefront::order_complete.manage_my_appointment') }}
                            </a>
                        @endif

                        @if ($canNotifyBeautician)
                            <form
                                action="{{ route('checkout.complete.notify_beautician') }}"
                                method="POST"
                                class="order-complete-action-form"
                            >
                                @csrf
                                <button type="submit" class="btn btn-default order-complete-btn">
                                    <i class="lab la-whatsapp"></i>
                                    {{ trans('storefront::order_complete.notify_beautician') }}
                                </button>
                            </form>
                        @endif

                        @if ($googleCalendarUrl)
                            <a
                                href="{{ $googleCalendarUrl }}"
                                class="btn btn-default order-complete-btn"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <i class="lab la-google"></i>
                                {{ trans('storefront::order_complete.add_to_google_calendar') }}
                            </a>
                        @endif
                    </div>

                    <a href="{{ route('home') }}" class="btn btn-default order-complete-btn order-complete-btn--ghost">
                        <i class="las la-shopping-bag"></i>
                        {{ trans('storefront::order_complete.continue_shopping') }}
                    </a>
                </div>

                @guest
                    <p class="order-complete-guest-note">
                        <a href="{{ route('login') }}">{{ trans('storefront::order_complete.login_to_view_order') }}</a>
                    </p>
                @endguest
            </div>
        </div>
    </section>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/checkout/complete/main.scss',
    ])
@endpush
