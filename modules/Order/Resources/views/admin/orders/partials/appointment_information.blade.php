@if ($order->hasAppointmentDetails() || $order->beautician || $order->spaBranch || ! empty($treatmentBooking?->beautician_notes))
    <div class="order-show__card order-show__card--appointment">
        <div class="order-show__card-head">
            <h5><i class="fa fa-calendar-check-o" aria-hidden="true"></i> {{ trans('order::orders.appointment_information') }}</h5>
        </div>

        @if ($order->appointment_date || $order->appointment_time)
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
                        <strong>{{ $order->appointment_time }}</strong>
                    </div>
                @endif
            </div>
        @endif

        @if ($order->spaBranch)
            <div class="order-show__appt-slot">
                <span class="order-show__appt-slot-label">{{ trans('order::orders.spa_branch') }}</span>
                <strong>{{ $order->spaBranch->name }}</strong>
            </div>
        @endif

        @if ($order->beautician)
            <div class="order-show__beautician-card">
                @if ($order->beautician->profile_image->exists)
                    <img src="{{ $order->beautician->profile_image->path }}" alt="" class="order-show__avatar order-show__avatar--lg">
                @else
                    <span class="order-show__avatar order-show__avatar--lg order-show__avatar--initial" style="background-color: {{ $order->beautician->profile_color ?? '#6366f1' }}">
                        {{ strtoupper(mb_substr($order->beautician->name, 0, 1)) }}
                    </span>
                @endif
                <div>
                    <span class="order-show__beautician-label">{{ trans('order::orders.beautician') }}</span>
                    <strong>{{ $order->beautician->name }}</strong>
                    @if ($order->beautician->job_title)
                        <small>{{ $order->beautician->job_title }}</small>
                    @endif
                </div>
            </div>
        @endif

        @if (! empty($treatmentBooking?->beautician_notes))
            <div class="order-show__note-box">
                <span class="order-show__note-box-label">{{ trans('treatmentreservation::admin.calendar.preview_beautician_notes') }}</span>
                <p class="order-show__prewrap">{{ $treatmentBooking->beautician_notes }}</p>
            </div>
        @endif
    </div>
@endif
