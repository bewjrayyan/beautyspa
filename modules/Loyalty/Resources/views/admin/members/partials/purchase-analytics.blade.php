@php
    $summary = $purchaseAnalytics['summary'] ?? [];
    $hasData = $purchaseAnalytics['has_data'] ?? false;
    $chartsPayload = null;

    if ($hasData) {
        $chartsPayload = array_merge($purchaseAnalytics['charts'] ?? [], [
            'bookings_label' => trans('loyalty::members.show.analytics_chart_treatments'),
            'purchases_label' => trans('loyalty::members.show.analytics_chart_retail'),
            'spend_label' => trans('loyalty::members.show.analytics_chart_spend'),
        ]);
    }
@endphp

<div class="loyalty-member-card loyalty-member-analytics">
    <div class="loyalty-member-card__head">
        <h3>
            <i class="fa fa-line-chart" aria-hidden="true"></i>
            {{ trans('loyalty::members.show.analytics_title') }}
        </h3>
        <p>{{ trans('loyalty::members.show.analytics_lead') }}</p>
    </div>

    <div class="loyalty-member-card__body loyalty-member-analytics__body">
        <div class="loyalty-member-analytics__stats">
            @include('admin::partials.fc_saas_stat', [
                'variant' => 'mint',
                'icon' => 'fa-calendar-check-o',
                'label' => trans('loyalty::members.show.analytics_last_visit'),
                'value' => $summary['last_visit'] ?? '—',
                'hint' => trans(
                    $summary['last_visit_hint_key'] ?? 'loyalty::members.show.analytics_no_visit_yet',
                    $summary['last_visit_hint_params'] ?? []
                ),
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'indigo',
                'icon' => 'fa-calendar-plus-o',
                'label' => trans('loyalty::members.show.analytics_next_visit'),
                'value' => $summary['next_appointment'] ?? '—',
                'hint' => trans(
                    $summary['next_appointment_hint_key'] ?? 'loyalty::members.show.analytics_no_upcoming',
                    $summary['next_appointment_hint_params'] ?? []
                ),
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'violet',
                'icon' => 'fa-refresh',
                'label' => trans('loyalty::members.show.analytics_visit_cadence'),
                'value' => $summary['visit_cadence_display'] ?? '—',
                'hint' => trans(
                    $summary['visit_cadence_hint_key'] ?? 'loyalty::members.show.analytics_cadence_need_visits',
                    $summary['visit_cadence_hint_params'] ?? []
                ),
            ])

            @include('admin::partials.fc_saas_stat', [
                'variant' => 'gold',
                'icon' => 'fa-heartbeat',
                'label' => trans('loyalty::members.show.analytics_treatment_sessions'),
                'value' => number_format($summary['treatment_sessions'] ?? 0),
                'hint' => trans('loyalty::members.show.analytics_treatment_spend_hint', [
                    'amount' => $summary['treatment_spend'] ?? '—',
                    'retail' => (int) ($summary['retail_orders'] ?? 0),
                ]),
            ])
        </div>

        @if ($hasData)
            <div class="loyalty-member-analytics__charts">
                <div class="loyalty-member-analytics__chart">
                    <h4>{{ trans('loyalty::members.show.analytics_chart_activity') }}</h4>
                    <div class="loyalty-member-analytics__canvas">
                        <canvas id="loyalty-member-activity-chart" height="220"></canvas>
                    </div>
                </div>
                <div class="loyalty-member-analytics__chart">
                    <h4>{{ trans('loyalty::members.show.analytics_chart_spend') }}</h4>
                    <div class="loyalty-member-analytics__canvas">
                        <canvas id="loyalty-member-spend-chart" height="220"></canvas>
                    </div>
                </div>
            </div>
        @else
            <p class="loyalty-member-analytics__empty">
                <i class="fa fa-bar-chart" aria-hidden="true"></i>
                {{ trans('loyalty::members.show.analytics_empty') }}
            </p>
        @endif
    </div>
</div>

@if ($hasData)
    @push('globals')
        <script>
            window.LoyaltyMemberAnalytics = @json($chartsPayload);
        </script>
    @endpush
@endif
