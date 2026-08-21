@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;

    $dashboardData = $dashboardData ?? [];
    $kpis = $dashboardData['kpis'] ?? [];
    $pipeline = $dashboardData['pipeline'] ?? [];
    $ledger = $dashboardData['ledger'] ?? [];
    $ledgerCount = $dashboardData['ledgerCount'] ?? 0;
    $beauticians = $dashboardData['beauticians'] ?? [];
    $alerts = $dashboardData['alerts'] ?? [];
    $recentActivity = $dashboardData['recentActivity'] ?? [];
    $filterDateLabel = $dashboardData['filterDateLabel'] ?? '';
    $filterDateValue = $dashboardData['filterDateValue'] ?? today()->toDateString();
    $dateFilter = $dashboardData['dateFilter'] ?? 'today';
    $pendingCount = (int) ($kpis['pending'] ?? 0);
    $inProgressCount = (int) ($kpis['inProgress'] ?? 0);
    $completedCount = (int) ($kpis['completed'] ?? 0);
    $todayCount = (int) ($kpis['today'] ?? 0);
    $tbaCount = (int) ($dashboardData['tbaCount'] ?? $kpis['tba'] ?? 0);
    $tbaBookings = $dashboardData['tbaBookings'] ?? [];
    $queueCount = count($pipeline['all'] ?? []);

    $kpiBookingsLabel = match ($dateFilter) {
        'tomorrow' => TrLang::trans('admin.crm.kpi_tomorrow'),
        'yesterday' => TrLang::trans('admin.crm.kpi_yesterday'),
        'all' => TrLang::trans('admin.crm.kpi_all'),
        'custom' => $filterDateLabel,
        default => TrLang::trans('admin.crm.kpi_today'),
    };
    $kpiBookingsHint = TrLang::trans('admin.crm.kpi_date_hint', ['count' => $todayCount, 'date' => $filterDateLabel]);
    $kpiBookingsSubhint = match ($dateFilter) {
        'all' => TrLang::trans('admin.crm.kpi_all_subhint'),
        default => TrLang::trans('admin.crm.kpi_date_subhint', ['date' => $filterDateLabel]),
    };
    $kpiCompletedSubhint = match ($dateFilter) {
        'all' => TrLang::trans('admin.crm.kpi_completed_all_subhint'),
        default => TrLang::trans('admin.crm.kpi_completed_subhint'),
    };
    $crmCanEdit = $crmCanEdit ?? auth()->user()?->hasAccess('admin.treatment_reservations.edit');
    $crmSpecialistToggleUrl = $crmSpecialistToggleUrl ?? route('admin.treatment_reservations.crm.specialist_availability', ['beautician' => '__ID__']);
    $crmSpecialistToggleEnabled = $crmSpecialistToggleEnabled ?? $crmCanEdit;
    $pipelineOnly = ! empty($pipelineOnly);
@endphp

