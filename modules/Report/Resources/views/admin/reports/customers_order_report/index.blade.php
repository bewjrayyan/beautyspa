@extends('report::admin.reports.layout')

@section('filters')
    @include('report::admin.reports.filters.from')
    @include('report::admin.reports.filters.to')
    @include('report::admin.reports.filters.status')
    @include('report::admin.reports.filters.group')
    @include('report::admin.reports.filters.spa_branch')

    <div class="form-group report-field">
        <label class="report-field__label" for="customer-name">{{ trans('report::admin.filters.customer_name') }}</label>
        <input type="text" name="customer_name" class="form-control" id="customer-name" value="{{ $request->customer_name }}">
    </div>

    <div class="form-group report-field">
        <label class="report-field__label" for="customer-email">{{ trans('report::admin.filters.customer_email') }}</label>
        <input type="text" name="customer_email" class="form-control" id="customer-email" value="{{ $request->customer_email }}">
    </div>
@endsection

@section('report_result')
    @php
        use Modules\Report\Support\ReportFormatters;
    @endphp

    <div class="box-header">
        <h5>
            {{ trans('report::admin.filters.report_types.customers_order_report') }}
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
                        <th>{{ trans('report::admin.table.customer_email') }}</th>
                        <th>{{ trans('report::admin.table.customer_phone') }}</th>
                        <th>{{ trans('report::admin.table.customer_group') }}</th>
                        @if (is_module_enabled('SpaBranch'))
                            <th>{{ trans('report::admin.table.spa_branch') }}</th>
                        @endif
                        <th>{{ trans('report::admin.table.beautician_appointment') }}</th>
                        <th>{{ trans('report::admin.table.order_status') }}</th>
                        <th>{{ trans('report::admin.table.payment_status') }}</th>
                        <th>{{ trans('report::admin.table.products') }}</th>
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
                            <td>{{ $data->customer_email ?: '—' }}</td>
                            <td>{{ $data->customer_phone ?: '—' }}</td>
                            <td>{{ is_null($data->customer_id) ? trans('report::admin.table.guest') : trans('report::admin.table.registered') }}</td>
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
                            <td>{{ $data->total->format() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="{{ is_module_enabled('SpaBranch') ? 12 : 11 }}">{{ trans('report::admin.no_data') }}</td>
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
