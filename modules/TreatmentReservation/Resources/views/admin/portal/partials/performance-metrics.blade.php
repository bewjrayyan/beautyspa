@php
    $pipelineTotal = (int) $stats['pending'] + (int) $stats['inProgress'] + (int) $stats['completed'];
    $pipelineBase = max($pipelineTotal, 1);
    $pendingWidth = round(((int) $stats['pending'] / $pipelineBase) * 100, 1);
    $progressWidth = round(((int) $stats['inProgress'] / $pipelineBase) * 100, 1);
    $completedWidth = round(100 - $pendingWidth - $progressWidth, 1);
@endphp

<section class="tr-portal-saas-performance" aria-labelledby="tr-portal-performance-title">
    <header class="tr-portal-saas-performance__header">
        <div class="tr-portal-saas-performance__intro">
            <span class="tr-portal-saas-performance__eyebrow">
                {{ trans('treatmentreservation::admin.stats.performance_period_title') }}
            </span>
            <h2 class="tr-portal-saas-performance__title" id="tr-portal-performance-title">
                {{ trans('treatmentreservation::admin.portal.performance_title') }}
            </h2>
            <p class="tr-portal-saas-performance__lead">
                {{ trans('treatmentreservation::admin.portal.performance_subtitle') }}
            </p>
        </div>

        <div class="tr-portal-saas-performance__summary" aria-label="{{ trans('treatmentreservation::admin.stats.pipeline_title') }}">
            <span class="tr-portal-saas-performance__summary-value">{{ number_format($pipelineTotal) }}</span>
            <span class="tr-portal-saas-performance__summary-label">
                {{ trans_choice('treatmentreservation::admin.portal.performance_pipeline_jobs', $pipelineTotal, ['count' => $pipelineTotal]) }}
            </span>
        </div>
    </header>

    <div class="tr-portal-saas-performance__kpis">
        @include('treatmentreservation::admin.portal.partials.portal-stat', [
            'tone' => 'revenue',
            'icon' => 'fa-line-chart',
            'label' => trans('treatmentreservation::admin.stats.treatment_revenue'),
            'value' => $performanceStats['treatmentRevenue']->format(),
            'hint' => trans('treatmentreservation::admin.stats.treatment_revenue_hint'),
            'featured' => true,
        ])

        @include('treatmentreservation::admin.portal.partials.portal-stat', [
            'tone' => 'success',
            'icon' => 'fa-check',
            'label' => trans('treatmentreservation::admin.stats.week_completed'),
            'value' => number_format($performanceStats['weekCompleted']),
            'hint' => trans('treatmentreservation::admin.stats.week_completed_hint'),
        ])

        @include('treatmentreservation::admin.portal.partials.portal-stat', [
            'tone' => 'violet',
            'icon' => 'fa-calendar-check-o',
            'label' => trans('treatmentreservation::admin.stats.upcoming_week'),
            'value' => number_format($performanceStats['upcomingWeek']),
            'hint' => trans('treatmentreservation::admin.stats.upcoming_week_hint'),
        ])

        @include('treatmentreservation::admin.portal.partials.portal-stat', [
            'tone' => 'sky',
            'icon' => 'fa-sun-o',
            'label' => trans('treatmentreservation::admin.stats.today_completed'),
            'value' => number_format($performanceStats['todayCompleted']),
            'hint' => trans('treatmentreservation::admin.stats.today_completed_hint'),
        ])
    </div>

    <div class="tr-portal-saas-performance__pipeline">
        <div class="tr-portal-saas-performance__pipeline-head">
            <h3>{{ trans('treatmentreservation::admin.stats.pipeline_title') }}</h3>
            <p>{{ trans('treatmentreservation::admin.portal.performance_pipeline_lead') }}</p>
        </div>

        <div
            class="tr-portal-pipeline-bar"
            role="img"
            aria-label="{{ trans('treatmentreservation::admin.portal.performance_pipeline_aria', [
                'pending' => number_format($stats['pending']),
                'progress' => number_format($stats['inProgress']),
                'completed' => number_format($stats['completed']),
            ]) }}"
        >
            <span class="tr-portal-pipeline-bar__segment tr-portal-pipeline-bar__segment--pending" style="width: {{ $pendingWidth }}%;"></span>
            <span class="tr-portal-pipeline-bar__segment tr-portal-pipeline-bar__segment--progress" style="width: {{ $progressWidth }}%;"></span>
            <span class="tr-portal-pipeline-bar__segment tr-portal-pipeline-bar__segment--completed" style="width: {{ $completedWidth }}%;"></span>
        </div>

        <div class="tr-portal-pipeline-legend">
            <div class="tr-portal-pipeline-legend__item tr-portal-pipeline-legend__item--pending">
                <span class="tr-portal-pipeline-legend__dot" aria-hidden="true"></span>
                <div class="tr-portal-pipeline-legend__copy">
                    <span class="tr-portal-pipeline-legend__value">{{ number_format($stats['pending']) }}</span>
                    <span class="tr-portal-pipeline-legend__label">{{ trans('treatmentreservation::admin.kanban.pending') }}</span>
                </div>
            </div>

            <div class="tr-portal-pipeline-legend__item tr-portal-pipeline-legend__item--progress">
                <span class="tr-portal-pipeline-legend__dot" aria-hidden="true"></span>
                <div class="tr-portal-pipeline-legend__copy">
                    <span class="tr-portal-pipeline-legend__value">{{ number_format($stats['inProgress']) }}</span>
                    <span class="tr-portal-pipeline-legend__label">{{ trans('treatmentreservation::admin.kanban.in_progress') }}</span>
                </div>
            </div>

            <div class="tr-portal-pipeline-legend__item tr-portal-pipeline-legend__item--completed">
                <span class="tr-portal-pipeline-legend__dot" aria-hidden="true"></span>
                <div class="tr-portal-pipeline-legend__copy">
                    <span class="tr-portal-pipeline-legend__value">{{ number_format($stats['completed']) }}</span>
                    <span class="tr-portal-pipeline-legend__label">{{ trans('treatmentreservation::admin.kanban.completed') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>
