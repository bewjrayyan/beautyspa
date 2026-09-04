@extends('report::admin.reports.layout')

@section('filter_zones')
    @component('report::admin.reports.partials.filter_zone', [
        'title' => trans('report::admin.filters.zone_filters'),
        'icon' => 'fa-filter',
        'hint' => trans('report::admin.filters.zone_filters_hint'),
        'class' => 'report-filter-zone--sales',
    ])
        @include('report::admin.reports.filters.from')
        @include('report::admin.reports.filters.to')
        @include('report::admin.reports.filters.status')
        @include('report::admin.reports.filters.category_id')
        @include('report::admin.reports.filters.product')
        @include('report::admin.reports.filters.spa_branch')
    @endcomponent

    @include('report::admin.reports.filters.product_options')
@endsection

@section('report_result')
    @php
        use Modules\Report\Support\ReportFormatters;

        $salesRows = collect($report->items());
        $moneyAmount = static fn ($value) => $value instanceof \Modules\Support\Money
            ? (float) $value->amount()
            : (float) ($value ?? 0);
        $ordersCount = $salesRows->count();
        $totalRevenue = $salesRows->sum(fn ($item) => $moneyAmount($item->total));
        $totalProducts = (int) $salesRows->sum('total_products');
        $totalDiscount = $salesRows->sum(fn ($item) => $moneyAmount($item->discount));
        $averageOrderValue = $ordersCount > 0 ? $totalRevenue / $ordersCount : 0;
        $completedOrders = $salesRows->where('order_status', 'completed')->count();
    @endphp

    <section class="sales-workspace">
        <header class="sales-workspace__header">
            <div class="sales-workspace__heading">
                <span class="sales-workspace__eyebrow">
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    {{ trans('report::admin.sales.command_center') }}
                </span>
                <h5>{{ trans('report::admin.sales.title') }}</h5>
                <p>{{ trans('report::admin.sales.subtitle') }}</p>
            </div>

            <div class="sales-workspace__headline">
                <span>{{ trans('report::admin.sales.filtered_revenue') }}</span>
                <strong>{{ \Modules\Support\Money::inDefaultCurrency($totalRevenue)->format() }}</strong>
                <small>{{ trans('report::admin.sales.current_page') }}</small>
            </div>
        </header>

        <div class="sales-workspace__metrics">
            @foreach ([
                ['class' => 'blue', 'icon' => 'fa-line-chart', 'label' => trans('report::admin.sales.revenue'), 'value' => \Modules\Support\Money::inDefaultCurrency($totalRevenue)->format()],
                ['class' => 'purple', 'icon' => 'fa-shopping-bag', 'label' => trans('report::admin.sales.orders'), 'value' => number_format($ordersCount)],
                ['class' => 'green', 'icon' => 'fa-calculator', 'label' => trans('report::admin.sales.average_order'), 'value' => \Modules\Support\Money::inDefaultCurrency($averageOrderValue)->format()],
                ['class' => 'cyan', 'icon' => 'fa-cubes', 'label' => trans('report::admin.sales.items'), 'value' => number_format($totalProducts)],
                ['class' => 'amber', 'icon' => 'fa-tag', 'label' => trans('report::admin.sales.discounts'), 'value' => \Modules\Support\Money::inDefaultCurrency($totalDiscount)->format()],
                ['class' => 'mint', 'icon' => 'fa-check-circle', 'label' => trans('report::admin.sales.completed'), 'value' => number_format($completedOrders)],
            ] as $metric)
                <article class="sales-workspace-metric sales-workspace-metric--{{ $metric['class'] }}">
                    <span class="sales-workspace-metric__icon">
                        <i class="fa {{ $metric['icon'] }}" aria-hidden="true"></i>
                    </span>
                    <span>{{ $metric['label'] }}</span>
                    <strong>{{ $metric['value'] }}</strong>
                </article>
            @endforeach
        </div>

        <section class="sales-transactions">
            <header class="sales-transactions__header">
                <div>
                    <span class="sales-transactions__kicker">{{ trans('report::admin.sales.current_page') }}</span>
                    <h6>{{ trans('report::admin.sales.order_activity') }}</h6>
                    <p>{{ trans('report::admin.sales.order_activity_help') }}</p>
                </div>
                <span class="sales-transactions__count">
                    {{ trans_choice('report::admin.sales.order_count', $ordersCount, ['count' => number_format($ordersCount)]) }}
                </span>
            </header>

            <div class="sales-transactions__table-wrap">
                <table class="sales-transactions__table" id="sales-transaction-table">
                    <thead>
                        <tr>
                            <th aria-sort="none">
                                <button type="button" class="sales-sort" data-sales-sort="text">
                                    {{ trans('report::admin.table.order') }} <i class="fa fa-sort" aria-hidden="true"></i>
                                </button>
                            </th>
                            <th aria-sort="none">
                                <button type="button" class="sales-sort" data-sales-sort="text">
                                    {{ trans('report::admin.table.customer') }} <i class="fa fa-sort" aria-hidden="true"></i>
                                </button>
                            </th>
                            <th>{{ trans('report::admin.sales.fulfilment') }}</th>
                            <th class="sales-transactions__center" aria-sort="none">
                                <button type="button" class="sales-sort" data-sales-sort="number">
                                    {{ trans('report::admin.sales.items') }} <i class="fa fa-sort" aria-hidden="true"></i>
                                </button>
                            </th>
                            <th aria-sort="none">
                                <button type="button" class="sales-sort" data-sales-sort="text">
                                    {{ trans('report::admin.table.order_status') }} <i class="fa fa-sort" aria-hidden="true"></i>
                                </button>
                            </th>
                            <th>{{ trans('report::admin.table.payment_status') }}</th>
                            <th class="sales-transactions__right" aria-sort="none">
                                <button type="button" class="sales-sort" data-sales-sort="number">
                                    {{ trans('report::admin.table.grand_total') }} <i class="fa fa-sort" aria-hidden="true"></i>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report as $data)
                            @php
                                $customerName = ReportFormatters::customerName($data) ?: trans('report::admin.sales.guest_customer');
                                $beautician = trim((string) ($data->beautician_name ?? ''));
                                $hasAppointment = ! empty($data->appointment_date);
                                $paymentStatusClass = $data->payment_status ?: 'pending';
                            @endphp
                            <tr>
                                <td data-sales-value="{{ $data->order_id }}">
                                    <a class="sales-order-link" href="{{ route('admin.orders.show', $data->order_id) }}" aria-label="{{ trans('report::admin.sales.open_order', ['order' => $data->order_id]) }}">
                                        <span><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
                                        <strong>#{{ $data->order_id }}</strong>
                                    </a>
                                    <small>{{ ReportFormatters::orderDate($data->order_date) }}</small>
                                </td>
                                <td data-sales-value="{{ mb_strtolower($customerName) }}">
                                    <div class="sales-customer">
                                        <span>{{ mb_strtoupper(mb_substr($customerName, 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $customerName }}</strong>
                                            <small>{{ $data->customer_phone ?: ($data->customer_email ?: '—') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong class="sales-fulfilment__primary">
                                        {{ is_module_enabled('SpaBranch') ? ReportFormatters::spaBranchName($data) : ($beautician ?: '—') }}
                                    </strong>
                                    @if ($beautician !== '')
                                        <small><i class="fa fa-user-o" aria-hidden="true"></i> {{ $beautician }}</small>
                                    @endif
                                    @if ($hasAppointment)
                                        <small><i class="fa fa-calendar-o" aria-hidden="true"></i> {{ ReportFormatters::appointment($data) }}</small>
                                    @endif
                                </td>
                                <td class="sales-transactions__center" data-sales-value="{{ $data->total_products }}">
                                    <span class="sales-items-count">{{ number_format($data->total_products) }}</span>
                                </td>
                                <td data-sales-value="{{ $data->order_status }}">
                                    <span class="report-status-pill report-status-pill--{{ $data->order_status }}">
                                        {{ ReportFormatters::orderStatus($data->order_status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="report-payment-pill report-payment-pill--{{ $paymentStatusClass }}">
                                        {{ ReportFormatters::paymentStatus($data->payment_status) }}
                                    </span>
                                </td>
                                <td class="sales-transactions__right" data-sales-value="{{ $moneyAmount($data->total) }}">
                                    <strong class="sales-total">{{ $data->total->format() }}</strong>
                                    <small>{{ trans('report::admin.table.subtotal') }} {{ $data->sub_total->format() }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="sales-transactions__empty">
                                        <span><i class="fa fa-shopping-bag" aria-hidden="true"></i></span>
                                        <strong>{{ trans('report::admin.sales.empty_title') }}</strong>
                                        <p>{{ trans('report::admin.sales.empty_help') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($report->hasPages())
                <div class="sales-transactions__pagination">
                    {!! $report->links() !!}
                </div>
            @endif
        </section>
    </section>
@endsection
