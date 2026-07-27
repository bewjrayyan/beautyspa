@php
    $context = app(\Modules\Account\Services\ConsultationContextService::class)->forDisplay($consultation);
    $treatmentName = $context['treatment_name'] ?: trans('account::consultation.treatment_not_available');
    $appointmentDate = filled($context['appointment_date'] ?? null)
        ? \Illuminate\Support\Carbon::parse($context['appointment_date'])
        : null;
    $appointmentTime = $context['appointment_time'] ?? null;
    $branchName = $context['branch_name'] ?? null;
    $beauticianName = $context['beautician_name'] ?? null;
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
