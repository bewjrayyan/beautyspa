<div class="table-responsive d-none d-lg-block">
    <table class="table table-borderless my-appointments-table">
        <thead>
        <tr>
            <th>{{ trans('treatmentreservation::public.treatment') }}</th>
            <th>{{ trans('treatmentreservation::public.date') }}</th>
            <th>{{ trans('treatmentreservation::public.time') }}</th>
            <th>{{ trans('treatmentreservation::public.beautician') }}</th>
            <th>{{ trans('storefront::account.status') }}</th>
            <th>{{ trans('storefront::account.action') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($bookings as $booking)
            @php
                $statusKey = 'treatmentreservation::public.statuses.' . $booking->status;
                $statusLabel = trans()->has($statusKey)
                    ? trans($statusKey)
                    : ucfirst(str_replace('_', ' ', $booking->status));
            @endphp
            <tr class="my-appointments-table__row" data-booking-id="{{ $booking->id }}">
                <td class="my-appointments-table__treatment">{{ $booking->product?->name }}</td>
                <td>{{ $booking->appointment_date?->format('d M Y') ?? '—' }}</td>
                <td>{{ $booking->appointment_time ?? '—' }}</td>
                <td>{{ $booking->beautician?->name ?? '—' }}</td>
                <td>
                    <span class="account-appointment-card__status account-appointment-card__status--{{ $booking->status }}">
                        {{ $statusLabel }}
                    </span>
                </td>
                <td class="my-appointments-table__actions">
                    <button type="button" class="btn btn-default btn-sm js-reschedule-toggle">
                        <i class="las la-edit"></i>
                        {{ trans('treatmentreservation::public.reschedule') }}
                    </button>
                    <button type="button" class="btn btn-danger btn-sm js-cancel-booking">
                        <i class="las la-times-circle"></i>
                        {{ trans('treatmentreservation::public.cancel') }}
                    </button>
                </td>
            </tr>
            <tr class="my-appointments-table__expand hide js-reschedule-row" data-booking-id="{{ $booking->id }}">
                <td colspan="6">
                    @include('treatmentreservation::public.booking.partials.reschedule_form', ['booking' => $booking])
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
