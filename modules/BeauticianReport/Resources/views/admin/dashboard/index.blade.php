@extends('admin::layout')

@section('title', trans('beauticianreport::admin.analytics'))

@section('content_header')
    <div class="br-page-header">
        <div>
            <span class="br-eyebrow"><i class="fa fa-bar-chart" aria-hidden="true"></i> {{ trans('beauticianreport::admin.analytics') }}</span>
            <h3>{{ trans('beauticianreport::admin.dashboard') }}</h3>
            <p class="br-page-subtitle">{{ trans('beauticianreport::admin.dashboard_intro') }}</p>
        </div>
        <div class="br-page-actions">
            <span class="br-updated"><i class="fa fa-clock-o" aria-hidden="true"></i> {{ trans('beauticianreport::admin.updated') }} {{ now()->format('d M Y, H:i') }}</span>
            <a href="{{ route('admin.beautician_reports.index', ['type' => 'treatment_sales_report']) }}" class="btn btn-primary br-primary-action">
                <i class="fa fa-file-text-o" aria-hidden="true"></i> {{ trans('beauticianreport::admin.view_reports') }}
            </a>
        </div>
    </div>
@endsection

@section('content')
    @php
        $completionRate = $analytics['totalTreatmentOrders']
            ? round(($analytics['completedTreatmentOrders'] / $analytics['totalTreatmentOrders']) * 100)
            : 0;
    @endphp

    <div class="br-dashboard br-dashboard--editorial">
        <section class="br-summary-grid" aria-label="{{ trans('beauticianreport::admin.performance_summary') }}">
            <article class="br-revenue-card">
                <div class="br-revenue-topline">
                    <span>{{ trans('beauticianreport::admin.stats.treatment_sales') }}</span>
                    <span class="br-period">{{ trans('beauticianreport::admin.all_time') }}</span>
                </div>

                <div class="br-revenue-value">{{ $analytics['totalTreatmentSales']->format() }}</div>
                <p>{{ trans('beauticianreport::admin.stats.treatment_sales_hint') }}</p>

                <div class="br-revenue-footer">
                    <div class="br-completion-ring" style="--br-progress: {{ $completionRate }}%" role="img" aria-label="{{ trans('beauticianreport::admin.completion_rate') }}: {{ $completionRate }}%">
                        <span>{{ $completionRate }}<small>%</small></span>
                    </div>
                    <div>
                        <strong>{{ trans('beauticianreport::admin.completion_rate') }}</strong>
                        <span>{{ number_format($analytics['completedTreatmentOrders']) }} / {{ number_format($analytics['totalTreatmentOrders']) }} {{ trans('beauticianreport::admin.stats.treatment_orders') }}</span>
                    </div>
                </div>
            </article>

            <div class="br-metric-grid">
                <article class="br-metric-card">
                    <span class="br-metric-icon br-metric-icon--berry"><i class="fa fa-shopping-bag" aria-hidden="true"></i></span>
                    <span class="br-metric-label">{{ trans('beauticianreport::admin.stats.treatment_orders') }}</span>
                    <strong>{{ number_format($analytics['totalTreatmentOrders']) }}</strong>
                    <small>{{ trans('beauticianreport::admin.stats.treatment_orders_hint') }}</small>
                </article>

                <article class="br-metric-card">
                    <span class="br-metric-icon br-metric-icon--mint"><i class="fa fa-check" aria-hidden="true"></i></span>
                    <span class="br-metric-label">{{ trans('beauticianreport::admin.stats.completed') }}</span>
                    <strong>{{ number_format($analytics['completedTreatmentOrders']) }}</strong>
                    <small>{{ trans('beauticianreport::admin.stats.completed_hint') }}</small>
                </article>

                <article class="br-metric-card">
                    <span class="br-metric-icon br-metric-icon--blue"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></span>
                    <span class="br-metric-label">{{ trans('beauticianreport::admin.stats.today_appointments') }}</span>
                    <strong>{{ number_format($analytics['todayAppointments']) }}</strong>
                    <small>{{ trans('beauticianreport::admin.stats.today_appointments_hint') }}</small>
                </article>

                <article class="br-metric-card">
                    <span class="br-metric-icon br-metric-icon--amber"><i class="fa fa-clock-o" aria-hidden="true"></i></span>
                    <span class="br-metric-label">{{ trans('beauticianreport::admin.stats.upcoming') }}</span>
                    <strong>{{ number_format($analytics['upcomingAppointments']) }}</strong>
                    <small>{{ trans('beauticianreport::admin.stats.upcoming_hint') }}</small>
                </article>

                <article class="br-metric-card">
                    <span class="br-metric-icon br-metric-icon--plum"><i class="fa fa-user-md" aria-hidden="true"></i></span>
                    <span class="br-metric-label">{{ trans('beauticianreport::admin.stats.active_beauticians') }}</span>
                    <strong>{{ number_format($analytics['activeBeauticians']) }}</strong>
                    <small>{{ trans('beauticianreport::admin.stats.active_beauticians_hint') }}</small>
                </article>

                <a class="br-report-card" href="{{ route('admin.beautician_reports.index', ['type' => 'beautician_sales_report']) }}">
                    <span><i class="fa fa-arrow-up" aria-hidden="true"></i></span>
                    <strong>{{ trans('beauticianreport::admin.open_report') }}</strong>
                    <small>{{ trans('beauticianreport::admin.filters.report_types.beautician_sales_report') }}</small>
                </a>
            </div>
        </section>

        <section class="br-visual-grid">
            <article class="br-panel br-panel--trend">
                <header class="br-panel-header">
                    <div>
                        <span class="br-section-index">01</span>
                        <h4>{{ trans('beauticianreport::admin.charts.sales_trend') }}</h4>
                        <p>{{ trans('beauticianreport::admin.charts.sales_trend_help') }}</p>
                    </div>
                    <span class="br-panel-badge">30 {{ trans('beauticianreport::admin.days') }}</span>
                </header>
                <div class="br-panel-body">
                    <div class="br-chart-wrap br-chart-wrap--trend">
                        <canvas id="br-sales-trend-chart"></canvas>
                    </div>
                </div>
            </article>

            <article class="br-panel br-panel--share">
                <header class="br-panel-header">
                    <div>
                        <span class="br-section-index">02</span>
                        <h4>{{ trans('beauticianreport::admin.charts.by_beautician') }}</h4>
                        <p>{{ trans('beauticianreport::admin.charts.by_beautician_help') }}</p>
                    </div>
                </header>
                <div class="br-panel-body">
                    <div class="br-chart-wrap br-chart-wrap--doughnut">
                        <canvas id="br-sales-by-beautician-chart"></canvas>
                    </div>
                </div>
            </article>
        </section>

        <section class="br-detail-grid">
            <article class="br-panel br-panel--leaderboard">
                <header class="br-panel-header">
                    <div>
                        <span class="br-section-index">03</span>
                        <h4>{{ trans('beauticianreport::admin.panels.top_beauticians') }}</h4>
                        <p>{{ trans('beauticianreport::admin.panels.top_beauticians_help') }}</p>
                    </div>
                    <span class="br-leaderboard-mark"><i class="fa fa-trophy" aria-hidden="true"></i></span>
                </header>
                <div class="br-leaderboard">
                    @php $leaderboardPeak = (float) ($analytics['topBeauticians']->max(fn ($item) => $item->total_sales->amount()) ?: 1); @endphp
                    @forelse ($analytics['topBeauticians'] as $row)
                        @php $leaderShare = round(((float) $row->total_sales->amount() / $leaderboardPeak) * 100); @endphp
                        <article class="br-leader-card {{ $loop->first ? 'br-leader-card--featured' : '' }}">
                            <div class="br-leader-rank">
                                <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                @if ($loop->first)<i class="fa fa-star" aria-hidden="true"></i>@endif
                            </div>
                            <div class="br-leader-profile">
                                <span class="br-leader-avatar">{{ mb_strtoupper(mb_substr($row->name, 0, 1)) }}</span>
                                <span>
                                    <small>{{ $loop->first ? trans('beauticianreport::admin.panels.revenue_leader') : trans('beauticianreport::admin.table.beautician') }}</small>
                                    <strong>{{ $row->name }}</strong>
                                    @if ($row->job_title)<em>{{ $row->job_title }}</em>@endif
                                </span>
                            </div>
                            <div class="br-leader-metrics">
                                <div><small>{{ trans('beauticianreport::admin.table.orders') }}</small><strong>{{ number_format($row->total_orders) }}</strong></div>
                                <div><small>{{ trans('beauticianreport::admin.table.total') }}</small><strong>{{ $row->total_sales->format() }}</strong></div>
                            </div>
                            <div class="br-leader-progress" role="progressbar" aria-valuenow="{{ $leaderShare }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ trans('beauticianreport::admin.panels.revenue_strength') }}">
                                <span style="width: {{ $leaderShare }}%"></span>
                            </div>
                        </article>
                    @empty
                        <div class="br-empty-state">{{ trans('beauticianreport::admin.no_data') }}</div>
                    @endforelse
                </div>
            </article>

            <article class="br-panel br-panel--recent-orders">
                <header class="br-panel-header">
                    <div>
                        <span class="br-section-index">04</span>
                        <h4>{{ trans('beauticianreport::admin.panels.recent_orders') }}</h4>
                        <p>{{ trans('beauticianreport::admin.panels.recent_orders_help') }}</p>
                    </div>
                    <div class="br-recent-actions">
                        <span class="br-order-count"><strong>{{ number_format($analytics['recentTreatmentOrders']->count()) }}</strong> {{ trans('beauticianreport::admin.table.orders') }}</span>
                        <a href="{{ route('admin.beautician_reports.index', ['type' => 'treatment_sales_report']) }}" class="br-panel-link">
                            {{ trans('beauticianreport::admin.view_all') }} <i class="fa fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </header>
                <div class="br-order-feed">
                    @forelse ($analytics['recentTreatmentOrders'] as $order)
                        <article class="br-order-feed-item">
                            <div class="br-order-identity">
                                <span class="br-order-icon"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
                                <span>
                                    <small>{{ trans('beauticianreport::admin.table.order') }}</small>
                                    <a class="br-order-link" href="{{ route('admin.orders.show', $order->id) }}">#{{ $order->id }}</a>
                                    @if ($order->appointment_date)
                                        <span class="br-cell-meta"><i class="fa fa-calendar-o" aria-hidden="true"></i> {{ $order->appointment_date->format('d M Y') }} · {{ $order->displayAppointmentTime() }}</span>
                                    @endif
                                </span>
                            </div>

                            <div class="br-order-person">
                                <span class="br-order-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($order->customer_full_name ?: '?', 0, 1)) }}</span>
                                <span><small>{{ trans('beauticianreport::admin.table.customer') }}</small><strong>{{ $order->customer_full_name }}</strong></span>
                            </div>

                            <div class="br-order-detail">
                                <small>{{ trans('beauticianreport::admin.table.beautician') }}</small>
                                <strong><i class="fa fa-user-md" aria-hidden="true"></i> {{ $order->beautician?->name ?? trans('beauticianreport::admin.not_assigned') }}</strong>
                            </div>

                            <div class="br-order-status">
                                <small>{{ trans('beauticianreport::admin.table.status') }}</small>
                                <span class="br-state br-state--{{ $order->status }}"><i class="fa fa-circle" aria-hidden="true"></i> {{ $order->status() }}</span>
                            </div>

                            <div class="br-order-total">
                                <small>{{ trans('beauticianreport::admin.table.total') }}</small>
                                <strong>{{ $order->total->format() }}</strong>
                            </div>
                        </article>
                    @empty
                        <div class="br-empty-state">{{ trans('beauticianreport::admin.no_data') }}</div>
                    @endforelse
                </div>
            </article>
        </section>

        @if (count($analytics['statusBreakdown']))
            @php
                $statusOrderTotal = array_sum(array_column($analytics['statusBreakdown'], 'count')) ?: 1;
                $statusCompletedCount = collect($analytics['statusBreakdown'])->firstWhere('status', 'completed')['count'] ?? 0;
                $statusCompletionRate = round(($statusCompletedCount / $statusOrderTotal) * 100);
            @endphp
            <section class="br-panel br-status-panel" aria-label="{{ trans('beauticianreport::admin.charts.order_status') }}">
                <header class="br-panel-header">
                    <div>
                        <span class="br-section-index">05</span>
                        <h4>{{ trans('beauticianreport::admin.charts.order_status') }}</h4>
                        <p>{{ trans('beauticianreport::admin.charts.order_status_help') }}</p>
                    </div>
                    <span class="br-status-total"><strong>{{ number_format($statusOrderTotal) }}</strong> {{ trans('beauticianreport::admin.table.orders') }}</span>
                </header>
                <div class="br-status-overview">
                    <div class="br-status-score">
                        <div class="br-status-ring" style="--br-status-progress: {{ $statusCompletionRate }}%" role="img" aria-label="{{ trans('beauticianreport::admin.completion_rate') }}: {{ $statusCompletionRate }}%">
                            <span><strong>{{ $statusCompletionRate }}</strong><small>%</small></span>
                        </div>
                        <div class="br-status-score-copy">
                            <span>{{ trans('beauticianreport::admin.completion_rate') }}</span>
                            <strong>{{ number_format($statusCompletedCount) }} {{ trans('beauticianreport::admin.stats.completed') }}</strong>
                            <small>{{ trans('beauticianreport::admin.charts.order_status_score_help') }}</small>
                        </div>
                    </div>

                    <div class="br-status-list">
                        @foreach ($analytics['statusBreakdown'] as $item)
                            @php $statusPercentage = round(($item['count'] / $statusOrderTotal) * 100); @endphp
                            <article class="br-status-item br-status-item--{{ $item['status'] }}">
                                <span class="br-status-item-icon"><i class="fa fa-{{ $item['status'] === 'completed' ? 'check' : ($item['status'] === 'canceled' || $item['status'] === 'refunded' ? 'times' : 'clock-o') }}" aria-hidden="true"></i></span>
                                <div class="br-status-item-main">
                                    <div class="br-status-item-topline">
                                        <strong>{{ $item['label'] }}</strong>
                                        <span>{{ $statusPercentage }}%</span>
                                    </div>
                                    <div class="br-status-track" role="progressbar" aria-valuenow="{{ $statusPercentage }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $item['label'] }}">
                                        <span class="br-status-fill" style="width: {{ $statusPercentage }}%"></span>
                                    </div>
                                </div>
                                <strong class="br-status-count">{{ number_format($item['count']) }}</strong>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
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
            salesLabel: @json(trans('beauticianreport::admin.charts.sales')),
            ordersLabel: @json(trans('beauticianreport::admin.charts.orders')),
        };
    </script>
@endpush
