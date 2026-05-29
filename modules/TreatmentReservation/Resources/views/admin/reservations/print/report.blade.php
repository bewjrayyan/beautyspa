<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ trans('treatmentreservation::admin.reports.pdf_title') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1e293b; margin: 24px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .meta { color: #64748b; margin-bottom: 20px; }
        .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px; }
        .summary-card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
        .summary-card strong { display: block; font-size: 18px; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f8fafc; }
        h2 { font-size: 14px; margin: 24px 0 8px; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 16px;">
        <button onclick="window.print()">{{ trans('treatmentreservation::admin.reports.print_pdf') }}</button>
    </div>

    <h1>{{ trans('treatmentreservation::admin.reports.pdf_title') }}</h1>
    <p class="meta">
        {{ setting('store_name') }} ·
        {{ trans('treatmentreservation::admin.reports.from') }} {{ $from }}
        — {{ trans('treatmentreservation::admin.reports.to') }} {{ $to }}
        · {{ $generatedAt->format('d M Y H:i') }}
    </p>

    <div class="summary">
        <div class="summary-card">
            {{ trans('treatmentreservation::admin.reports.total') }}
            <strong>{{ number_format($summary['total']) }}</strong>
        </div>
        <div class="summary-card">
            {{ trans('treatmentreservation::admin.kanban.completed') }}
            <strong>{{ number_format($summary['completed']) }}</strong>
        </div>
        <div class="summary-card">
            {{ trans('treatmentreservation::admin.reports.canceled') }}
            <strong>{{ number_format($summary['canceled']) }}</strong>
        </div>
        <div class="summary-card">
            {{ trans('treatmentreservation::admin.reports.revenue') }}
            <strong>{{ $summary['revenue']->format() }}</strong>
        </div>
    </div>

    <h2>{{ trans('treatmentreservation::admin.reports.beautician_breakdown') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ trans('treatmentreservation::admin.filters.beautician') }}</th>
                <th>{{ trans('treatmentreservation::admin.kanban.completed') }}</th>
                <th>{{ trans('treatmentreservation::admin.reports.revenue') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($breakdown as $row)
                <tr>
                    <td>{{ $row['beautician_name'] }}</td>
                    <td>{{ number_format($row['completed']) }}</td>
                    <td>{{ number_format($row['revenue'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">—</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>{{ trans('treatmentreservation::admin.reports.booking_list') }}</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ trans('treatmentreservation::admin.calendar.preview_customer') }}</th>
                <th>{{ trans('treatmentreservation::admin.calendar.preview_treatment') }}</th>
                <th>{{ trans('treatmentreservation::admin.filters.beautician') }}</th>
                <th>{{ trans('treatmentreservation::admin.calendar.preview_date') }}</th>
                <th>{{ trans('treatmentreservation::admin.calendar.preview_time') }}</th>
                <th>{{ trans('treatmentreservation::admin.calendar.preview_status') }}</th>
                <th>{{ trans('treatmentreservation::admin.reports.revenue') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $booking)
                <tr>
                    <td>{{ $booking->id }}</td>
                    <td>{{ $booking->customer_full_name }}</td>
                    <td>{{ $booking->product?->name }}</td>
                    <td>{{ $booking->beautician?->name ?? '—' }}</td>
                    <td>{{ $booking->appointment_date?->format('d M Y') }}</td>
                    <td>{{ $booking->appointment_time }}</td>
                    <td>{{ trans('treatmentreservation::admin.kanban.' . $booking->status) }}</td>
                    <td>{{ number_format((float) $booking->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
