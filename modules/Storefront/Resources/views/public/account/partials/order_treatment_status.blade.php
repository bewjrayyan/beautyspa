@if (is_module_enabled('TreatmentReservation'))
    @php
        $bookings = $order->relationLoaded('treatmentBookings')
            ? $order->treatmentBookings
            : collect($order->treatmentBooking ? [$order->treatmentBooking] : []);
        $count = $bookings->count();
    @endphp
    @if ($count > 1)
        <span class="badge badge-info">{{ trans('storefront::account.orders.appointments_count', ['count' => $count]) }}</span>
    @elseif ($order->treatmentBooking)
        <span class="badge {{ treatment_status_badge_class($order->treatmentBooking->status) }}">
            {{ $order->treatmentBooking->treatmentStatusLabel() }}
        </span>
    @else
        <span class="my-orders-table__muted">—</span>
    @endif
@endif
