@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('report::admin.reports'))

    <li class="active">{{ trans('report::admin.reports') }}</li>
@endcomponent

@section('content')
    @php
        $reportTypes = trans('report::admin.filters.report_types');
        $currentReportLabel = $reportTypes[$request->type] ?? trans('report::admin.reports');
        $isBookingsLayout = ($reportLayoutMode ?? 'full') === 'bookings';
        $resetUrl = route('admin.reports.index', ['type' => $request->type]);
    @endphp

    <div class="report-wrapper report-modern {{ $isBookingsLayout ? 'report-modern--bookings' : '' }}">
        @if ($isBookingsLayout)
            @include('report::admin.reports.partials.booking_stats')
        @else
            @include('report::admin.reports.partials.dashboard')
        @endif

        <div class="report-modern-body">
            <div class="report-filter-panel report-modern-panel {{ $isBookingsLayout ? 'report-filter-panel--compact' : '' }}">
                @unless ($isBookingsLayout)
                    <div class="report-filter-panel__header">
                        <div class="report-filter-panel__intro">
                            <span class="report-filter-panel__icon" aria-hidden="true">
                                <i class="fa fa-sliders"></i>
                            </span>
                            <div>
                                <h4 class="report-filter-panel__title">{{ trans('report::admin.filter') }}</h4>
                                <p class="report-filter-panel__subtitle">{{ trans('report::admin.filter_subtitle') }}</p>
                            </div>
                        </div>

                        <div class="report-filter-panel__meta">
                            <span class="report-filter-chip">
                                <i class="fa fa-bar-chart" aria-hidden="true"></i>
                                {{ $currentReportLabel }}
                            </span>

                            @if ($beauticianAnalyticsUrl ?? null)
                                @hasAccess('admin.beautician_reports.index')
                                    <a href="{{ $beauticianAnalyticsUrl }}" class="report-filter-link-btn">
                                        <i class="fa fa-line-chart"></i>
                                        {{ trans('report::admin.beautician_analytics') }}
                                    </a>
                                @endHasAccess
                            @endif
                        </div>
                    </div>
                @endunless

                <form method="GET" action="{{ route('admin.reports.index') }}" class="report-filter-form">
                    <div class="report-filter-panel__type">
                        <label class="report-field__label" for="report-type">
                            {{ trans('report::admin.filters.report_type') }}
                        </label>
                        <div class="report-filter-type-select">
                            <i class="fa fa-file-text-o report-filter-type-select__leading" aria-hidden="true"></i>
                            <select name="type" id="report-type" class="custom-select-black report-filter-type-select__input">
                                @foreach ($reportTypes as $type => $label)
                                    @if ($type === 'beautician_bookings_report' && !($showBeauticianAnalytics ?? false))
                                        @continue
                                    @endif
                                    <option value="{{ $type }}" {{ $request->type === $type ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="fa fa-chevron-down report-filter-type-select__caret" aria-hidden="true"></i>
                        </div>
                    </div>

                    <div class="report-filter-panel__body">
                        @hasSection('filter_zones')
                            <div class="report-filter-stack">
                                @yield('filter_zones')
                            </div>
                        @else
                            <div class="report-filter-grid">
                                @yield('filters')
                            </div>
                        @endif
                    </div>

                    <div class="report-filter-panel__footer">
                        <a href="{{ $resetUrl }}" class="report-filter-reset">
                            <i class="fa fa-undo" aria-hidden="true"></i>
                            {{ trans('report::admin.reset_filters') }}
                        </a>

                        <div class="report-filter-panel__footer-actions">
                            @if ($isBookingsLayout && ($beauticianAnalyticsUrl ?? null))
                                @hasAccess('admin.beautician_reports.index')
                                    <a href="{{ $beauticianAnalyticsUrl }}" class="btn btn-default report-filter-secondary-btn">
                                        <i class="fa fa-line-chart"></i>
                                        {{ trans('report::admin.beautician_analytics') }}
                                    </a>
                                @endHasAccess
                            @endif

                            <button type="submit" class="btn btn-primary report-filter-apply" data-loading>
                                <i class="fa fa-filter" aria-hidden="true"></i>
                                {{ trans('report::admin.filter') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="report-result box report-modern-panel report-result-full {{ $isBookingsLayout ? 'report-result--bookings' : '' }}">
                <div class="report-result__toolbar">
                    @include('report::admin.reports.partials.export')
                </div>

                @yield('report_result')
            </div>
        </div>
    </div>
@endsection

@push('globals')
    @vite(['modules/Report/Resources/assets/admin/sass/main.scss'])

    @if ($isBookingsLayout)
        @vite(['modules/Report/Resources/assets/admin/js/bookings.js'])
    @else
        @vite(['modules/Report/Resources/assets/admin/js/main.js'])
    @endif

    <script>
        window.ReportProductAutocompleteLang = @json([
            'notFound' => trans('report::admin.filters.product_not_found'),
            'resultsCount' => trans('report::admin.filters.product_results_count'),
        ]);
    </script>

    @unless ($isBookingsLayout)
        @php
            $reportDashboardCharts = [
                'enabled' => true,
                'salesTrend' => $reportDashboard['salesTrend'] ?? [],
                'treatmentSalesTrend' => $reportDashboard['treatmentSalesTrend'] ?? [],
                'salesByBeautician' => $reportDashboard['salesByBeautician'] ?? ['labels' => [], 'amounts' => []],
                'hasBeautician' => $reportDashboard['hasBeautician'] ?? false,
            ];
        @endphp
        <script>
            window.ReportDashboardCharts = @json($reportDashboardCharts);
        </script>
    @endunless
@endpush
