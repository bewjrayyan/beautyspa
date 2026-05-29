@extends('beauticianreport::admin.reports.layout')

@section('filters')
    @include('report::admin.reports.filters.from')
    @include('report::admin.reports.filters.to')
    @include('report::admin.reports.filters.status')
    @include('beauticianreport::admin.reports.filters.beautician')
@endsection

@section('report_result')
    <div class="box-header">
        <h5>{{ trans('beauticianreport::admin.filters.report_types.appointments_report') }}</h5>
    </div>

    <div class="box-body">
        <div class="table-responsive anchor-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ trans('beauticianreport::admin.table.appointment_date') }}</th>
                        <th>{{ trans('beauticianreport::admin.table.appointment_time') }}</th>
                        <th>{{ trans('beauticianreport::admin.table.beautician') }}</th>
                        <th>{{ trans('beauticianreport::admin.table.job_title') }}</th>
                        <th>{{ trans('beauticianreport::admin.table.orders') }}</th>
                        <th>{{ trans('beauticianreport::admin.table.total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report as $data)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($data->appointment_date)->format('d M Y') }}</td>
                            <td>{{ $data->appointment_time }}</td>
                            <td>{{ $data->beautician_name }}</td>
                            <td>{{ $data->beautician_job_title ?: '—' }}</td>
                            <td>{{ $data->total_orders }}</td>
                            <td>{{ \Modules\Support\Money::inDefaultCurrency($data->total)->format() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="6">{{ trans('beauticianreport::admin.no_data') }}</td>
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
