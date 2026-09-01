@php
    $hasAppointmentCard = $order->hasAppointmentDetails()
        || $order->beautician
        || $order->spaBranch
        || ! empty($treatmentBooking?->beautician_notes);
@endphp

@if ($hasAppointmentCard)
    <section id="order-overview" class="order-show__section">
        <div class="order-show__grid">
            @include('order::admin.orders.partials.appointment_information')
        </div>
    </section>
@endif
