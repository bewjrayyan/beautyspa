@php
    use Modules\Report\Support\ReportFormatters;
@endphp

<li>
    @if ($line->trashed())
        <div class="report-cell__line report-cell__line--label">{{ $line->name }}</div>
        <div class="report-cell__line report-order-lines__meta">{{ ReportFormatters::orderProductLineMeta($line) }}</div>
    @else
        <div class="report-cell__line report-cell__line--label">
            <a href="{{ route('admin.products.edit', $line->product_id) }}">{{ $line->name }}</a>
        </div>
        <div class="report-cell__line report-order-lines__meta">{{ ReportFormatters::orderProductLineMeta($line) }}</div>
    @endif
</li>
