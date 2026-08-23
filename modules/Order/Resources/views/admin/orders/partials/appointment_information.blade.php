@php
    $bookings = isset($treatmentBookings) && $treatmentBookings instanceof \Illuminate\Support\Collection
        ? $treatmentBookings
        : collect($treatmentBooking ? [$treatmentBooking] : []);
@endphp

@if ($order->hasAppointmentDetails() || $order->beautician || $order->spaBranch || $bookings->isNotEmpty())
    <div id="order-fulfillment" class="order-show__card order-show__card--appointment">
        <div class="order-show__card-head">
            <h5><i class="fa fa-calendar-check-o" aria-hidden="true"></i> {{ trans('order::orders.appointment_information') }}</h5>
            @if ($bookings->isNotEmpty())
                <span class="order-show__appointment-count">{{ trans_choice('order::orders.appointments_count', $bookings->count(), ['count' => $bookings->count()]) }}</span>
            @endif
        </div>

        @if ($order->spaBranch)
            <div class="order-show__appt-slot">
                <span class="order-show__appt-slot-label">{{ trans('order::orders.spa_branch') }}</span>
                <strong>{{ $order->spaBranch->name }}</strong>
            </div>
        @endif

        @forelse ($bookings as $booking)
            @php
                $treatmentLine = $booking->treatmentLineMeta();
                $canOpenJobSheet = auth()->user()?->hasAccess('admin.beauticians.edit')
                    || (auth()->user()?->isBeauticianOnly()
                        && (int) $booking->beautician?->user_id === (int) auth()->id());
            @endphp
            <article class="order-show__appt-booking">
                <div class="order-show__appt-booking-head">
                    <div>
                        <span class="order-show__appt-sequence">{{ trans('order::orders.treatment_sequence', [
                            'current' => $loop->iteration,
                            'total' => $loop->count,
                        ]) }}</span>
                        <strong class="order-show__appt-product-name">{{ $treatmentLine['product_name'] }}</strong>

                        @if (filled($treatmentLine['treatment_selection']))
                            <div class="order-show__appt-selection">
                                <span>{{ trans('order::orders.selected_treatment') }}</span>
                                <strong>{{ $treatmentLine['treatment_selection'] }}</strong>
                            </div>
                        @endif

                        @if ($booking->beautician)
                            <div class="order-show__appt-beautician">
                                @if ($booking->beautician->profile_image->exists)
                                    <img src="{{ $booking->beautician->profile_image->path }}" alt="" class="order-show__avatar order-show__avatar--compact">
                                @else
                                    <span class="order-show__avatar order-show__avatar--compact order-show__avatar--initial" style="background-color: {{ $booking->beautician->profile_color ?? '#6366f1' }}">
                                        {{ strtoupper(mb_substr($booking->beautician->name, 0, 1)) }}
                                    </span>
                                @endif
                                <div>
                                    <span>{{ trans('order::orders.beautician') }}</span>
                                    <strong>{{ $booking->beautician->name }}</strong>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="order-show__appt-statuses">
                        @if ($booking->beautician)
                            <span class="order-show__job-sheet-badge">
                                <i class="fa fa-briefcase" aria-hidden="true"></i>
                                {{ trans('order::orders.job_sheet_status') }}
                            </span>
                        @endif
                        <span class="badge {{ treatment_status_badge_class($booking->status) }}">{{ $booking->treatmentStatusLabel() }}</span>
                    </div>
                </div>

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

                @if ($booking->beautician && $canOpenJobSheet)
                    <div class="order-show__job-sheet-action">
                        <div>
                            <strong>{{ trans('order::orders.manage_job_sheet') }}</strong>
                            <span>{{ trans('order::orders.manage_job_sheet_help') }}</span>
                        </div>
                        <a
                            class="btn btn-default btn-sm"
                            href="{{ route('admin.beauticians.portal.calendar_page', [
                                'id' => $booking->beautician_id,
                                'focus' => 1,
                                'booking_id' => $booking->id,
                                'month' => $booking->appointment_date?->format('Y-m') ?? now()->format('Y-m'),
                            ]) }}"
                        >
                            <i class="fa fa-calendar" aria-hidden="true"></i>
                            {{ trans('order::orders.manage_job_sheet') }}
                            <i class="fa fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                @endif

                @if ($booking->beautician_notes)
                    <div class="order-show__note-box">
                        <span class="order-show__note-box-label">{{ trans('treatmentreservation::admin.calendar.preview_beautician_notes') }}</span>
                        <p class="order-show__prewrap">{{ $booking->beautician_notes }}</p>
                    </div>
                @endif
            </article>
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
