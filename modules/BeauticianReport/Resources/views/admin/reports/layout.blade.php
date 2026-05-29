@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('beauticianreport::admin.analytics'))

    <li><a href="{{ route('admin.beautician_reports.index') }}">{{ trans('beauticianreport::admin.analytics') }}</a></li>
    <li class="active">{{ $currentReportLabel ?? trans('beauticianreport::admin.view_reports') }}</li>
@endcomponent

@section('content')
    @php
        $reportTypes = trans('beauticianreport::admin.filters.report_types');
        $currentReportLabel = $reportTypes[$request->type] ?? trans('beauticianreport::admin.view_reports');
    @endphp

    <div class="report-wrapper br-reports-modern">
        @hasSection('report_stats')
            @yield('report_stats')
        @endif

        <div class="br-reports-body">
            <div class="report-modern-panel br-filter-bar">
                <div class="br-filter-bar-top">
                    <div>
                        <h4 class="br-filter-title">{{ trans('beauticianreport::admin.filter') }}</h4>
                        <p class="br-filter-active">{{ $currentReportLabel }}</p>
                    </div>
                    <a href="{{ route('admin.beautician_reports.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-line-chart"></i> {{ trans('beauticianreport::admin.dashboard') }}
                    </a>
                </div>

                <form method="GET" action="{{ route('admin.beautician_reports.index') }}" class="br-filter-form">
                    <div class="br-filter-grid">
                        <div class="form-group br-filter-field br-filter-field--type">
                            <label for="report-type">{{ trans('beauticianreport::admin.filters.report_type') }}</label>
                            <select name="type" id="report-type" class="custom-select-black">
                                @foreach ($reportTypes as $type => $label)
                                    <option value="{{ $type }}" {{ $request->type === $type ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @yield('filters')
                    </div>

                    <div class="br-filter-actions">
                        <button type="submit" class="btn btn-primary" data-loading>
                            <i class="fa fa-filter"></i> {{ trans('beauticianreport::admin.filter') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="report-modern-panel br-result-panel">
                @yield('report_result')
            </div>
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/BeauticianReport/Resources/assets/admin/sass/main.scss',
    ])
@endpush
