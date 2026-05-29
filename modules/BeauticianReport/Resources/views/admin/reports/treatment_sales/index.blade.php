@extends('beauticianreport::admin.reports.layout')

@section('filters')
    <div class="form-group br-filter-field">
        <label for="from">{{ trans('report::admin.filters.date_start') }}</label>
        <input type="text" name="from" class="form-control datetime-picker" id="from" data-default-date="{{ $request->from }}">
    </div>

    <div class="form-group br-filter-field">
        <label for="to">{{ trans('report::admin.filters.date_end') }}</label>
        <input type="text" name="to" class="form-control datetime-picker" id="to" data-default-date="{{ $request->to }}">
    </div>

    <div class="form-group br-filter-field">
        <label for="status">{{ trans('beauticianreport::admin.filters.status') }}</label>
        <select name="status" id="status" class="custom-select-black">
            <option value="">{{ trans('beauticianreport::admin.filters.please_select') }}</option>
            @foreach (trans('order::statuses') as $key => $label)
                <option value="{{ $key }}" {{ $request->status === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group br-filter-field">
        @include('beauticianreport::admin.reports.filters.beautician')
    </div>
@endsection

@section('report_stats')
    @include('beauticianreport::admin.reports.partials.report_stats', ['stats' => $stats])
@endsection

@section('report_result')
    <div class="br-result-header">
        <div>
            <h5>{{ trans('beauticianreport::admin.filters.report_types.treatment_sales_report') }}</h5>
            <p class="text-muted">{{ trans('beauticianreport::admin.reports.treatment_sales_help') }}</p>
        </div>
        <span class="br-result-count">{{ $report->count() }} {{ trans('beauticianreport::admin.reports.records') }}</span>
    </div>

    <div class="br-result-body">
        <div class="table-responsive">
            <table class="table br-table br-table--saas">
                <thead>
                    <tr>
                        <th>{{ trans('beauticianreport::admin.table.order') }}</th>
                        <th>{{ trans('beauticianreport::admin.table.customer') }}</th>
                        <th>{{ trans('beauticianreport::admin.table.beautician') }}</th>
                        <th>{{ trans('beauticianreport::admin.table.appointment_date') }}</th>
                        <th>{{ trans('beauticianreport::admin.table.status') }}</th>
                        <th>{{ trans('beauticianreport::admin.table.products') }}</th>
                        <th class="text-right">{{ trans('beauticianreport::admin.table.total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report as $order)
                        <tr>
                            <td data-label="{{ trans('beauticianreport::admin.table.order') }}">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="br-order-link">#{{ $order->id }}</a>
                                <span class="br-cell-muted">{{ $order->created_at->format('d M Y H:i') }}</span>
                            </td>
                            <td data-label="{{ trans('beauticianreport::admin.table.customer') }}">
                                <strong>{{ $order->customer_first_name }} {{ $order->customer_last_name }}</strong>
                                @if ($order->customer_email)
                                    <span class="br-cell-muted">{{ $order->customer_email }}</span>
                                @endif
                            </td>
                            <td data-label="{{ trans('beauticianreport::admin.table.beautician') }}">
                                <strong>{{ $order->beautician_name ?: '—' }}</strong>
                                @if ($order->beautician_job_title)
                                    <span class="br-cell-muted">{{ $order->beautician_job_title }}</span>
                                @endif
                            </td>
                            <td data-label="{{ trans('beauticianreport::admin.table.appointment_date') }}">
                                @if ($order->appointment_date)
                                    <strong>{{ $order->appointment_date->format('d M Y') }}</strong>
                                    <span class="br-cell-muted">{{ $order->appointment_time }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td data-label="{{ trans('beauticianreport::admin.table.status') }}">
                                <span class="br-status-pill br-status-pill--{{ $order->status }}">
                                    {{ trans("order::statuses.{$order->status}") }}
                                </span>
                            </td>
                            <td data-label="{{ trans('beauticianreport::admin.table.products') }}">{{ $order->total_products }}</td>
                            <td class="text-right" data-label="{{ trans('beauticianreport::admin.table.total') }}">
                                <strong>{{ $order->total->format() }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="br-empty-state">
                                    <i class="fa fa-inbox"></i>
                                    <p>{{ trans('beauticianreport::admin.no_data') }}</p>
                                </div>
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
    </div>
@endsection
