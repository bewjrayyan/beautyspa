@extends('admin::layout')

@section('title', trans('beauticianreport::admin.analytics'))

@section('content_header')
    <div class="br-page-header">
        <div>
            <h3>{{ trans('beauticianreport::admin.analytics') }}</h3>
            <p class="br-page-subtitle">{{ trans('beauticianreport::admin.dashboard') }}</p>
        </div>
        <a href="{{ route('admin.beautician_reports.index', ['type' => 'treatment_sales_report']) }}" class="btn btn-default">
            {{ trans('beauticianreport::admin.view_reports') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="br-dashboard">
        <div class="fc-saas-stats">
            @include('admin::partials.fc_saas_stat', [
                'variant' => 'peach',
                'icon' => 'fa-line-chart',
                'label' => trans('beauticianreport::admin.stats.treatment_sales'),
                'value' => $analytics['totalTreatmentSales']->format(),
                'hint' => trans('beauticianreport::admin.stats.treatment_sales_hint'),
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'sky',
                'icon' => 'fa-shopping-cart',
                'label' => trans('beauticianreport::admin.stats.treatment_orders'),
                'value' => number_format($analytics['totalTreatmentOrders']),
                'hint' => trans('beauticianreport::admin.stats.treatment_orders_hint'),
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'mint',
                'icon' => 'fa-check-circle',
                'label' => trans('beauticianreport::admin.stats.completed'),
                'value' => number_format($analytics['completedTreatmentOrders']),
                'hint' => trans('beauticianreport::admin.stats.completed_hint'),
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'violet',
                'icon' => 'fa-calendar-check-o',
                'label' => trans('beauticianreport::admin.stats.today_appointments'),
                'value' => number_format($analytics['todayAppointments']),
                'hint' => trans('beauticianreport::admin.stats.today_appointments_hint'),
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'indigo',
                'icon' => 'fa-clock-o',
                'label' => trans('beauticianreport::admin.stats.upcoming'),
                'value' => number_format($analytics['upcomingAppointments']),
                'hint' => trans('beauticianreport::admin.stats.upcoming_hint'),
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'rose',
                'icon' => 'fa-user-md',
                'label' => trans('beauticianreport::admin.stats.active_beauticians'),
                'value' => number_format($analytics['activeBeauticians']),
                'hint' => trans('beauticianreport::admin.stats.active_beauticians_hint'),
            ])
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="br-panel">
                    <div class="br-panel-header">
                        <h4>{{ trans('beauticianreport::admin.charts.sales_trend') }}</h4>
                    </div>
                    <div class="br-panel-body">
                        <div class="br-chart-wrap br-chart-wrap--trend">
                            <canvas id="br-sales-trend-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="br-panel">
                    <div class="br-panel-header">
                        <h4>{{ trans('beauticianreport::admin.charts.by_beautician') }}</h4>
                    </div>
                    <div class="br-panel-body">
                        <div class="br-chart-wrap br-chart-wrap--doughnut">
                            <canvas id="br-sales-by-beautician-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5">
                <div class="br-panel">
                    <div class="br-panel-header">
                        <h4>{{ trans('beauticianreport::admin.panels.top_beauticians') }}</h4>
                    </div>
                    <div class="br-panel-body br-panel-body--flush">
                        <table class="table br-table">
                            <thead>
                                <tr>
                                    <th>{{ trans('beauticianreport::admin.table.beautician') }}</th>
                                    <th>{{ trans('beauticianreport::admin.table.orders') }}</th>
                                    <th class="text-right">{{ trans('beauticianreport::admin.table.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($analytics['topBeauticians'] as $row)
                                    <tr>
                                        <td>
                                            <strong>{{ $row->name }}</strong>
                                            @if ($row->job_title)
                                                <br><small class="text-muted">{{ $row->job_title }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $row->total_orders }}</td>
                                        <td class="text-right">{{ $row->total_sales->format() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">{{ trans('beauticianreport::admin.no_data') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="br-panel">
                    <div class="br-panel-header">
                        <h4>{{ trans('beauticianreport::admin.panels.recent_orders') }}</h4>
                    </div>
                    <div class="br-panel-body br-panel-body--flush">
                        <table class="table br-table">
                            <thead>
                                <tr>
                                    <th>{{ trans('beauticianreport::admin.table.order') }}</th>
                                    <th>{{ trans('beauticianreport::admin.table.customer') }}</th>
                                    <th>{{ trans('beauticianreport::admin.table.beautician') }}</th>
                                    <th>{{ trans('beauticianreport::admin.table.status') }}</th>
                                    <th class="text-right">{{ trans('beauticianreport::admin.table.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($analytics['recentTreatmentOrders'] as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order->id) }}">#{{ $order->id }}</a>
                                            @if ($order->appointment_date)
                                                <br><small>{{ $order->appointment_date->format('d M Y') }} {{ $order->appointment_time }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $order->customer_full_name }}</td>
                                        <td>{{ $order->beautician?->name ?? '—' }}</td>
                                        <td><span class="label label-default">{{ $order->status() }}</span></td>
                                        <td class="text-right">{{ $order->total->format() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">{{ trans('beauticianreport::admin.no_data') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if (count($analytics['statusBreakdown']))
            <div class="br-panel">
                <div class="br-panel-header">
                    <h4>{{ trans('beauticianreport::admin.charts.order_status') }}</h4>
                </div>
                <div class="br-panel-body">
                    <div class="br-status-bars">
                        @php $maxStatus = max(array_column($analytics['statusBreakdown'], 'count')) ?: 1; @endphp
                        @foreach ($analytics['statusBreakdown'] as $item)
                            <div class="br-status-row">
                                <span class="br-status-label">{{ $item['label'] }}</span>
                                <div class="br-status-track">
                                    <div class="br-status-fill" style="width: {{ round(($item['count'] / $maxStatus) * 100) }}%"></div>
                                </div>
                                <span class="br-status-count">{{ $item['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('globals')
    @vite([
        'modules/BeauticianReport/Resources/assets/admin/sass/main.scss',
        'modules/BeauticianReport/Resources/assets/admin/js/dashboard.js',
    ])
    <script>
        window.BeauticianReportCharts = {
            byBeautician: @json($salesByBeautician),
            salesTrendUrl: @json(route('admin.beautician_reports.analytics.sales_trend', ['days' => 30])),
        };
    </script>
@endpush
