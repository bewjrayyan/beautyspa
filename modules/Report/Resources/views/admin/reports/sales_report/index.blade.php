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
    @endphp

    <div class="box-header">
        <h5>
            {{ trans('report::admin.filters.report_types.sales_report') }}
        </h5>
    </div>

    <div class="box-body">
        <div class="table-responsive anchor-table report-table-scroll">
            <table class="table report-table--detailed">
                <thead>
                    <tr>
                        <th>{{ trans('report::admin.table.order') }}</th>
                        <th>{{ trans('report::admin.table.order_date') }}</th>
                        <th>{{ trans('report::admin.table.customer_name') }}</th>
                        <th>{{ trans('report::admin.table.contact') }}</th>
                        @if (is_module_enabled('SpaBranch'))
                            <th>{{ trans('report::admin.table.spa_branch') }}</th>
                        @endif
                        <th>{{ trans('report::admin.table.beautician_appointment') }}</th>
                        <th>{{ trans('report::admin.table.order_status') }}</th>
                        <th>{{ trans('report::admin.table.payment_status') }}</th>
                        <th>{{ trans('report::admin.table.products') }}</th>
                        <th>{{ trans('report::admin.table.subtotal') }}</th>
                        <th>{{ trans('report::admin.table.shipping') }}</th>
                        <th>{{ trans('report::admin.table.discount') }}</th>
                        <th>{{ trans('report::admin.table.tax') }}</th>
                        <th>{{ trans('report::admin.table.grand_total') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($report as $data)
                        <tr>
                            <td class="report-cell--nowrap">
                                <a href="{{ route('admin.orders.show', $data->order_id) }}">#{{ $data->order_id }}</a>
                            </td>
                            <td class="report-cell--nowrap">{{ ReportFormatters::orderDate($data->order_date) }}</td>
                            <td>{{ ReportFormatters::customerName($data) }}</td>
                            @include('report::admin.reports.partials.contact', ['row' => $data])
                            @if (is_module_enabled('SpaBranch'))
                                @include('report::admin.reports.partials.spa_branch', ['row' => $data])
                            @endif
                            @include('report::admin.reports.partials.beautician_appointment', ['row' => $data])
                            <td>
                                <span class="report-status-pill report-status-pill--{{ $data->order_status }}">
                                    {{ ReportFormatters::orderStatus($data->order_status) }}
                                </span>
                            </td>
                            <td>
                                @include('report::admin.reports.partials.payment_status', ['paymentStatus' => $data->payment_status])
                            </td>
                            <td>{{ $data->total_products }}</td>
                            <td>{{ $data->sub_total->format() }}</td>
                            <td>{{ $data->shipping_cost->format() }}</td>
                            <td>{{ $data->discount->format() }}</td>
                            <td>{{ $data->tax->format() }}</td>
                            <td>{{ $data->total->format() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="{{ is_module_enabled('SpaBranch') ? 14 : 13 }}">{{ trans('report::admin.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="pull-right">
                {!! $report->links() !!}
            </div>
        </div>
    </div>
@endsection
