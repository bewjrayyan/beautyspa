@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
@endphp

@php
    $analytics = $analytics ?? [];
    $analyticsCharts = $analyticsCharts ?? [];
    $kpis = $kpis ?? [];
    $completionRate = (int) round($analytics['conversionRate'] ?? $analytics['completionRate'] ?? 0);
    $noShowRate = (int) round($analytics['noShowRate'] ?? 0);
    $doneCount = (int) ($kpis['completed'] ?? 0);
    $activeCount = (int) ($kpis['inProgress'] ?? 0);
    $waitCount = (int) ($kpis['pending'] ?? 0);
    $pipelineTotal = $doneCount + $activeCount + $waitCount;
    $distributionTotal = max($pipelineTotal, 1);
    $donePct = $pipelineTotal > 0 ? (int) round(($doneCount / $pipelineTotal) * 100) : 0;
    $activePct = $pipelineTotal > 0 ? (int) round(($activeCount / $pipelineTotal) * 100) : 0;
    $waitPct = $pipelineTotal > 0 ? max(0, 100 - $donePct - $activePct) : 0;
    $distributionSegments = array_values(array_filter([
        [
            'key' => 'done',
            'count' => $doneCount,
            'pct' => $donePct,
            'width' => round(($doneCount / $distributionTotal) * 100, 1),
            'label' => TrLang::trans('admin.crm.distribution_segment_done'),
        ],
        [
            'key' => 'active',
            'count' => $activeCount,
            'pct' => $activePct,
            'width' => round(($activeCount / $distributionTotal) * 100, 1),
            'label' => TrLang::trans('admin.crm.distribution_segment_active'),
        ],
        [
            'key' => 'wait',
            'count' => $waitCount,
            'pct' => $waitPct,
            'width' => round(($waitCount / $distributionTotal) * 100, 1),
            'label' => TrLang::trans('admin.crm.distribution_segment_wait'),
        ],
    ], fn (array $segment) => $segment['count'] > 0));
    $distributionBreakdown = [
        [
            'key' => 'done',
            'count' => $doneCount,
            'pct' => $donePct,
            'label' => TrLang::trans('admin.crm.distribution_segment_done'),
        ],
        [
            'key' => 'active',
            'count' => $activeCount,
            'pct' => $activePct,
            'label' => TrLang::trans('admin.crm.distribution_segment_active'),
        ],
        [
            'key' => 'wait',
            'count' => $waitCount,
            'pct' => $waitPct,
            'label' => TrLang::trans('admin.crm.distribution_segment_wait'),
        ],
    ];
    $revenueTrend = $analyticsCharts['revenueTrend'] ?? [];
    $peakRevenue = $revenueTrend['peakFormatted'] ?? '—';
    $noShowBadge = $noShowRate <= 10
        ? TrLang::trans('admin.crm.no_show_low_risk')
        : TrLang::trans('admin.crm.no_show_watch');
    $noShowBadgeVariant = $noShowRate <= 10 ? 'success' : 'warning';
@endphp

