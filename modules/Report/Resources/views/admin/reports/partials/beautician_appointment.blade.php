@php
    use Modules\Report\Support\ReportFormatters;

    $row = $row ?? null;
    $beautician = trim((string) ($row->beautician_name ?? ''));
    $hasAppointment = ! empty($row->appointment_date);
    $appointment = $hasAppointment ? ReportFormatters::appointment($row) : null;
@endphp

<td class="report-cell--beautician-appointment">
    @if ($beautician !== '')
        <div class="report-cell__line report-cell__line--label">{{ $beautician }}</div>
    @endif
    @if ($hasAppointment)
        <div class="report-cell__line">{{ $appointment }}</div>
    @endif
    @if ($beautician === '' && ! $hasAppointment)
        <div class="report-cell__line">—</div>
    @endif
</td>
