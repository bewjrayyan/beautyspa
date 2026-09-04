@php
    $dash = $reportDashboard ?? [];
@endphp

@include('report::admin.reports.partials.overview_stats')

@if (($reportLayoutMode ?? 'full') === 'full' && ($dash['hasBeautician'] ?? false))
    @php
        $storeTrendTotal = array_sum($dash['salesTrend']['amounts'] ?? []);
        $treatmentTrendTotal = array_sum($dash['treatmentSalesTrend']['amounts'] ?? []);
        $beauticianLabels = $dash['salesByBeautician']['labels'] ?? [];
        $beauticianAmounts = $dash['salesByBeautician']['amounts'] ?? [];
        $beauticianTotal = array_sum($beauticianAmounts);
        $beauticianColors = ['purple', 'blue', 'pink', 'orange', 'green', 'cyan'];
    @endphp

    <section class="report-analytics-suite">
        <header class="report-analytics-suite__header">
            <div>
                <span class="report-analytics-suite__eyebrow">
                    <i class="fa fa-line-chart" aria-hidden="true"></i>
                    {{ trans('report::admin.charts.performance_insights') }}
                </span>
                <h4>{{ trans('report::admin.charts.sales_comparison') }}</h4>
                <p>{{ trans('report::admin.charts.sales_comparison_help') }}</p>
            </div>
            <span class="report-analytics-suite__period">
                <i class="fa fa-calendar-o" aria-hidden="true"></i>
                {{ trans('report::admin.charts.last_14_days') }}
            </span>
        </header>

        <div class="report-analytics-suite__body">
            <article class="report-trend-comparison">
                <header class="report-trend-comparison__legend">
                    <div class="report-trend-legend report-trend-legend--store">
                        <span aria-hidden="true"></span>
                        <div>
                            <small>{{ trans('report::admin.charts.store_sales') }}</small>
                            <strong>{{ \Modules\Support\Money::inDefaultCurrency($storeTrendTotal)->format() }}</strong>
                        </div>
                    </div>
                    <div class="report-trend-legend report-trend-legend--treatment">
                        <span aria-hidden="true"></span>
                        <div>
                            <small>{{ trans('report::admin.charts.treatment_sales') }}</small>
                            <strong>{{ \Modules\Support\Money::inDefaultCurrency($treatmentTrendTotal)->format() }}</strong>
                        </div>
                    </div>
                </header>
                <div class="report-trend-comparison__canvas">
                    <canvas
                        id="report-sales-comparison-chart"
                        data-store-label="{{ trans('report::admin.charts.store_sales') }}"
                        data-treatment-label="{{ trans('report::admin.charts.treatment_sales') }}"
                    ></canvas>
                </div>
            </article>

            <aside class="report-beautician-share">
                <header>
                    <div>
                        <span>{{ trans('report::admin.charts.team_contribution') }}</span>
                        <h5>{{ trans('report::admin.charts.by_beautician') }}</h5>
                    </div>
                    <strong>{{ \Modules\Support\Money::inDefaultCurrency($beauticianTotal)->format() }}</strong>
                </header>

                @if ($beauticianLabels !== [])
                    <div class="report-beautician-share__content">
                        <div class="report-beautician-share__chart">
                            <canvas id="report-by-beautician-chart"></canvas>
                        </div>
                        <ol class="report-beautician-share__list">
                            @foreach ($beauticianLabels as $index => $label)
                                @php
                                    $amount = (float) ($beauticianAmounts[$index] ?? 0);
                                    $share = $beauticianTotal > 0 ? round(($amount / $beauticianTotal) * 100) : 0;
                                @endphp
                                <li class="report-beautician-share__item report-beautician-share__item--{{ $beauticianColors[$index % count($beauticianColors)] }}">
                                    <span class="report-beautician-share__dot" aria-hidden="true"></span>
                                    <div>
                                        <strong>{{ $label }}</strong>
                                        <small>{{ \Modules\Support\Money::inDefaultCurrency($amount)->format() }}</small>
                                    </div>
                                    <b>{{ $share }}%</b>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @else
                    <div class="report-beautician-share__empty">{{ trans('report::admin.no_data') }}</div>
                @endif
            </aside>
        </div>
    </section>
@else
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
@endif

