@php
    $booking = $booking ?? [];
    $statusKey = 'treatmentreservation::admin.kanban.' . ($booking['status'] ?? 'pending');
@endphp

<li
    class="tr-crm-appointment tr-crm-appointment--clickable"
    data-booking-id="{{ $booking['id'] ?? '' }}"
    role="button"
    tabindex="0"
    data-search="{{ strtolower(($booking['customer_name'] ?? '') . ' ' . ($booking['customer_phone'] ?? '') . ' ' . ($booking['customer_email'] ?? '') . ' ' . ($booking['treatment_name'] ?? '') . ' ' . ($booking['beautician_name'] ?? '')) }}"
>
    <span class="tr-crm-appointment__time">{{ $booking['appointment_time'] ?? '—' }}</span>
    <div class="tr-crm-appointment__main">
        <strong class="tr-crm-appointment__customer">{{ $booking['customer_name'] ?? '—' }}</strong>
        <span class="tr-crm-appointment__treatment">{{ $booking['treatment_name'] ?? '—' }}</span>
    </div>
    <div class="tr-crm-appointment__meta">
        <span
            class="tr-crm-status-pill tr-crm-status-pill--{{ $booking['status'] ?? 'pending' }}"
            style="--tr-status-color: {{ $booking['status_accent'] ?? '#94a3b8' }}"
        >
            {{ trans()->has($statusKey) ? trans($statusKey) : ($booking['status'] ?? '—') }}
        </span>
        @if (! empty($booking['beautician_name']))
            <span class="tr-crm-appointment__beautician">
                <span
                    class="tr-crm-appointment__avatar"
                    style="background-color: {{ $booking['beautician_color'] ?? '#6366f1' }}"
                >{{ $booking['beautician_initial'] ?? '?' }}</span>
                {{ $booking['beautician_name'] }}
            </span>
        @endif
    </div>
</li>
