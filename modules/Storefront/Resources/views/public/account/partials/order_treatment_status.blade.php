@if (is_module_enabled('TreatmentReservation'))
    @if ($order->treatmentBooking)
        <span class="badge {{ treatment_status_badge_class($order->treatmentBooking->status) }}">
            {{ $order->treatmentBooking->treatmentStatusLabel() }}
        </span>
    @else
        <span class="my-orders-table__muted">—</span>
    @endif
@endif
