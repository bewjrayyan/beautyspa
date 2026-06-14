@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
@endphp

@php
    $analytics = $analytics ?? [];
    $analyticsCharts = $analyticsCharts ?? [];
    $kpis = $kpis ?? [];
    $completionRate = (int) ($analytics['conversionRate'] ?? $analytics['completionRate'] ?? 0);
    $noShowRate = (int) ($analytics['noShowRate'] ?? 0);
    $doneCount = (int) ($kpis['completed'] ?? 0);
    $activeCount = (int) ($kpis['inProgress'] ?? 0);
    $waitCount = (int) ($kpis['pending'] ?? 0);
@endphp

<section class="tr-crm-panel tr-crm-panel--stats">
    <header class="tr-crm-panel__head">
        <div>
            <h3 class="tr-crm-panel__title">{{ TrLang::trans('admin.crm.stats_title_long') }}</h3>
        </div>
    </header>

    <div class="tr-crm-stats">
        <div class="tr-crm-stat">
            <div class="tr-crm-stat__head">
                <span>{{ TrLang::trans('admin.crm.metric_fulfillment') }}</span>
                <strong>{{ $completionRate }}%<em>{{ TrLang::trans('admin.crm.metric_optimal') }}</em></strong>
            </div>
            <div class="tr-crm-stat__bar" role="presentation">
                <span style="width: {{ min(100, max(0, $completionRate)) }}%"></span>
            </div>
        </div>

        <div class="tr-crm-stat">
            <div class="tr-crm-stat__head">
                <span>{{ TrLang::trans('admin.crm.metric_no_show') }}</span>
                <strong>{{ $noShowRate }}%<em>{{ $noShowRate <= 10 ? TrLang::trans('admin.crm.no_show_low_risk') : TrLang::trans('admin.crm.no_show_watch') }}</em></strong>
            </div>
            <div class="tr-crm-stat__bar tr-crm-stat__bar--warning" role="presentation">
                <span style="width: {{ min(100, max(0, $noShowRate)) }}%"></span>
            </div>
        </div>

        <div class="tr-crm-stat tr-crm-stat--revenue">
            <span class="tr-crm-stat__label">{{ TrLang::trans('admin.crm.metric_revenue_checked') }}</span>
            <strong class="tr-crm-stat__value">{{ $analytics['revenueFormatted'] ?? '—' }}</strong>
            <p class="tr-crm-stat__hint">{{ TrLang::trans('admin.crm.revenue_checked_hint', ['count' => $doneCount]) }}</p>
        </div>

        <div class="tr-crm-stat-distribution">
            <span class="tr-crm-stat-distribution__label">{{ TrLang::trans('admin.crm.distribution') }}</span>
            <div class="tr-crm-stat-distribution__items">
                <span class="tr-crm-stat-distribution__item tr-crm-stat-distribution__item--done">
                    {{ TrLang::trans('admin.crm.distribution_done', ['count' => $doneCount]) }}
                </span>
                <span class="tr-crm-stat-distribution__item tr-crm-stat-distribution__item--active">
                    {{ TrLang::trans('admin.crm.distribution_active', ['count' => $activeCount]) }}
                </span>
                <span class="tr-crm-stat-distribution__item tr-crm-stat-distribution__item--wait">
                    {{ TrLang::trans('admin.crm.distribution_wait', ['count' => $waitCount]) }}
                </span>
            </div>
        </div>
    </div>

    <div
        class="tr-crm-stats-chart"
        id="tr-analytics"
        data-chart-empty="{{ TrLang::trans('admin.analytics.chart_empty') }}"
        data-chart-revenue-trend-empty="{{ TrLang::trans('admin.analytics.revenue_trend_empty') }}"
        data-chart-label-revenue="{{ TrLang::trans('admin.analytics.chart_label_revenue') }}"
        data-chart-label-bookings="{{ TrLang::trans('admin.analytics.chart_label_bookings') }}"
    >
        <h4>{{ TrLang::trans('admin.crm.revenue_trend_title') }}</h4>
        <p class="tr-crm-stats-chart__lead">{{ TrLang::trans('admin.crm.revenue_trend_lead') }}</p>
        <div class="tr-crm-chart__canvas tr-crm-chart__canvas--mini">
            <canvas id="tr-revenue-trend-chart" height="120"></canvas>
        </div>
    </div>
</section>

@push('globals')
    <script>
        window.TRAnalytics = @json($analyticsCharts);
    </script>
@endpush
