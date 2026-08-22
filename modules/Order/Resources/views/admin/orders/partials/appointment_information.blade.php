@php
    $bookings = isset($treatmentBookings) && $treatmentBookings instanceof \Illuminate\Support\Collection
        ? $treatmentBookings
        : collect($treatmentBooking ? [$treatmentBooking] : []);
@endphp

@if ($order->hasAppointmentDetails() || $order->beautician || $order->spaBranch || $bookings->isNotEmpty())
    <div class="order-show__card order-show__card--appointment">
        <div class="order-show__card-head">
            <h5><i class="fa fa-calendar-check-o" aria-hidden="true"></i> {{ trans('order::orders.appointment_information') }}</h5>
        </div>

        @if ($order->spaBranch)
            <div class="order-show__appt-slot">
                <span class="order-show__appt-slot-label">{{ trans('order::orders.spa_branch') }}</span>
                <strong>{{ $order->spaBranch->name }}</strong>
            </div>
        @endif

        @forelse ($bookings as $booking)
            <div class="order-show__appt-booking" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #eee;">
                <strong>{{ $booking->product?->name ?? trans('order::orders.appointment_information') }}</strong>
                <span class="badge {{ treatment_status_badge_class($booking->status) }}">{{ $booking->treatmentStatusLabel() }}</span>

                @if ($booking->isTbaSchedule())
                    <div class="order-show__appt-slot">
                        <span class="order-show__appt-slot-label">{{ trans('treatmentreservation::admin.tba.badge') }}</span>
                        <strong>{{ trans('treatmentreservation::admin.tba.badge') }}</strong>
                    </div>
                @else
                    <div class="order-show__appt-schedule">
                        @if ($booking->appointment_date)
                            <div class="order-show__appt-slot">
                                <span class="order-show__appt-slot-label">{{ trans('order::orders.appointment_date') }}</span>
                                <strong>{{ $booking->appointment_date->format('d M Y') }}</strong>
                            </div>
                        @endif
                        @if ($booking->appointment_time)
                            <div class="order-show__appt-slot">
                                <span class="order-show__appt-slot-label">{{ trans('order::orders.appointment_time') }}</span>
                                <strong>{{ $booking->displayAppointmentTime() }}</strong>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($booking->beautician)
                    <div class="order-show__beautician-card" style="margin-top: 8px;">
                        @if ($booking->beautician->profile_image->exists)
                            <img src="{{ $booking->beautician->profile_image->path }}" alt="" class="order-show__avatar order-show__avatar--lg">
                        @else
                            <span class="order-show__avatar order-show__avatar--lg order-show__avatar--initial" style="background-color: {{ $booking->beautician->profile_color ?? '#6366f1' }}">
                                {{ strtoupper(mb_substr($booking->beautician->name, 0, 1)) }}
                            </span>
                        @endif
                        <div>
                            <span class="order-show__beautician-label">{{ trans('order::orders.beautician') }}</span>
                            <strong>{{ $booking->beautician->name }}</strong>
                        </div>
                    </div>
                @endif

                @if ($booking->beautician_notes)
                    <div class="order-show__note-box">
                        <span class="order-show__note-box-label">{{ trans('treatmentreservation::admin.calendar.preview_beautician_notes') }}</span>
                        <p class="order-show__prewrap">{{ $booking->beautician_notes }}</p>
                    </div>
                @endif
            </div>
        @empty
            @if ($order->appointment_date || $order->appointment_time || $order->beautician)
                <div class="order-show__appt-schedule">
                    @if ($order->appointment_date)
                        <div class="order-show__appt-slot">
                            <span class="order-show__appt-slot-label">{{ trans('order::orders.appointment_date') }}</span>
                            <strong>{{ $order->appointment_date->format('d M Y') }}</strong>
                        </div>
                    @endif
                    @if ($order->appointment_time)
                        <div class="order-show__appt-slot">
                            <span class="order-show__appt-slot-label">{{ trans('order::orders.appointment_time') }}</span>
                            <strong>{{ $order->displayAppointmentTime() }}</strong>
                        </div>
                    @endif
                </div>
                @if ($order->beautician)
                    <div class="order-show__beautician-card">
                        <div>
                            <span class="order-show__beautician-label">{{ trans('order::orders.beautician') }}</span>
                            <strong>{{ $order->beautician->name }}</strong>
                        </div>
                    </div>
                @endif
            @endif
        @endforelse
    </div>
@endif
