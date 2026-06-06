@php
    $dash = $reportDashboard ?? [];
@endphp

@include('report::admin.reports.partials.overview_stats')

<div class="row report-charts-row">
    <div class="col-lg-{{ ($dash['hasBeautician'] ?? false) ? '5' : '12' }}">
        <div class="report-modern-panel report-chart-panel">
            <div class="report-panel-header">
                <h4>{{ trans('report::admin.charts.store_sales') }}</h4>
            </div>
            <div class="report-panel-body">
                <div class="report-chart-wrap">
                    <canvas id="report-store-sales-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if ($dash['hasBeautician'] ?? false)
        <div class="col-lg-4">
            <div class="report-modern-panel report-chart-panel">
                <div class="report-panel-header">
                    <h4>{{ trans('report::admin.charts.treatment_sales') }}</h4>
                </div>
                <div class="report-panel-body">
                    <div class="report-chart-wrap">
                        <canvas id="report-treatment-sales-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="report-modern-panel report-chart-panel">
                <div class="report-panel-header">
                    <h4>{{ trans('report::admin.charts.by_beautician') }}</h4>
                </div>
                <div class="report-panel-body">
                    <div class="report-chart-wrap report-chart-wrap--compact">
                        <canvas id="report-by-beautician-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@if ($showBeauticianAnalytics ?? false)
    <div class="report-modern-panel report-bookings-panel">
        <div class="report-panel-header report-panel-header--flex">
            <div>
                <h4>{{ trans('report::admin.panels.beautician_bookings') }}</h4>
                <p>{{ trans('report::admin.panels.beautician_bookings_help') }}</p>
            </div>
            <a href="{{ route('admin.reports.index', ['type' => 'beautician_bookings_report']) }}" class="btn btn-default btn-sm">
                {{ trans('report::admin.view_all_bookings') }}
            </a>
        </div>
        <div class="report-panel-body report-panel-body--flush">
            <div class="table-responsive">
                <table class="table report-bookings-table">
                    <thead>
                        <tr>
                            <th>{{ trans('report::admin.table.appointment') }}</th>
                            <th>{{ trans('report::admin.table.customer') }}</th>
                            <th>{{ trans('report::admin.table.beautician') }}</th>
                            @if (is_module_enabled('SpaBranch'))
                                <th>{{ trans('report::admin.table.spa_branch') }}</th>
                            @endif
                            <th>{{ trans('report::admin.table.contact') }}</th>
                            <th>{{ trans('report::admin.table.status') }}</th>
                            <th class="text-right">{{ trans('report::admin.table.total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($beauticianBookings ?? [] as $booking)
                            <tr>
                                <td>
                                    <strong>{{ $booking->appointment_date->format('d M Y') }}</strong>
                                    <br><span class="text-muted">{{ $booking->appointment_time }}</span>
                                </td>
                                <td>
                                    {{ $booking->customer_full_name }}
                                    <br><small class="text-muted">#{{ $booking->id }}</small>
                                </td>
                                <td>
                                    <strong>{{ $booking->beautician?->name ?? '—' }}</strong>
                                    @if ($booking->beautician?->job_title)
                                        <br><small class="text-muted">{{ $booking->beautician->job_title }}</small>
                                    @endif
                                </td>
                                @if (is_module_enabled('SpaBranch'))
                                    <td>{{ $booking->spaBranch?->name ?? '—' }}</td>
                                @endif
                                <td>
                                    @if ($booking->customer_phone)
                                        <div><i class="fa fa-phone"></i> {{ $booking->customer_phone }}</div>
                                    @endif
                                    @if ($booking->customer_email)
                                        <small class="text-muted">{{ $booking->customer_email }}</small>
                                    @endif
                                </td>
                                <td><span class="label label-default">{{ $booking->status() }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('admin.orders.show', $booking->id) }}">{{ $booking->total->format() }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ is_module_enabled('SpaBranch') ? 7 : 6 }}" class="text-center text-muted empty">{{ trans('report::admin.no_bookings') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
