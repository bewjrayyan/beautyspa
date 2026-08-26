@php
    $hasTreatmentModule = is_module_enabled('TreatmentReservation');
    $bookings = collect();

    if ($hasTreatmentModule) {
        $bookings = $order->relationLoaded('treatmentBookings')
            ? $order->treatmentBookings
            : ($order->treatmentBookings ?? collect());

        if ($bookings->isEmpty() && ! empty($order->treatmentBooking)) {
            $bookings = collect([$order->treatmentBooking]);
        }
    }

    $singleBooking = $bookings->count() === 1 ? $bookings->first() : null;
@endphp
<div class="dropdown order-table-actions">
    <button
        type="button"
        class="btn btn-default btn-table-actions-toggle"
        aria-haspopup="true"
        aria-expanded="false"
        title="{{ trans('order::orders.table.actions') }}"
        data-order-id="{{ $order->id }}"
        data-current-status="{{ $order->status }}"
        data-current-payment-status="{{ $order->payment_status }}"
        data-show-url="{{ route('admin.orders.show', $order) }}"
        data-print-url="{{ route('admin.orders.print.show', $order) }}"
        data-receipt-url="{{ route('admin.orders.receipt.show', $order) }}"
        data-status-url="{{ route('admin.orders.status.update', $order) }}"
        data-payment-status-url="{{ route('admin.orders.payment_status.update', $order) }}"
        @if ($singleBooking)
            data-treatment-status-url="{{ route('admin.orders.treatment_status.update', $order) }}"
            data-current-treatment-status="{{ $singleBooking->status }}"
        @elseif ($bookings->count() > 1)
            data-treatment-manage-url="{{ route('admin.orders.show', $order) }}"
            data-treatment-count="{{ $bookings->count() }}"
        @endif
    >
        <span class="actions-dots" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </button>
</div>
