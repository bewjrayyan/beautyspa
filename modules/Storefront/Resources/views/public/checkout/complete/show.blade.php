@extends('storefront::public.layout')

@section('content')
    <section class="order-complete-wrap">
        <div class="container">
            @if (session('error'))
                <div class="order-complete-alert order-complete-alert-error">
                    {{ session('error') }}
                </div>
            @endif

            <div class="order-complete-card">
                <div class="order-complete-hero">
                    <div class="order-complete-icon-wrap">
                        <svg class="checkmark checkmark--success" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52" aria-hidden="true">
                            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>

                    <h1 class="order-complete-title">{{ trans('storefront::order_complete.order_placed') }}</h1>
                    <p class="order-complete-subtitle">{{ trans('storefront::order_complete.booking_confirmed_subtitle') }}</p>
                    <p class="order-complete-order-id">{!! trans('storefront::order_complete.your_order_has_been_placed', ['id' => $order->id]) !!}</p>
                </div>

                @if ($hasTreatmentBooking)
                    <div class="order-complete-section" id="booking-details">
                        <h2 class="order-complete-section-title">
                            <i class="las la-spa"></i>
                            {{ trans('storefront::order_complete.booking_details') }}
                        </h2>

                        <div class="order-complete-details-grid">
                            @if ($order->beautician)
                                <div class="order-complete-detail">
                                    <span class="order-complete-detail-label">{{ trans('storefront::order_complete.beautician') }}</span>
                                    <span class="order-complete-detail-value">
                                        @if ($order->beautician->profile_image->exists)
                                            <img
                                                src="{{ $order->beautician->profile_image->path }}"
                                                alt=""
                                                class="order-complete-beautician-avatar"
                                            >
                                        @else
                                            <span
                                                class="order-complete-beautician-initial"
                                                style="background-color: {{ $order->beautician->profile_color ?? '#22c55e' }}"
                                            >{{ strtoupper(mb_substr($order->beautician->name, 0, 1)) }}</span>
                                        @endif
                                        {{ $order->beautician->name }}
                                        @if ($order->beautician->job_title)
                                            <small>{{ $order->beautician->job_title }}</small>
                                        @endif
                                    </span>
                                </div>
                            @endif

                            @if ($order->appointment_date)
                                <div class="order-complete-detail">
                                    <span class="order-complete-detail-label">{{ trans('storefront::order_complete.appointment_date') }}</span>
                                    <span class="order-complete-detail-value">{{ $order->appointment_date->format('l, d M Y') }}</span>
                                </div>
                            @endif

                            @if ($order->appointment_time)
                                <div class="order-complete-detail">
                                    <span class="order-complete-detail-label">{{ trans('storefront::order_complete.appointment_time') }}</span>
                                    <span class="order-complete-detail-value">{{ $order->appointment_time }}</span>
                                </div>
                            @endif

                            <div class="order-complete-detail">
                                <span class="order-complete-detail-label">{{ trans('storefront::order_complete.customer') }}</span>
                                <span class="order-complete-detail-value">
                                    {{ $order->customer_full_name }}<br>
                                    <small>{{ $order->customer_email }} · {{ $order->customer_phone }}</small>
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="order-complete-section" id="order-details">
                    <h2 class="order-complete-section-title">
                        <i class="las la-receipt"></i>
                        {{ trans('storefront::order_complete.order_summary') }}
                    </h2>

                    <ul class="order-complete-items">
                        @foreach ($order->products as $line)
                            <li class="order-complete-item">
                                <div class="order-complete-item-info">
                                    <span class="order-complete-item-name">{{ $line->name }}</span>
                                    @if ($line->hasAnyVariation())
                                        <span class="order-complete-item-meta">
                                            {{ $line->variations->pluck('value')->implode(', ') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="order-complete-item-qty">
                                    {{ trans('storefront::order_complete.qty') }}: {{ $line->qty }}
                                </div>
                                <div class="order-complete-item-price">
                                    {{ $line->line_total->convertToCurrentCurrency()->format() }}
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="order-complete-totals">
                        <div class="order-complete-total-row">
                            <span>{{ trans('storefront::order_complete.payment_method') }}</span>
                            <span>{{ $order->payment_method }}</span>
                        </div>
                        <div class="order-complete-total-row order-complete-total-row--grand">
                            <span>{{ trans('storefront::order_complete.order_total') }}</span>
                            <span>{{ $order->total->convertToCurrentCurrency()->format() }}</span>
                        </div>
                    </div>
                </div>

                @if (! empty($orderRewards))
                    @include('loyalty::public.order_complete.rewards', ['orderRewards' => $orderRewards])
                @endif

                <div class="order-complete-actions">
                    <a
                        href="{{ route('checkout.complete.invoice') }}"
                        class="btn btn-primary order-complete-btn"
                        target="_blank"
                        rel="noopener"
                    >
                        <i class="las la-file-invoice"></i>
                        {{ trans('storefront::order_complete.view_invoice') }}
                    </a>

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
