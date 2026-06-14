<section class="tr-dashboard-section tr-dashboard-section--analytics">
    <div class="tr-section-head">
        <h2 class="tr-section-head__title">{{ trans('treatmentreservation::admin.analytics.title') }}</h2>
        <p class="tr-section-head__lead">{{ trans('treatmentreservation::admin.analytics.subtitle', ['days' => $analytics['periodDays']]) }}</p>
    </div>

    <div class="tr-stat-grid tr-stat-grid--4 tr-analytics-stats">
        @include('treatmentreservation::admin.reservations.partials.stat-card', [
            'variant' => 'revenue',
            'icon' => 'fa-money',
            'label' => trans('treatmentreservation::admin.analytics.revenue'),
            'value' => $analytics['revenueFormatted'],
            'hint' => trans('treatmentreservation::admin.analytics.revenue_hint', [
                'days' => $analytics['periodDays'],
                'count' => number_format($analytics['completed']),
            ]),
        ])

        @include('treatmentreservation::admin.reservations.partials.stat-card', [
            'variant' => 'completed',
            'icon' => 'fa-check-circle',
            'label' => trans('treatmentreservation::admin.analytics.completion_rate'),
            'value' => $analytics['completionRate'] . '%',
            'hint' => trans('treatmentreservation::admin.analytics.completion_rate_hint'),
        ])

        @include('treatmentreservation::admin.reservations.partials.stat-card', [
            'variant' => 'in_progress',
            'icon' => 'fa-line-chart',
            'label' => trans('treatmentreservation::admin.analytics.conversion_rate'),
            'value' => $analytics['conversionRate'] . '%',
            'hint' => trans('treatmentreservation::admin.analytics.conversion_rate_hint'),
        ])

        @include('treatmentreservation::admin.reservations.partials.stat-card', [
            'variant' => 'pending',
            'icon' => 'fa-user-times',
            'label' => trans('treatmentreservation::admin.analytics.no_show_rate'),
            'value' => $analytics['noShowRate'] . '%',
            'hint' => trans('treatmentreservation::admin.analytics.no_show_rate_hint', ['count' => number_format($analytics['noShows'])]),
        ])
    </div>

    <div
        class="tr-analytics-charts box"
        id="tr-analytics"
        data-chart-empty="{{ trans('treatmentreservation::admin.analytics.chart_empty') }}"
        data-chart-revenue-trend-empty="{{ trans('treatmentreservation::admin.analytics.revenue_trend_empty') }}"
        data-chart-label-revenue="{{ trans('treatmentreservation::admin.analytics.chart_label_revenue') }}"
        data-chart-label-bookings="{{ trans('treatmentreservation::admin.analytics.chart_label_bookings') }}"
    >
        <div class="tr-analytics-charts__grid">
            <div class="tr-analytics-chart">
                <h5>{{ trans('treatmentreservation::admin.analytics.revenue_trend') }}</h5>
                <div class="tr-analytics-chart__canvas">
                    <canvas id="tr-revenue-trend-chart" height="220"></canvas>
                </div>
            </div>

            <div class="tr-analytics-chart">
                <h5>{{ trans('treatmentreservation::admin.analytics.status_breakdown') }}</h5>
                <div class="tr-analytics-chart__canvas tr-analytics-chart__canvas--compact">
                    <canvas id="tr-status-breakdown-chart" height="220"></canvas>
                </div>
            </div>

            <div class="tr-analytics-chart tr-analytics-chart--wide">
                <h5>{{ trans('treatmentreservation::admin.analytics.revenue_by_beautician') }}</h5>
                <div class="tr-analytics-chart__canvas">
                    <canvas id="tr-revenue-by-beautician-chart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>
</section>

@push('globals')
    <script>
        window.TRAnalytics = @json($analyticsCharts);
    </script>
@endpush