<div
    class="tr-crm-dashboard{{ $pipelineOnly ? ' tr-crm-dashboard--pipeline-only' : '' }}"
    id="tr-crm-dashboard"
    data-crm-dashboard="1"
    data-initial-bookings='@json($pipeline['all'] ?? [])'
    data-agenda-status-pending="{{ TrLang::trans('admin.kanban.pending') }}"
    data-agenda-status-in-progress="{{ TrLang::trans('admin.kanban.in_progress') }}"
    data-agenda-status-completed="{{ TrLang::trans('admin.kanban.completed') }}"
    data-agenda-status-canceled="{{ TrLang::trans('admin.crm.status_canceled') }}"
    data-agenda-id-label="{{ TrLang::trans('admin.crm.agenda_booking_id') }}"
    data-agenda-duration-minutes="{{ TrLang::trans('admin.crm.agenda_duration_minutes') }}"
    data-agenda-update-status-aria="{{ TrLang::trans('admin.crm.agenda_update_status_aria') }}"
    data-agenda-status-update-failed="{{ TrLang::trans('admin.crm.agenda_status_update_failed') }}"
    data-agenda-locale="{{ str_replace('_', '-', locale()) }}"
    data-agenda-order-notes-label="{{ TrLang::trans('admin.crm.agenda_order_notes') }}"
    data-agenda-beautician-notes-label="{{ TrLang::trans('admin.crm.agenda_beautician_notes') }}"
    data-agenda-view-order="{{ TrLang::trans('admin.crm.action_view_order') }}"
    data-agenda-reschedule="{{ TrLang::trans('admin.crm.action_reschedule') }}"
    data-tba-schedule-url="{{ route('admin.treatment_reservations.tba.schedule', ['id' => '__ID__']) }}"
    data-tba-slots-url="{{ route('admin.treatment_reservations.manual_bookings.slots') }}"
    data-tba-schedule-label="{{ TrLang::trans('admin.tba.schedule') }}"
    data-tba-badge="{{ TrLang::trans('admin.tba.badge') }}"
    data-agenda-edit-manual="{{ TrLang::trans('admin.manual_booking.edit_title') }}"
    data-agenda-whatsapp="{{ TrLang::trans('admin.crm.action_whatsapp') }}"
    data-agenda-whatsapp-sending="{{ TrLang::trans('admin.calendar.preview_whatsapp_sending') }}"
    data-agenda-whatsapp-sent="{{ TrLang::trans('admin.calendar.preview_whatsapp_sent') }}"
    data-agenda-whatsapp-failed="{{ TrLang::trans('admin.calendar.preview_whatsapp_failed') }}"
    data-agenda-view-profile="{{ TrLang::trans('admin.crm.action_view_profile') }}"
    data-agenda-send-reminder="{{ TrLang::trans('admin.crm.action_send_reminder') }}"
    data-agenda-resend-reminder="{{ TrLang::trans('admin.crm.action_resend_reminder') }}"
    data-agenda-reminder-sent="{{ TrLang::trans('admin.crm.reminder_sent_label') }}"
    data-agenda-holiday-eyebrow="{{ TrLang::trans('admin.crm.agenda_holiday_eyebrow') }}"
    data-agenda-holiday-states="{{ TrLang::trans('admin.crm.agenda_holiday_states') }}"
    data-agenda-holiday-type="{{ TrLang::trans('admin.crm.agenda_holiday_type') }}"
    data-agenda-holiday-subject-to-change="{{ TrLang::trans('admin.crm.agenda_holiday_subject_to_change') }}"
    data-agenda-holiday-kind-national="{{ TrLang::trans('admin.crm.agenda_holiday_kind_national') }}"
    data-agenda-holiday-kind-labour="{{ TrLang::trans('admin.crm.agenda_holiday_kind_labour') }}"
    data-agenda-holiday-kind-religious="{{ TrLang::trans('admin.crm.agenda_holiday_kind_religious') }}"
    data-agenda-holiday-kind-festival="{{ TrLang::trans('admin.crm.agenda_holiday_kind_festival') }}"
    data-agenda-holiday-kind-other="{{ TrLang::trans('admin.crm.agenda_holiday_kind_other') }}"
    data-agenda-holiday-nationwide="{{ TrLang::trans('admin.crm.agenda_holiday_nationwide') }}"
    data-agenda-initial-date="{{ $filterDateValue }}"
    @if ($crmCanEdit)
        data-crm-can-edit="1"
        @if ($crmSpecialistToggleEnabled)
            data-specialist-toggle-enabled="1"
            data-specialist-toggle-url="{{ $crmSpecialistToggleUrl }}"
        @endif
    @else
        data-crm-can-edit="0"
    @endif
    data-specialist-toggle-date="{{ $filterDateValue }}"
    data-search-no-results="{{ TrLang::trans('admin.crm.search_no_results') }}"
    data-search-results-title="{{ TrLang::trans('admin.crm.search_results_title') }}"
    data-search-results-count="{{ TrLang::trans('admin.crm.search_results_count') }}"
    data-specialist-unavailable="{{ TrLang::trans('admin.crm.specialist_unavailable') }}"
    data-specialist-available="{{ TrLang::trans('admin.crm.specialist_available') }}"
    data-specialist-toggle-aria="{{ TrLang::trans('admin.crm.specialist_toggle_aria') }}"
    data-specialist-toggle-failed="{{ TrLang::trans('admin.crm.specialist_toggle_failed') }}"
    data-pipeline-status-failed="{{ TrLang::trans('admin.crm.agenda_status_update_failed') }}"
