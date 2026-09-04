@php
    $dash = $reportDashboard ?? [];
@endphp

<div class="report-modern-header">
    <div>
        <h3 class="report-modern-title">{{ trans('report::admin.overview') }}</h3>
        <p class="report-modern-subtitle">{{ trans('report::admin.overview_help') }}</p>
    </div>
</div>

@if (($reportLayoutMode ?? 'full') === 'full')
    <div class="report-key-stats">
        <article class="report-key-stat report-key-stat--brand">
            <div class="report-key-stat__top">
                <span class="report-key-stat__icon"><i class="fa fa-line-chart" aria-hidden="true"></i></span>
                <span class="report-key-stat__label">{{ trans('report::admin.stats.total_sales') }}</span>
            </div>
            <strong class="report-key-stat__value">{{ ($dash['totalSales'] ?? null)?->format() ?? '—' }}</strong>
            <div class="report-key-stat__footer">
                <span>{{ trans('report::admin.stats.treatment_sales') }}</span>
                <b>{{ ($dash['treatmentSales'] ?? null)?->format() ?? '—' }}</b>
            </div>
        </article>

        <article class="report-key-stat report-key-stat--green">
            <div class="report-key-stat__top">
                <span class="report-key-stat__icon"><i class="fa fa-balance-scale" aria-hidden="true"></i></span>
                <span class="report-key-stat__label">{{ trans('report::admin.stats.net_sales') }}</span>
            </div>
            <strong class="report-key-stat__value">{{ ($dash['netSales'] ?? null)?->format() ?? '—' }}</strong>
            <p>{{ trans('report::admin.stats.net_sales_hint') }}</p>
        </article>

        <article class="report-key-stat report-key-stat--purple">
            <div class="report-key-stat__top">
                <span class="report-key-stat__icon"><i class="fa fa-shopping-bag" aria-hidden="true"></i></span>
                <span class="report-key-stat__label">{{ trans('report::admin.stats.total_orders') }}</span>
            </div>
            <strong class="report-key-stat__value">{{ number_format($dash['totalOrders'] ?? 0) }}</strong>
            <div class="report-key-stat__split">
                <span><i class="fa fa-check-circle" aria-hidden="true"></i> {{ number_format($dash['completedOrders'] ?? 0) }} {{ trans('report::admin.stats.completed') }}</span>
                <span><i class="fa fa-clock-o" aria-hidden="true"></i> {{ number_format($dash['pendingOrders'] ?? 0) }} {{ trans('report::admin.stats.pending') }}</span>
            </div>
        </article>

        <article class="report-key-stat report-key-stat--blue">
            <div class="report-key-stat__top">
                <span class="report-key-stat__icon"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></span>
                <span class="report-key-stat__label">{{ trans('report::admin.stats.today_appointments') }}</span>
            </div>
            <strong class="report-key-stat__value">{{ number_format($dash['todayAppointments'] ?? 0) }}</strong>
            <p>{{ trans('report::admin.stats.today_appointments_hint') }}</p>
        </article>
    </div>
@else
<div class="fc-saas-stats">
    @include('admin::partials.fc_saas_stat', [
        'variant' => 'sky',
        'icon' => 'fa-line-chart',
        'label' => trans('report::admin.stats.total_sales'),
        'value' => ($dash['totalSales'] ?? null)?->format() ?? '—',
        'hint' => trans('report::admin.stats.total_sales_hint'),
    ])

    @include('admin::partials.fc_saas_stat', [
        'variant' => 'mint',
        'icon' => 'fa-balance-scale',
        'label' => trans('report::admin.stats.net_sales'),
        'value' => ($dash['netSales'] ?? null)?->format() ?? '—',
        'hint' => trans('report::admin.stats.net_sales_hint'),
    ])

    @include('admin::partials.fc_saas_stat', [
        'variant' => 'rose',
        'icon' => 'fa-shopping-cart',
        'label' => trans('report::admin.stats.total_orders'),
        'value' => number_format($dash['totalOrders'] ?? 0),
        'hint' => trans('report::admin.stats.total_orders_hint'),
    ])

    @include('admin::partials.fc_saas_stat', [
        'variant' => 'indigo',
        'icon' => 'fa-check-circle',
        'label' => trans('report::admin.stats.completed'),
        'value' => number_format($dash['completedOrders'] ?? 0),
        'hint' => trans('report::admin.stats.completed_hint'),
    ])

    @include('admin::partials.fc_saas_stat', [
        'variant' => 'peach',
        'icon' => 'fa-clock-o',
        'label' => trans('report::admin.stats.pending'),
        'value' => number_format($dash['pendingOrders'] ?? 0),
        'hint' => trans('report::admin.stats.pending_hint'),
    ])

    @if ($dash['hasBeautician'] ?? false)
        @include('admin::partials.fc_saas_stat', [
            'variant' => 'violet',
            'icon' => 'fa-heart',
            'label' => trans('report::admin.stats.treatment_sales'),
            'value' => $dash['treatmentSales']->format(),
            'hint' => trans('report::admin.stats.treatment_sales_hint'),
        ])

        @include('admin::partials.fc_saas_stat', [
            'variant' => 'peach',
            'icon' => 'fa-calendar',
            'label' => trans('report::admin.stats.today_appointments'),
            'value' => number_format($dash['todayAppointments'] ?? 0),
            'hint' => trans('report::admin.stats.today_appointments_hint'),
        ])
    @endif
