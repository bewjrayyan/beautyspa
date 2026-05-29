@extends('report::admin.reports.layout')

@section('filters')
    @include('report::admin.reports.filters.from')
    @include('report::admin.reports.filters.to')

    <div class="form-group">
        <label for="type">{{ trans('loyalty::reports.type') }}</label>
        <select name="type" id="type" class="form-control custom-select-black">
            <option value="">{{ trans('loyalty::reports.all_types') }}</option>
            @foreach ($types as $value => $label)
                <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
@endsection

@section('report_result')
    <div class="box-header">
        <h5>{{ trans('loyalty::reports.transaction_report') }}</h5>
    </div>

    <div class="box-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ trans('loyalty::reports.date') }}</th>
                        <th>{{ trans('loyalty::reports.customer') }}</th>
                        <th>{{ trans('loyalty::reports.type') }}</th>
                        <th>{{ trans('loyalty::reports.points') }}</th>
                        <th>{{ trans('loyalty::reports.balance') }}</th>
                        <th>{{ trans('loyalty::reports.description') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report as $row)
                        <tr>
                            <td>{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $row->customer_email }}<br><small>{{ $row->first_name }} {{ $row->last_name }}</small></td>
                            <td>{{ $types[$row->type] ?? $row->type }}</td>
                            <td>{{ $row->points > 0 ? '+' : '' }}{{ $row->points }}</td>
                            <td>{{ number_format($row->balance_after) }}</td>
                            <td>{{ $row->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="6">{{ trans('report::admin.no_data') }}</td>
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