<section class="tr-crm-panel tr-crm-panel--stats">
    <header class="tr-crm-panel__head tr-crm-panel__head--stats">
        <h3 class="tr-crm-panel__title">{{ TrLang::trans('admin.crm.stats_title_long') }}</h3>
    </header>

    <div class="tr-crm-stats-grid">
        <article class="tr-crm-stat-card">
            <span class="tr-crm-stat-card__label">{{ TrLang::trans('admin.crm.metric_fulfillment_pct') }}</span>
            <div class="tr-crm-stat-card__value-row">
                <strong class="tr-crm-stat-card__value">{{ $completionRate }}%</strong>
                <span class="tr-crm-stat-card__badge tr-crm-stat-card__badge--success">{{ TrLang::trans('admin.crm.metric_optimal') }}</span>
            </div>
            <div class="tr-crm-stat-card__bar tr-crm-stat-card__bar--success" role="presentation" aria-hidden="true">
                <span style="width: {{ min(100, max(0, $completionRate)) }}%"></span>
            </div>
        </article>

        <article class="tr-crm-stat-card">
            <span class="tr-crm-stat-card__label">{{ TrLang::trans('admin.crm.metric_no_show_pct') }}</span>
            <div class="tr-crm-stat-card__value-row">
                <strong class="tr-crm-stat-card__value">{{ $noShowRate }}%</strong>
                <span class="tr-crm-stat-card__badge tr-crm-stat-card__badge--{{ $noShowBadgeVariant }}">{{ $noShowBadge }}</span>
            </div>
            <div class="tr-crm-stat-card__bar tr-crm-stat-card__bar--danger" role="presentation" aria-hidden="true">
                <span style="width: {{ min(100, max(0, $noShowRate)) }}%"></span>
            </div>
        </article>

        <article class="tr-crm-stat-card tr-crm-stat-card--revenue">
            <span class="tr-crm-stat-card__label">{{ TrLang::trans('admin.crm.metric_revenue_checked') }}</span>
            <strong class="tr-crm-stat-card__value tr-crm-stat-card__value--revenue">{{ $analytics['revenueFormatted'] ?? '—' }}</strong>
            <p class="tr-crm-stat-card__hint">{{ TrLang::trans('admin.crm.revenue_checked_hint', ['count' => $doneCount]) }}</p>
        </article>

        <article class="tr-crm-stat-card tr-crm-stat-card--distribution">
            <div class="tr-crm-stat-card__label-row">
                <i class="fa fa-pie-chart" aria-hidden="true"></i>
                <span class="tr-crm-stat-card__label">{{ TrLang::trans('admin.crm.distribution') }}</span>
                <span class="tr-crm-stat-card__dist-total">{{ TrLang::trans('admin.crm.distribution_total', ['count' => number_format($pipelineTotal)]) }}</span>
            </div>

            @if ($pipelineTotal > 0)
                <div
                    class="tr-crm-stat-card__dist-bar"
                    role="img"
                    aria-label="{{ TrLang::trans('admin.crm.distribution_compact', ['done' => $doneCount, 'active' => $activeCount, 'wait' => $waitCount]) }}"
                >
                    @foreach ($distributionSegments as $segment)
                        <span
                            class="tr-crm-stat-card__dist-segment tr-crm-stat-card__dist-segment--{{ $segment['key'] }}"
                            style="width: {{ $segment['width'] }}%"
                            title="{{ $segment['label'] }}: {{ number_format($segment['count']) }} ({{ $segment['pct'] }}%)"
                        ></span>
                    @endforeach
                </div>

                <div class="tr-crm-stat-card__dist-breakdown" aria-label="{{ TrLang::trans('admin.crm.distribution_breakdown_aria') }}">
                    @foreach ($distributionBreakdown as $item)
                        <div class="tr-crm-stat-card__dist-item tr-crm-stat-card__dist-item--{{ $item['key'] }}{{ $item['count'] === 0 ? ' is-zero' : '' }}">
                            <span class="tr-crm-stat-card__dist-dot" aria-hidden="true"></span>
                            <span class="tr-crm-stat-card__dist-name">{{ $item['label'] }}</span>
                            <strong class="tr-crm-stat-card__dist-count">{{ number_format($item['count']) }}</strong>
                            <span class="tr-crm-stat-card__dist-pct">{{ $item['pct'] }}%</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="tr-crm-stat-card__dist-empty">{{ TrLang::trans('admin.crm.distribution_empty') }}</p>
            @endif
        </article>
    </div>

    <div
        class="tr-crm-stats-chart"
        id="tr-analytics"
        data-chart-empty="{{ TrLang::trans('admin.analytics.chart_empty') }}"
        data-chart-revenue-trend-empty="{{ TrLang::trans('admin.analytics.revenue_trend_empty') }}"
        data-chart-label-revenue="{{ TrLang::trans('admin.analytics.chart_label_revenue') }}"
        data-chart-label-bookings="{{ TrLang::trans('admin.analytics.chart_label_bookings') }}"
    >
        <header class="tr-crm-stats-chart__head">
            <div class="tr-crm-stats-chart__intro">
                <span class="tr-crm-stats-chart__icon" aria-hidden="true">
                    <i class="fa fa-line-chart"></i>
                </span>
                <div>
                    <h4 class="tr-crm-stats-chart__title">{{ TrLang::trans('admin.crm.revenue_trend_title') }}</h4>
                    <p class="tr-crm-stats-chart__lead">{{ TrLang::trans('admin.crm.revenue_trend_lead') }}</p>
                </div>
            </div>
            <div class="tr-crm-stats-chart__peak">
                <span class="tr-crm-stats-chart__peak-label">{{ TrLang::trans('admin.crm.peak_revenue') }}</span>
                <strong class="tr-crm-stats-chart__peak-value" id="tr-revenue-peak">{{ $peakRevenue }}</strong>
            </div>
        </header>
        <div class="tr-crm-chart__canvas tr-crm-chart__canvas--trend">
            <canvas id="tr-revenue-trend-chart" height="160"></canvas>
        </div>
    </div>
</section>

@push('globals')
    <script>
        window.TRAnalytics = @json($analyticsCharts);
    </script>
@endpush
