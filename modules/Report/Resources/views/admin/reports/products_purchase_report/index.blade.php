@extends('report::admin.reports.layout')

@section('filters')
    @include('report::admin.reports.filters.from')
    @include('report::admin.reports.filters.to')
    @include('report::admin.reports.filters.status')

    @include('report::admin.reports.filters.product_select', [
        'id' => 'report-product-purchase',
        'requireCategory' => false,
        'selectedProduct' => $selectedProduct ?? null,
        'optionsUrl' => route('admin.reports.products.options'),
        'optionsTarget' => '#report-purchase-options',
        'skuOptionsSwap' => true,
        'skuField' => '#report-sku-field',
    ])

    @include('report::admin.reports.filters.sku_or_product_options', [
        'selectedProduct' => $selectedProduct ?? null,
    ])
@endsection

@section('report_result')
    @php
        use Modules\Report\Support\ReportFormatters;
    @endphp

    <div class="box-header">
        <h5>
            {{ trans('report::admin.filters.report_types.products_purchase_report') }}
        </h5>
    </div>

    <div class="box-body">
        <div class="table-responsive anchor-table report-table-scroll">
            <table class="table report-table--detailed">
                <thead>
                    <tr>
                        <th>{{ trans('report::admin.table.order') }}</th>
                        <th>{{ trans('report::admin.table.order_date') }}</th>
                        <th>{{ trans('report::admin.table.product') }}</th>
                        <th>{{ trans('report::admin.table.qty') }}</th>
                        <th>{{ trans('report::admin.table.grand_total') }}</th>
                        <th>{{ trans('report::admin.table.customer_name') }}</th>
                        <th>{{ trans('report::admin.table.contact') }}</th>
                        <th>{{ trans('report::admin.table.beautician_appointment') }}</th>
                        <th>{{ trans('report::admin.table.order_status') }}</th>
                        <th>{{ trans('report::admin.table.payment_status') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($report as $order)
                        @php
                            $lines = $order->products;
                        @endphp
                        <tr>
                            <td class="report-cell--nowrap">
                                <a href="{{ route('admin.orders.show', $order->order_id) }}">#{{ $order->order_id }}</a>
                            </td>
                            <td class="report-cell--nowrap">{{ ReportFormatters::orderDate($order->order_date) }}</td>
                            <td class="report-cell--products">
                                <ul class="report-order-lines">
                                    @foreach ($lines as $line)
                                        @include('report::admin.reports.partials.order_product_line', ['line' => $line])
                                    @endforeach
                                </ul>
                            </td>
                            <td>{{ ReportFormatters::orderLinesQty($lines) }}</td>
                            <td>{{ $order->total->format() }}</td>
                            <td>{{ ReportFormatters::customerName($order) }}</td>
                            @include('report::admin.reports.partials.contact', ['row' => $order])
                            @include('report::admin.reports.partials.beautician_appointment', ['row' => $order])
                            <td>
                                <span class="report-status-pill report-status-pill--{{ $order->order_status }}">
                                    {{ ReportFormatters::orderStatus($order->order_status) }}
                                </span>
                            </td>
                            <td>
                                @include('report::admin.reports.partials.payment_status', ['paymentStatus' => $order->payment_status])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="10">{{ trans('report::admin.no_data') }}</td>
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