>
    @unless ($pipelineOnly)
    <section class="tr-crm-dashboard__kpis" aria-label="{{ TrLang::trans('admin.crm.kpi_aria') }}">
        @include('treatmentreservation::admin.reservations.partials.dashboard.kpi-card', [
            'variant' => 'today',
            'icon' => 'fa-calendar',
            'label' => $kpiBookingsLabel,
            'value' => number_format($todayCount),
            'hint' => $kpiBookingsHint,
            'subhint' => $kpiBookingsSubhint,
        ])
        @include('treatmentreservation::admin.reservations.partials.dashboard.kpi-card', [
            'variant' => 'pending',
            'icon' => 'fa-hourglass-half',
            'label' => TrLang::trans('admin.crm.kpi_pending'),
            'value' => number_format($pendingCount),
            'hint' => TrLang::trans('admin.crm.kpi_pending_hint', ['count' => $pendingCount]),
            'subhint' => TrLang::trans('admin.crm.kpi_pending_subhint'),
        ])
        @include('treatmentreservation::admin.reservations.partials.dashboard.kpi-card', [
            'variant' => 'in_progress',
            'icon' => 'fa-heartbeat',
            'label' => TrLang::trans('admin.crm.kpi_in_progress'),
            'value' => number_format($inProgressCount),
            'hint' => TrLang::trans('admin.crm.kpi_in_progress_hint'),
            'subhint' => TrLang::trans('admin.crm.kpi_in_progress_subhint'),
        ])
        @include('treatmentreservation::admin.reservations.partials.dashboard.kpi-card', [
            'variant' => 'completed',
            'icon' => 'fa-check-circle',
            'label' => TrLang::trans('admin.crm.kpi_completed'),
            'value' => number_format($completedCount),
            'hint' => TrLang::trans('admin.crm.kpi_completed_hint'),
            'subhint' => $kpiCompletedSubhint,
        ])
    </section>

    <div class="tr-crm-dashboard__calendar">
        @include('treatmentreservation::admin.reservations.partials.dashboard.calendar-agenda', [
            'calendarFullViewUrl' => $calendarFullViewUrl ?? null,
        ])
    </div>

    <div class="tr-crm-dashboard__pipeline-wrap">
        
    @if (! $pipelineOnly && ($tbaCount > 0 || ! empty($tbaBookings)))
        <section class="tr-crm-panel tr-crm-tba-panel" aria-label="{{ TrLang::trans('admin.tba.title') }}">
            <header class="tr-crm-panel__head">
                <h3 class="tr-crm-panel__title">{{ TrLang::trans('admin.tba.title') }} <span class="badge">{{ $tbaCount }}</span></h3>
            </header>
            <div class="tr-crm-tba-list">
                @forelse ($tbaBookings as $tba)
                    <article class="tr-crm-tba-item" draggable="{{ !empty($tba['can_schedule_tba']) && $crmCanEdit ? 'true' : 'false' }}" data-tba-booking-id="{{ $tba['id'] ?? '' }}" data-tba-beautician-id="{{ $tba['beautician_id'] ?? '' }}" data-product-id="{{ $tba['product_id'] ?? '' }}" data-spa-branch-id="{{ $tba['spa_branch_id'] ?? '' }}" data-search="{{ strtolower(($tba['customer_name'] ?? trim(($tba['customer_first_name'] ?? '').' '.($tba['customer_last_name'] ?? ''))) . ' ' . ($tba['customer_phone'] ?? '') . ' ' . ($tba['product_name'] ?? '') . ' ' . ($tba['beautician_name'] ?? '')) }}" title="{{ TrLang::trans('admin.tba.drag_to_calendar') }}">
                        <div>
                            <strong>{{ $tba['customer_name'] ?? trim(($tba['customer_first_name'] ?? '').' '.($tba['customer_last_name'] ?? '')) }}</strong>
                            <span>{{ $tba['product_name'] ?? '—' }}</span>
                            <span>{{ $tba['beautician_name'] ?? '—' }} · {{ TrLang::trans('admin.tba.badge') }}</span>
                        </div>
                        @if ($crmCanEdit && ! empty($tba['can_schedule_tba']))
                            <button type="button" class="btn btn-primary btn-sm" data-tba-schedule data-booking-id="{{ $tba['id'] }}" data-beautician-id="{{ $tba['beautician_id'] }}" data-product-id="{{ $tba['product_id'] ?? '' }}" data-spa-branch-id="{{ $tba['spa_branch_id'] ?? '' }}">
                                {{ TrLang::trans('admin.tba.schedule') }}
                            </button>
                        @endif
                    </article>
                @empty
                    <p class="text-muted">{{ TrLang::trans('admin.tba.empty') }}</p>
                @endforelse
            </div>
        </section>
    @endif

@include('treatmentreservation::admin.reservations.partials.dashboard.pipeline-board', [
            'pipeline' => $pipeline,
            'filterDateLabel' => $filterDateLabel,
            'dateFilter' => $dateFilter,
            'queueCount' => $queueCount,
        ])
    </div>

    <div class="tr-crm-dashboard__workspace">
        <div class="tr-crm-dashboard__main">
            @include('treatmentreservation::admin.reservations.partials.dashboard.booking-stats-panel', [
                'analytics' => $analytics,
                'analyticsCharts' => $analyticsCharts,
                'kpis' => $kpis,
            ])
            @include('treatmentreservation::admin.reservations.partials.dashboard.ledger-table', [
                'ledger' => $ledger,
                'ledgerCount' => $ledgerCount,
                'filterDateLabel' => $filterDateLabel,
            ])
        </div>

        <aside class="tr-crm-dashboard__aside" aria-label="{{ TrLang::trans('admin.crm.aside_aria') }}">
            @include('treatmentreservation::admin.reservations.partials.dashboard.specialists-panel', [
                'beauticians' => $beauticians,
                'filterDateValue' => $filterDateValue,
                'filterDateLabel' => $filterDateLabel,
                'dateFilter' => $dateFilter,
                'crmShowSpecialistToggle' => $crmSpecialistToggleEnabled ?? false,
                'crmSpecialistProfileUrl' => $crmSpecialistProfileUrl ?? null,
            ])
            @include('treatmentreservation::admin.reservations.partials.dashboard.alerts-feed', [
                'alerts' => $alerts,
            ])
        </aside>
    </div>

    @else
        @include('treatmentreservation::admin.reservations.partials.dashboard.pipeline-board', [
            'pipeline' => $pipeline,
            'filterDateLabel' => $filterDateLabel,
            'dateFilter' => $dateFilter,
            'queueCount' => $queueCount,
        ])
    @endunless

    @include('treatmentreservation::admin.reservations.partials.dashboard.customer-profile-drawer', [
        'crmCustomerProfileUrl' => $crmCustomerProfileUrl ?? null,
        'crmReminderUrlTemplate' => $crmReminderUrlTemplate ?? null,
    ])
</div>
