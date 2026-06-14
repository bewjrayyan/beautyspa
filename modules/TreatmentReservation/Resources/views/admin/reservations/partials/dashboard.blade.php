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
    $queueCount = count($pipeline['all'] ?? []);

    $kpiBookingsLabel = match ($dateFilter) {
        'tomorrow' => TrLang::trans('admin.crm.kpi_tomorrow'),
        'yesterday' => TrLang::trans('admin.crm.kpi_yesterday'),
        'all' => TrLang::trans('admin.crm.kpi_all'),
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
@endphp

<div
    class="tr-crm-dashboard"
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
    data-agenda-edit-manual="{{ TrLang::trans('admin.manual_booking.edit_title') }}"
    data-agenda-whatsapp="{{ TrLang::trans('admin.crm.action_whatsapp') }}"
    data-agenda-whatsapp-sending="{{ TrLang::trans('admin.calendar.preview_whatsapp_sending') }}"
    data-agenda-whatsapp-sent="{{ TrLang::trans('admin.calendar.preview_whatsapp_sent') }}"
    data-agenda-whatsapp-failed="{{ TrLang::trans('admin.calendar.preview_whatsapp_failed') }}"
    data-agenda-view-profile="{{ TrLang::trans('admin.crm.action_view_profile') }}"
    data-agenda-send-reminder="{{ TrLang::trans('admin.crm.action_send_reminder') }}"
    data-agenda-resend-reminder="{{ TrLang::trans('admin.crm.action_resend_reminder') }}"
    data-agenda-reminder-sent="{{ TrLang::trans('admin.crm.reminder_sent_label') }}"
    data-agenda-initial-date="{{ $filterDateValue }}"
    @hasAccess('admin.treatment_reservations.edit')
        data-crm-can-edit="1"
        data-specialist-toggle-enabled="1"
        data-specialist-toggle-url="{{ route('admin.treatment_reservations.crm.specialist_availability', ['beautician' => '__ID__']) }}"
    @else
        data-crm-can-edit="0"
    @endHasAccess
    data-specialist-toggle-date="{{ $filterDateValue }}"
    data-search-no-results="{{ TrLang::trans('admin.crm.search_no_results') }}"
    data-specialist-unavailable="{{ TrLang::trans('admin.crm.specialist_unavailable') }}"
    data-specialist-available="{{ TrLang::trans('admin.crm.specialist_available') }}"
    data-specialist-toggle-aria="{{ TrLang::trans('admin.crm.specialist_toggle_aria') }}"
    data-specialist-toggle-failed="{{ TrLang::trans('admin.crm.specialist_toggle_failed') }}"
    data-pipeline-status-failed="{{ TrLang::trans('admin.crm.agenda_status_update_failed') }}"
>
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

    <div class="tr-crm-dashboard__workspace">
        <div class="tr-crm-dashboard__main">
            @include('treatmentreservation::admin.reservations.partials.dashboard.pipeline-board', [
                'pipeline' => $pipeline,
                'filterDateLabel' => $filterDateLabel,
                'dateFilter' => $dateFilter,
                'queueCount' => $queueCount,
            ])
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
            ])
            @include('treatmentreservation::admin.reservations.partials.dashboard.alerts-feed', [
                'alerts' => $alerts,
            ])
            @include('treatmentreservation::admin.reservations.partials.dashboard.audit-feed', [
                'recentActivity' => $recentActivity,
            ])
        </aside>
    </div>

    <div class="tr-crm-dashboard__calendar">
        @include('treatmentreservation::admin.reservations.partials.dashboard.calendar-agenda')
    </div>

    @include('treatmentreservation::admin.reservations.partials.dashboard.customer-profile-drawer')
</div>
