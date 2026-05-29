<section class="tr-dashboard-section">
    <div class="tr-section-head">
        <h2 class="tr-section-head__title">{{ trans('treatmentreservation::admin.stats.pipeline_title') }}</h2>
        <p class="tr-section-head__lead">{{ trans('treatmentreservation::admin.dashboard.pipeline_lead') }}</p>
    </div>

    <div class="tr-stat-grid tr-stat-grid--3">
        @include('treatmentreservation::admin.reservations.partials.stat-card', [
            'variant' => 'pending',
            'icon' => 'fa-clock-o',
            'label' => trans('treatmentreservation::admin.kanban.pending'),
            'value' => number_format($stats['pending']),
            'hint' => trans('treatmentreservation::admin.stats.pending_hint'),
        ])

        @include('treatmentreservation::admin.reservations.partials.stat-card', [
            'variant' => 'in_progress',
            'icon' => 'fa-spinner',
            'label' => trans('treatmentreservation::admin.kanban.in_progress'),
            'value' => number_format($stats['inProgress']),
            'hint' => trans('treatmentreservation::admin.stats.in_progress_hint'),
        ])

        @include('treatmentreservation::admin.reservations.partials.stat-card', [
            'variant' => 'completed',
            'icon' => 'fa-check-circle',
            'label' => trans('treatmentreservation::admin.kanban.completed'),
            'value' => number_format($stats['completed']),
            'hint' => trans('treatmentreservation::admin.stats.completed_hint'),
        ])
    </div>
</section>

@include('treatmentreservation::admin.reservations.partials.analytics', [
    'analytics' => $analytics,
    'analyticsCharts' => $analyticsCharts,
])

<section class="tr-dashboard-section">
    <div class="tr-section-head">
        <h2 class="tr-section-head__title">{{ trans('treatmentreservation::admin.dashboard.shortcuts_title') }}</h2>
        <p class="tr-section-head__lead">{{ trans('treatmentreservation::admin.dashboard.shortcuts_lead') }}</p>
    </div>

    <div class="tr-action-cards">
        <a href="{{ route('admin.treatment_reservations.index', ['view' => 'calendar']) }}" class="tr-action-card">
            <span class="tr-action-card__icon tr-action-card__icon--calendar"><i class="fa fa-calendar" aria-hidden="true"></i></span>
            <span class="tr-action-card__body">
                <strong>{{ trans('treatmentreservation::admin.tabs.calendar') }}</strong>
                <span>{{ trans('treatmentreservation::admin.tabs.calendar_desc') }}</span>
            </span>
            <i class="fa fa-chevron-right tr-action-card__arrow" aria-hidden="true"></i>
        </a>
        <a href="{{ route('admin.treatment_reservations.index', ['view' => 'kanban']) }}" class="tr-action-card">
            <span class="tr-action-card__icon tr-action-card__icon--kanban"><i class="fa fa-columns" aria-hidden="true"></i></span>
            <span class="tr-action-card__body">
                <strong>{{ trans('treatmentreservation::admin.tabs.kanban') }}</strong>
                <span>{{ trans('treatmentreservation::admin.tabs.kanban_desc') }}</span>
            </span>
            <i class="fa fa-chevron-right tr-action-card__arrow" aria-hidden="true"></i>
        </a>
        <a href="{{ route('admin.treatment_reservations.index', ['view' => 'reports']) }}" class="tr-action-card">
            <span class="tr-action-card__icon tr-action-card__icon--reports"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
            <span class="tr-action-card__body">
                <strong>{{ trans('treatmentreservation::admin.tabs.reports') }}</strong>
                <span>{{ trans('treatmentreservation::admin.tabs.reports_desc') }}</span>
            </span>
            <i class="fa fa-chevron-right tr-action-card__arrow" aria-hidden="true"></i>
        </a>
    </div>
</section>

<section class="tr-dashboard-section tr-dashboard-section--calendar">
    <div class="tr-section-head tr-section-head--inline">
        <div>
            <h2 class="tr-section-head__title">{{ trans('treatmentreservation::admin.calendar.title') }}</h2>
            <p class="tr-section-head__lead">{{ trans('treatmentreservation::admin.calendar.embedded_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.treatment_reservations.index', ['view' => 'calendar']) }}" class="btn btn-default btn-sm">
            {{ trans('treatmentreservation::admin.calendar.full_view') }}
            <i class="fa fa-external-link m-l-5" aria-hidden="true"></i>
        </a>
    </div>

    @include('treatmentreservation::admin.reservations.partials.calendar', ['embedded' => true])
</section>
