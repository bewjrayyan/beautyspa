@if ($beautician->exists && $scheduleStats)
    <div class="bp-card bp-card-schedule">
        <div class="bp-card-header">
            <h3>{{ trans('beautician::beauticians.form.schedule.title') }}</h3>
            <p>{{ trans('beautician::beauticians.form.schedule.help') }}</p>
        </div>

        <div class="bp-card-body">
            <div class="bp-schedule-stats">
                @include('admin::partials.fc_saas_stat', [
                    'variant' => 'tr-pending',
                    'icon' => 'fa-clock-o',
                    'label' => trans('beautician::beauticians.form.schedule.stats_pending'),
                    'value' => number_format($scheduleStats['pending']),
                    'hint' => trans('beautician::beauticians.form.schedule.stats_pending_hint'),
                ])

                @include('admin::partials.fc_saas_stat', [
                    'variant' => 'tr-in_progress',
                    'icon' => 'fa-spinner',
                    'label' => trans('treatmentreservation::admin.stats.in_progress'),
                    'value' => number_format($scheduleStats['inProgress']),
                    'hint' => trans('beautician::beauticians.form.schedule.stats_in_progress_hint'),
                ])

                @include('admin::partials.fc_saas_stat', [
                    'variant' => 'tr-completed',
                    'icon' => 'fa-check-circle',
                    'label' => trans('beautician::beauticians.form.schedule.stats_completed'),
                    'value' => number_format($scheduleStats['completed']),
                    'hint' => trans('beautician::beauticians.form.schedule.stats_completed_hint'),
                ])
            </div>

            <div
                class="bp-schedule-app tr-reservations"
                id="tr-beautician-schedule-app"
                data-active-view="kanban"
                data-calendar-url="{{ route('admin.beauticians.schedule.calendar', $beautician) }}"
                data-kanban-url="{{ route('admin.beauticians.schedule.kanban', $beautician) }}"
                data-status-url="{{ route('admin.beauticians.schedule.update_status', ['id' => $beautician->id, 'booking' => '__ID__']) }}"
                data-initial-month="{{ now()->format('Y-m') }}"
                data-initial-beautician="{{ $beautician->id }}"
                data-cal-preview-date="{{ trans('treatmentreservation::admin.calendar.preview_date') }}"
                data-cal-preview-time="{{ trans('treatmentreservation::admin.calendar.preview_time') }}"
                data-cal-preview-customer="{{ trans('treatmentreservation::admin.calendar.preview_customer') }}"
                data-cal-preview-treatment="{{ trans('treatmentreservation::admin.calendar.preview_treatment') }}"
                data-cal-preview-category="{{ trans('treatmentreservation::admin.calendar.preview_category') }}"
                data-cal-preview-view-order="{{ trans('treatmentreservation::admin.kanban.view_order') }}"
                data-cal-status-pending="{{ trans('treatmentreservation::admin.kanban.pending') }}"
                data-cal-status-in-progress="{{ trans('treatmentreservation::admin.kanban.in_progress') }}"
                data-cal-status-completed="{{ trans('treatmentreservation::admin.kanban.completed') }}"
            >
                <ul class="nav nav-tabs bp-schedule-tabs" role="tablist">
                    <li class="active">
                        <a href="#" data-schedule-view="kanban">
                            <i class="fa fa-columns"></i>
                            {{ trans('beautician::beauticians.form.schedule.tab_kanban') }}
                        </a>
                    </li>
                    <li>
                        <a href="#" data-schedule-view="calendar">
                            <i class="fa fa-calendar"></i>
                            {{ trans('beautician::beauticians.form.schedule.tab_calendar') }}
                        </a>
                    </li>
                    <li class="bp-schedule-tabs__link">
                        <a
                            href="{{ route('admin.treatment_reservations.index', ['view' => 'kanban', 'beautician_id' => $beautician->id]) }}"
                            target="_blank"
                        >
                            <i class="fa fa-external-link"></i>
                            {{ trans('beautician::beauticians.form.schedule.open_full') }}
                        </a>
                    </li>
                </ul>

                <div class="bp-schedule-panels">
                    <div class="bp-schedule-panel" data-schedule-panel="kanban">
                        @include('treatmentreservation::admin.reservations.partials.kanban', ['embedded' => true])
                    </div>

                    <div class="bp-schedule-panel" data-schedule-panel="calendar" hidden>
                        @include('treatmentreservation::admin.reservations.partials.calendar', [
                            'embedded' => true,
                            'fullViewUrl' => route('admin.treatment_reservations.index', [
                                'view' => 'calendar',
                                'beautician_id' => $beautician->id,
                            ]),
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
