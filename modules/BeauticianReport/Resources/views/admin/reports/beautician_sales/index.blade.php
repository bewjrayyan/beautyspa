@extends('beauticianreport::admin.reports.layout')

@section('filters')
    @include('report::admin.reports.filters.from')
    @include('report::admin.reports.filters.to')
    @include('report::admin.reports.filters.status')
    @include('beauticianreport::admin.reports.filters.beautician')
@endsection

@section('report_result')
    @php
        $reportItems = collect($report->items());
        $moneyAmount = static fn ($value) => $value instanceof \Modules\Support\Money
            ? (float) $value->amount()
            : (float) ($value ?? 0);
        $totalOrders = (int) $reportItems->sum('total_orders');
        $totalProducts = (int) $reportItems->sum('total_products');
        $totalRevenue = $reportItems->sum(fn ($item) => $moneyAmount($item->total));
        $totalDiscount = $reportItems->sum(fn ($item) => $moneyAmount($item->discount));
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $beauticiansCount = $reportItems->count();
        $peakRevenue = $reportItems->max(fn ($item) => $moneyAmount($item->total)) ?: 0;
    @endphp

    <section class="br-sales-report">
        <header class="br-sales-hero">
            <div class="br-sales-hero__copy">
                <span class="br-sales-eyebrow">
                    <i class="fa fa-line-chart" aria-hidden="true"></i>
                    {{ trans('beauticianreport::admin.analytics') }}
                </span>
                <h5>{{ trans('beauticianreport::admin.filters.report_types.beautician_sales_report') }}</h5>
                <p>{{ trans('beauticianreport::admin.reports.beautician_sales_help') }}</p>
            </div>

            <div class="br-sales-records">
                <strong>{{ number_format($reportItems->count()) }}</strong>
                <span>{{ trans('beauticianreport::admin.reports.records') }}</span>
            </div>
        </header>

        <div class="br-sales-kpis">
            <article class="br-sales-kpi br-sales-kpi--primary">
                <span class="br-sales-kpi__icon"><i class="fa fa-money" aria-hidden="true"></i></span>
                <div>
                    <span class="br-sales-kpi__label">{{ trans('beauticianreport::admin.reports.revenue') }}</span>
                    <strong>{{ \Modules\Support\Money::inDefaultCurrency($totalRevenue)->format() }}</strong>
                    <small>{{ trans('beauticianreport::admin.reports.current_page_summary') }}</small>
                </div>
            </article>

            <article class="br-sales-kpi">
                <span class="br-sales-kpi__icon"><i class="fa fa-users" aria-hidden="true"></i></span>
                <div>
                    <span class="br-sales-kpi__label">{{ trans('beauticianreport::admin.reports.beauticians') }}</span>
                    <strong>{{ number_format($beauticiansCount) }}</strong>
                </div>
            </article>

            <article class="br-sales-kpi">
                <span class="br-sales-kpi__icon"><i class="fa fa-shopping-bag" aria-hidden="true"></i></span>
                <div>
                    <span class="br-sales-kpi__label">{{ trans('beauticianreport::admin.reports.total_orders') }}</span>
                    <strong>{{ number_format($totalOrders) }}</strong>
                </div>
            </article>

            <article class="br-sales-kpi">
                <span class="br-sales-kpi__icon"><i class="fa fa-cubes" aria-hidden="true"></i></span>
                <div>
                    <span class="br-sales-kpi__label">{{ trans('beauticianreport::admin.reports.items_sold') }}</span>
                    <strong>{{ number_format($totalProducts) }}</strong>
                </div>
            </article>

            <article class="br-sales-kpi">
                <span class="br-sales-kpi__icon"><i class="fa fa-calculator" aria-hidden="true"></i></span>
                <div>
                    <span class="br-sales-kpi__label">{{ trans('beauticianreport::admin.reports.average_order_value') }}</span>
                    <strong>{{ \Modules\Support\Money::inDefaultCurrency($avgOrderValue)->format() }}</strong>
                </div>
            </article>

            <article class="br-sales-kpi">
                <span class="br-sales-kpi__icon"><i class="fa fa-tag" aria-hidden="true"></i></span>
                <div>
                    <span class="br-sales-kpi__label">{{ trans('beauticianreport::admin.reports.discounts') }}</span>
                    <strong>{{ \Modules\Support\Money::inDefaultCurrency($totalDiscount)->format() }}</strong>
                </div>
            </article>
        </div>

        <section class="br-sales-team">
            <header class="br-sales-team__header">
                <div>
                    <span class="br-sales-section-index">01</span>
                    <h6>{{ trans('beauticianreport::admin.reports.team_performance') }}</h6>
                    <p>{{ trans('beauticianreport::admin.reports.team_performance_help') }}</p>
                </div>
            </header>

            <div class="table-responsive br-sales-table-wrap">
                <table class="table br-sales-table">
                    <thead>
                        <tr>
                            <th>{{ trans('beauticianreport::admin.table.beautician') }}</th>
                            <th>{{ trans('beauticianreport::admin.table.orders') }}</th>
                            <th>{{ trans('beauticianreport::admin.table.products') }}</th>
                            <th>{{ trans('beauticianreport::admin.reports.average_order_value') }}</th>
                            <th>{{ trans('beauticianreport::admin.table.discount') }}</th>
                            <th>{{ trans('beauticianreport::admin.reports.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report as $data)
                            @php
                                $dataTotal = $moneyAmount($data->total);
                                $dataDiscount = $moneyAmount($data->discount);
                                $avgValue = $data->total_orders > 0 ? $dataTotal / $data->total_orders : 0;
                                $revenueShare = $peakRevenue > 0 ? (int) round(($dataTotal / $peakRevenue) * 100) : 0;
                                $initial = mb_strtoupper(mb_substr(trim($data->beautician_name), 0, 1));
                            @endphp
                            <tr>
                                <td data-label="{{ trans('beauticianreport::admin.table.beautician') }}">
                                    <div class="br-sales-person">
                                        <span class="br-sales-avatar">{{ $initial ?: '—' }}</span>
                                        <span>
                                            <strong>{{ $data->beautician_name }}</strong>
                                            <small>{{ $data->beautician_job_title ?: trans('beauticianreport::admin.not_assigned') }}</small>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="{{ trans('beauticianreport::admin.table.orders') }}">
                                    <span class="br-sales-number">{{ number_format($data->total_orders) }}</span>
                                </td>
                                <td data-label="{{ trans('beauticianreport::admin.table.products') }}">
                                    {{ number_format($data->total_products) }}
                                </td>
                                <td data-label="{{ trans('beauticianreport::admin.reports.average_order_value') }}">
                                    {{ \Modules\Support\Money::inDefaultCurrency($avgValue)->format() }}
                                </td>
                                <td data-label="{{ trans('beauticianreport::admin.table.discount') }}">
                                    <span class="br-sales-discount">−{{ \Modules\Support\Money::inDefaultCurrency($dataDiscount)->format() }}</span>
                                </td>
                                <td data-label="{{ trans('beauticianreport::admin.reports.revenue') }}">
                                    <div class="br-sales-revenue">
                                        <strong>{{ \Modules\Support\Money::inDefaultCurrency($dataTotal)->format() }}</strong>
                                        <span class="br-sales-revenue__track" aria-hidden="true">
                                            <span style="width: {{ $revenueShare }}%"></span>
                                        </span>
                                        <small>{{ $loop->first ? trans('beauticianreport::admin.reports.top_performer') : trans('beauticianreport::admin.reports.team_contribution') }}</small>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="br-empty-state" colspan="6">
                                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                                    <p>{{ trans('beauticianreport::admin.no_data') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($report->hasPages())
                <div class="br-pagination">
                    {!! $report->links() !!}
                </div>
            @endif
        </section>
    </section>
@endsection
