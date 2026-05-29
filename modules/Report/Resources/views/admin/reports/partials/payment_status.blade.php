@php
    use Modules\Report\Support\ReportFormatters;

    $paymentStatus = $paymentStatus ?? null;
    $paymentStatusClass = $paymentStatus ?: 'pending';
@endphp

<span class="report-payment-pill report-payment-pill--{{ $paymentStatusClass }}">
    {{ ReportFormatters::paymentStatus($paymentStatus) }}
</span>
