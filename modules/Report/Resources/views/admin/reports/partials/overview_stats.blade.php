@php
    $dash = $reportDashboard ?? [];
@endphp

<div class="report-modern-header">
    <div>
        <h3 class="report-modern-title">{{ trans('report::admin.overview') }}</h3>
        <p class="report-modern-subtitle">{{ trans('report::admin.overview_help') }}</p>
    </div>
</div>

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


@if (! empty($dash['salesByBranch']))
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