</div>
@endif


@if (! empty($dash['salesByBranch']))
    @if (($reportLayoutMode ?? 'full') === 'full')
        <section class="report-branch-performance">
            <header class="report-branch-performance__header">
                <div class="report-branch-performance__intro">
                    <span class="report-branch-performance__icon">
                        <i class="fa fa-building-o" aria-hidden="true"></i>
                    </span>
                    <div>
                        <h4>{{ trans('report::admin.stats.sales_by_branch') }}</h4>
                        <p>{{ trans('report::admin.stats.sales_by_branch_hint') }}</p>
                    </div>
                </div>
                <span class="report-branch-performance__count">
                    {{ number_format(count($dash['salesByBranch'])) }}
                    {{ trans('report::admin.table.spa_branch') }}
                </span>
            </header>

            <div class="report-branch-performance__list">
                @foreach ($dash['salesByBranch'] as $branch)
                    @php
                        $branchTotal = (float) $branch['total']->amount();
                        $branchNet = (float) $branch['net']->amount();
                        $netShare = $branchTotal > 0
                            ? min(100, max(0, round(($branchNet / $branchTotal) * 100)))
                            : 0;
                    @endphp
                    <article class="report-branch-performance__item">
                        <div class="report-branch-performance__branch">
                            <span>{{ mb_strtoupper(mb_substr($branch['name'], 0, 1)) }}</span>
                            <div>
                                <strong>{{ $branch['name'] }}</strong>
                                <small>{{ trans('report::admin.table.spa_branch') }}</small>
                            </div>
                        </div>

                        <div class="report-branch-performance__amount">
                            <span>{{ trans('report::admin.stats.total_sales') }}</span>
                            <strong>{{ $branch['total']->format() }}</strong>
                        </div>

                        <div class="report-branch-performance__net">
                            <div>
                                <span>{{ trans('report::admin.stats.net_sales') }}</span>
                                <strong>{{ $branch['net']->format() }}</strong>
                            </div>
                            <div class="report-branch-performance__progress" aria-label="{{ trans('report::admin.stats.net_sales') }} {{ $netShare }}%">
                                <span style="width: {{ $netShare }}%"></span>
                            </div>
                            <small>{{ $netShare }}%</small>
                        </div>
                    </article>
                @endforeach
            </div>

            <footer class="report-branch-performance__footer">
                <div>
                    <span>{{ trans('report::admin.stats.total_sales') }}</span>
                    <strong>{{ ($dash['totalSales'] ?? null)?->format() }}</strong>
                </div>
                <div>
                    <span>{{ trans('report::admin.stats.net_sales') }}</span>
                    <strong>{{ ($dash['netSales'] ?? null)?->format() }}</strong>
                </div>
            </footer>
        </section>
    @else
        <section class="report-branch-sales" style="margin-top: 16px;">
            <h4 style="margin: 0 0 4px; font-size: 15px; font-weight: 700;">{{ trans('report::admin.stats.sales_by_branch') }}</h4>
            <p class="text-muted" style="margin: 0 0 12px; font-size: 12px;">{{ trans('report::admin.stats.sales_by_branch_hint') }}</p>
            <div class="table-responsive">
                <table class="table table-condensed" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th>{{ trans('report::admin.table.spa_branch') }}</th>
                            <th class="text-right">{{ trans('report::admin.stats.total_sales') }}</th>
                            <th class="text-right">{{ trans('report::admin.stats.net_sales') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dash['salesByBranch'] as $branch)
                            <tr>
                                <td>{{ $branch['name'] }}</td>
                                <td class="text-right">{{ $branch['total']->format() }}</td>
                                <td class="text-right">{{ $branch['net']->format() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>{{ trans('report::admin.stats.total_sales') }}</th>
                            <th class="text-right">{{ ($dash['totalSales'] ?? null)?->format() }}</th>
                            <th class="text-right">{{ ($dash['netSales'] ?? null)?->format() }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    @endif
@endif