@if ($showBeauticianAnalytics ?? false)
    <div class="report-modern-panel report-bookings-panel">
        <div class="report-panel-header report-panel-header--flex">
            <div class="report-bookings-panel__intro">
                <span class="report-bookings-panel__icon" aria-hidden="true"><i class="fa fa-calendar-check-o"></i></span>
                <div>
                    <h4>{{ trans('report::admin.panels.beautician_bookings') }}</h4>
                    <p>{{ trans('report::admin.panels.beautician_bookings_help') }}</p>
                </div>
            </div>
            <a href="{{ route('admin.reports.index', ['type' => 'beautician_bookings_report']) }}" class="btn btn-default btn-sm">
                {{ trans('report::admin.view_all_bookings') }}
            </a>
        </div>
        <div class="report-bookings-snapshot" aria-label="{{ trans('report::admin.panels.beautician_bookings') }}">
            <div class="report-bookings-snapshot__item report-bookings-snapshot__item--today">
                <span>{{ trans('report::admin.bookings.stats.today') }}</span>
                <strong>{{ number_format($bookingStats['today'] ?? 0) }}</strong>
                <small>{{ trans('report::admin.bookings.stats.today_hint') }}</small>
            </div>
            <div class="report-bookings-snapshot__item report-bookings-snapshot__item--upcoming">
                <span>{{ trans('report::admin.bookings.stats.upcoming') }}</span>
                <strong>{{ number_format($bookingStats['upcoming'] ?? 0) }}</strong>
                <small>{{ trans('report::admin.bookings.stats.upcoming_hint') }}</small>
            </div>
            <div class="report-bookings-snapshot__item report-bookings-snapshot__item--completed">
                <span>{{ trans('report::admin.bookings.stats.completed') }}</span>
                <strong>{{ number_format($bookingStats['completed'] ?? 0) }}</strong>
                <small>{{ trans('report::admin.bookings.stats.completed_hint') }}</small>
            </div>
            <div class="report-bookings-snapshot__item report-bookings-snapshot__item--sales">
                <span>{{ trans('report::admin.bookings.stats.total_sales') }}</span>
                <strong>{{ ($bookingStats['totalSales'] ?? \Modules\Support\Money::inDefaultCurrency(0))->format() }}</strong>
                <small>{{ trans('report::admin.bookings.stats.total_sales_hint') }}</small>
            </div>
        </div>
        <div class="report-panel-body report-panel-body--flush">
            <div class="table-responsive">
                <table class="table report-bookings-table report-bookings-table--overview">
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
                            @php
                                $appointmentDay = $booking->appointment_date->copy()->startOfDay();
                                $timingClass = $appointmentDay->isToday() ? 'today' : ($appointmentDay->isFuture() ? 'upcoming' : 'recent');
                                $statusClass = str_replace('_', '-', $booking->status ?: 'pending');
                                $paymentClass = str_replace('_', '-', $booking->payment_status ?: 'pending');
                            @endphp
                            <tr>
                                <td>
                                    <div class="report-booking-appointment">
                                        <span class="report-booking-datebox">
                                            <b>{{ $booking->appointment_date->format('d') }}</b>
                                            <small>{{ $booking->appointment_date->format('M') }}</small>
                                        </span>
                                        <span>
                                            <strong>{{ $booking->appointment_date->format('D, d M Y') }}</strong>
                                            <small>{{ $booking->displayAppointmentTime() }}</small>
                                            <em class="report-booking-timing report-booking-timing--{{ $timingClass }}">{{ trans('report::admin.bookings.timeline.' . $timingClass) }}</em>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="report-booking-person">
                                        <span aria-hidden="true">{{ mb_strtoupper(mb_substr($booking->customer_full_name ?: '?', 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $booking->customer_full_name }}</strong>
                                            <a href="{{ route('admin.orders.show', $booking->id) }}">#{{ $booking->id }}</a>
                                        </div>
                                    </div>
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
                                        <div class="report-booking-contact"><i class="fa fa-phone" aria-hidden="true"></i> {{ $booking->customer_phone }}</div>
                                    @endif
                                    @if ($booking->customer_email)
                                        <small class="report-booking-contact"><i class="fa fa-envelope-o" aria-hidden="true"></i> {{ $booking->customer_email }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="report-status-pill report-status-pill--{{ $statusClass }}">{{ $booking->status() }}</span>
                                    <small class="report-booking-payment">
                                        <span class="report-payment-pill report-payment-pill--{{ $paymentClass }}">{{ $booking->paymentStatusLabel() }}</span>
                                    </small>
                                </td>
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
