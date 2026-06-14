@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
@endphp

@php
    $analytics = $analytics ?? [];
    $analyticsCharts = $analyticsCharts ?? [];
@endphp

<div class="tr-crm-panel tr-crm-panel--analytics">
    <header class="tr-crm-panel__head">
        <div>
            <h3 class="tr-crm-panel__title">{{ TrLang::trans('admin.crm.analytics_title') }}</h3>
            <p class="tr-crm-panel__lead">{{ TrLang::trans('admin.analytics.subtitle', ['days' => $analytics['periodDays'] ?? 30]) }}</p>
        </div>
    </header>

    <div class="tr-crm-metrics">
        <div class="tr-crm-metric">
            <span class="tr-crm-metric__label">{{ TrLang::trans('admin.crm.metric_revenue') }}</span>
            <strong class="tr-crm-metric__value">{{ $analytics['revenueFormatted'] ?? '—' }}</strong>
        </div>
        <div class="tr-crm-metric">
            <span class="tr-crm-metric__label">{{ TrLang::trans('admin.crm.metric_completion') }}</span>
            <strong class="tr-crm-metric__value">{{ ($analytics['completionRate'] ?? 0) . '%' }}</strong>
        </div>
        <div class="tr-crm-metric">
            <span class="tr-crm-metric__label">{{ TrLang::trans('admin.crm.metric_fulfillment') }}</span>
            <strong class="tr-crm-metric__value">{{ ($analytics['conversionRate'] ?? 0) . '%' }}</strong>
        </div>
        <div class="tr-crm-metric">
            <span class="tr-crm-metric__label">{{ TrLang::trans('admin.crm.metric_no_show') }}</span>
            <strong class="tr-crm-metric__value">{{ ($analytics['noShowRate'] ?? 0) . '%' }}</strong>
        </div>
    </div>

    <div
        class="tr-crm-charts"
        id="tr-analytics"
        data-chart-empty="{{ TrLang::trans('admin.analytics.chart_empty') }}"
        data-chart-revenue-trend-empty="{{ TrLang::trans('admin.analytics.revenue_trend_empty') }}"
        data-chart-label-revenue="{{ TrLang::trans('admin.analytics.chart_label_revenue') }}"
        data-chart-label-bookings="{{ TrLang::trans('admin.analytics.chart_label_bookings') }}"
    >
        <div class="tr-crm-charts__grid">
            <div class="tr-crm-chart">
                <h4>{{ TrLang::trans('admin.analytics.revenue_trend') }}</h4>
                <div class="tr-crm-chart__canvas">
                    <canvas id="tr-revenue-trend-chart" height="180"></canvas>
                </div>
            </div>
            <div class="tr-crm-chart">
                <h4>{{ TrLang::trans('admin.analytics.status_breakdown') }}</h4>
                <div class="tr-crm-chart__canvas tr-crm-chart__canvas--compact">
                    <canvas id="tr-status-breakdown-chart" height="180"></canvas>
                </div>
            </div>
            <div class="tr-crm-chart tr-crm-chart--wide">
                <h4>{{ TrLang::trans('admin.analytics.revenue_by_beautician') }}</h4>
                <div class="tr-crm-chart__canvas">
                    <canvas id="tr-revenue-by-beautician-chart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('globals')
    <script>
        window.TRAnalytics = @json($analyticsCharts);
    </script>
@endpush
