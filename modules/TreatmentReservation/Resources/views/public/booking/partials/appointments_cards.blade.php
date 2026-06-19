<div class="account-appointment-cards d-lg-none">
    @foreach ($bookings as $booking)
        @php
            $statusKey = 'treatmentreservation::public.statuses.' . $booking->status;
            $statusLabel = trans()->has($statusKey)
                ? trans($statusKey)
                : ucfirst(str_replace('_', ' ', $booking->status));
        @endphp

        <article class="account-appointment-card" data-booking-id="{{ $booking->id }}">
            <div class="account-appointment-card__top">
                <h3 class="account-appointment-card__title">{{ $booking->product?->name }}</h3>
                <span class="account-appointment-card__status account-appointment-card__status--{{ $booking->status }}">
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="account-appointment-card__meta">
                <span>
                    <i class="las la-calendar" aria-hidden="true"></i>
                    {{ $booking->appointment_date?->format('d M Y') }}
                </span>
                <span>
                    <i class="las la-clock" aria-hidden="true"></i>
                    {{ $booking->appointment_time }}
                </span>
                @if ($booking->beautician?->name)
                    <span>
                        <i class="las la-user" aria-hidden="true"></i>
                        {{ $booking->beautician->name }}
                    </span>
                @endif
            </div>

            <div class="account-appointment-card__actions">
                <button type="button" class="btn btn-default btn-sm js-reschedule-toggle">
                    <i class="las la-edit"></i>
                    {{ trans('treatmentreservation::public.reschedule') }}
                </button>
                <button type="button" class="btn btn-danger btn-sm js-cancel-booking">
                    <i class="las la-times-circle"></i>
                    {{ trans('treatmentreservation::public.cancel') }}
                </button>
            </div>

            @include('treatmentreservation::public.booking.partials.reschedule_form', ['booking' => $booking])
        </article>
    @endforeach
</div>
