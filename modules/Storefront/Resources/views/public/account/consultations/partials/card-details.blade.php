@php
    $booking = $consultation->treatmentBooking;
    $order = $consultation->order;
    $orderProduct = $consultation->orderProduct;
    $treatmentName = $orderProduct?->nameWithSelections()
        ?: $booking?->product?->name
        ?: $consultation->product?->name
        ?: trans('account::consultation.treatment_not_available');
    $appointmentDate = $booking?->appointment_date ?: $order?->appointment_date;
    $appointmentTime = $booking?->appointmentTimeRange() ?: $order?->appointment_time;
    $branchName = $order?->spaBranch?->name ?: $booking?->spaBranchLabel();
    $beauticianName = $consultation->beautician?->name ?: $booking?->beautician?->name ?: $order?->beautician?->name;
@endphp

<div class="consultation-card__treatment">
    <span><i class="las la-spa" aria-hidden="true"></i>{{ trans('account::consultation.purchased_treatment') }}</span>
    <strong>{{ $treatmentName }}</strong>
</div>

<div class="consultation-card__meta">
    @if ($consultation->order_id)
        <span><i class="las la-receipt" aria-hidden="true"></i>{{ trans('account::consultation.order_reference', ['id' => $consultation->order_id]) }}</span>
    @endif

    @if ($appointmentDate || $appointmentTime)
        <span class="consultation-card__meta-appointment">
            <i class="las la-calendar-check" aria-hidden="true"></i>
            @if ($appointmentDate){{ $appointmentDate->format('d M Y') }}@endif
            @if ($appointmentTime){{ $appointmentDate ? ' · ' : '' }}{{ $appointmentTime }}@endif
        </span>
    @else
        <span><i class="las la-calendar-times" aria-hidden="true"></i>{{ trans('account::consultation.appointment_pending') }}</span>
    @endif

    @if ($branchName)
        <span><i class="las la-store" aria-hidden="true"></i>{{ $branchName }}</span>
    @endif

    @if ($beauticianName)
        <span><i class="las la-user-nurse" aria-hidden="true"></i>{{ $beauticianName }}</span>
    @endif

    @if (! empty($showSubmitted) && $consultation->submitted_at)
        <span><i class="las la-check-circle" aria-hidden="true"></i>{{ trans('account::consultation.submitted', ['date' => $consultation->submitted_at->format('d M Y, H:i')]) }}</span>
    @endif
</div>
