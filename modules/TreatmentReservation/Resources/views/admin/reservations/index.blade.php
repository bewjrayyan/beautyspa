@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
    use Modules\User\Services\OneSenderWhatsAppService;
@endphp

@extends('admin::layout')

@php
    $isCrmPipelineView = in_array($activeView, ['dashboard', 'kanban'], true);
    $viewCrumbLabel = match ($activeView) {
        'calendar' => TrLang::trans('admin.tabs.calendar'),
        'reports' => TrLang::trans('admin.tabs.reports'),
        'kanban' => TrLang::trans('admin.tabs.kanban'),
        default => TrLang::trans('admin.tabs.dashboard'),
    };
@endphp

@section('title')
    {{ $activeView === 'dashboard'
        ? trans('treatmentreservation::sidebar.agenda')
        : $viewCrumbLabel . ' - ' . trans('treatmentreservation::sidebar.agenda') }}
@endsection

@section('content')
    @php
        $workLogLabels = [
            'title' => trans('treatmentreservation::admin.calendar.work_log_title'),
            'help' => trans('treatmentreservation::admin.calendar.work_log_help'),
            'date' => trans('treatmentreservation::admin.calendar.work_log_date'),
            'time' => trans('treatmentreservation::admin.calendar.work_log_time'),
            'checklist' => trans('treatmentreservation::admin.calendar.work_log_checklist'),
            'quickAdd' => trans('treatmentreservation::admin.calendar.work_log_quick_add'),
            'customItem' => trans('treatmentreservation::admin.calendar.work_log_custom_item'),
            'itemPlaceholder' => trans('treatmentreservation::admin.calendar.work_log_item_placeholder'),
            'removeItem' => trans('treatmentreservation::admin.calendar.work_log_remove_item'),
            'customerNote' => trans('treatmentreservation::admin.calendar.work_log_customer_note'),
            'customerNoteHelp' => trans('treatmentreservation::admin.calendar.work_log_customer_note_help'),
            'generateSummary' => trans('treatmentreservation::admin.calendar.work_log_generate_summary'),
            'noCompletedItems' => trans('treatmentreservation::admin.calendar.work_log_no_completed_items'),
            'emptyChecklistItem' => trans('treatmentreservation::admin.calendar.work_log_empty_checklist_item'),
            'summaryPrefix' => trans('treatmentreservation::admin.calendar.work_log_summary_prefix'),
            'presets' => trans('treatmentreservation::admin.calendar.work_log_presets'),
        ];
    @endphp

    @if (! $isCrmPipelineView)
        @include('treatmentreservation::admin.partials.urgency-alerts')
    @endif

    <div class="tr-reservations tr-reservations-page tr-reservations--view-{{ $activeView }}{{ $isCrmPipelineView ? ' tr-reservations--crm-dashboard tr-reservations--mockup' : '' }}" id="tr-reservations-app"
        data-active-view="{{ $activeView }}"
        data-calendar-legend-label="{{ TrLang::trans('admin.calendar.legend_label') }}"
        data-cal-preview-title="{{ TrLang::trans('admin.calendar.preview_title') }}"
        data-cal-preview-details-load-failed="{{ TrLang::trans('admin.calendar.preview_details_load_failed') }}"
        data-cal-preview-date="{{ TrLang::trans('admin.calendar.preview_date') }}"
        data-cal-preview-time="{{ TrLang::trans('admin.calendar.preview_time') }}"
        data-cal-preview-customer="{{ TrLang::trans('admin.calendar.preview_customer') }}"
        data-cal-preview-treatment="{{ TrLang::trans('admin.calendar.preview_treatment') }}"
        data-cal-preview-category="{{ TrLang::trans('admin.calendar.preview_category') }}"
        data-cal-preview-view-order="{{ TrLang::trans('admin.kanban.view_order') }}"
        data-cal-preview-phone="{{ TrLang::trans('admin.calendar.preview_phone') }}"
        data-cal-preview-email="{{ TrLang::trans('admin.calendar.preview_email') }}"
        data-cal-preview-order-notes="{{ TrLang::trans('admin.calendar.preview_order_notes') }}"
        data-cal-preview-beautician-notes="{{ TrLang::trans('admin.calendar.preview_beautician_notes') }}"
        data-cal-preview-activity-title="{{ TrLang::trans('admin.activity.title') }}"
        data-cal-preview-activity-show="{{ TrLang::trans('admin.activity.show') }}"
        data-cal-preview-activity-hide="{{ TrLang::trans('admin.activity.hide') }}"
        data-cal-status-pending="{{ TrLang::trans('admin.kanban.pending') }}"
        data-cal-status-in-progress="{{ TrLang::trans('admin.kanban.in_progress') }}"
        data-cal-status-completed="{{ TrLang::trans('admin.kanban.completed') }}"
        data-cal-empty-label="{{ TrLang::trans('admin.calendar.no_bookings') }}"
        data-cal-day-modal-title="{{ TrLang::trans('admin.calendar.day_modal_title') }}"
        data-cal-day-modal-empty="{{ TrLang::trans('admin.calendar.day_modal_empty') }}"
        data-cal-day-modal-holiday="{{ TrLang::trans('admin.calendar.day_modal_holiday') }}"
        data-cal-day-modal-count="{{ TrLang::trans('admin.calendar.day_modal_count') }}"
        data-cal-day-modal-view="{{ TrLang::trans('admin.calendar.day_modal_view') }}"
        data-cal-day-modal-close="{{ TrLang::trans('admin.calendar.day_modal_close') }}"
        data-calendar-url="{{ route('admin.treatment_reservations.calendar') }}"
        data-calendar-details-url="{{ route('admin.treatment_reservations.calendar.event', ['booking' => '__ID__']) }}"
        data-holidays-range-url="{{ route('admin.treatment_reservations.holidays_range') }}"
        data-kanban-url="{{ route('admin.treatment_reservations.kanban') }}"
        data-status-url="{{ route('admin.treatment_reservations.update_status', ['id' => '__ID__']) }}"
        data-notes-url="{{ route('admin.treatment_reservations.update_notes', ['id' => '__ID__']) }}"
        data-whatsapp-url="{{ route('admin.treatment_reservations.send_whatsapp', ['id' => '__ID__']) }}"
        data-consultation-url="{{ route('admin.treatment_reservations.consultation', ['id' => '__ID__']) }}"
        data-cal-preview-consultation="{{ trans('account::consultation.request.action') }}"
        data-cal-preview-consultation-preparing="{{ trans('account::consultation.request.preparing') }}"
        data-cal-preview-consultation-ready="{{ trans('account::consultation.request.ready') }}"
        data-cal-preview-consultation-failed="{{ trans('account::consultation.request.failed') }}"
        data-reminder-url="{{ route('admin.treatment_reservations.send_reminder', ['id' => '__ID__']) }}"
        data-beautician-reminder-url="{{ route('admin.treatment_reservations.send_beautician_reminder', ['id' => '__ID__']) }}"
        data-whatsapp-configured="{{ OneSenderWhatsAppService::isConfigured() ? '1' : '0' }}"
        data-cal-preview-whatsapp-sending="{{ TrLang::trans('admin.calendar.preview_whatsapp_sending') }}"
        data-cal-preview-whatsapp-sent="{{ TrLang::trans('admin.calendar.preview_whatsapp_sent') }}"
        data-cal-preview-whatsapp-failed="{{ TrLang::trans('admin.calendar.preview_whatsapp_failed') }}"
        data-cal-preview-whatsapp-not-configured="{{ TrLang::trans('admin.calendar.whatsapp_not_configured') }}"
        data-cal-preview-whatsapp-customer="{{ TrLang::trans('admin.calendar.preview_whatsapp_customer') }}"
        data-cal-preview-save-notes="{{ TrLang::trans('admin.calendar.preview_save_notes') }}"
        data-cal-preview-saving-notes="{{ TrLang::trans('admin.calendar.preview_saving_notes') }}"
        data-cal-preview-notes-saved="{{ TrLang::trans('admin.calendar.preview_notes_saved') }}"
        data-cal-preview-notes-save-failed="{{ TrLang::trans('admin.calendar.preview_notes_save_failed') }}"
        data-cal-work-log-labels='@json($workLogLabels)'
        data-cal-preview-edit-manual="{{ TrLang::trans('admin.manual_booking.edit_title') }}"
        data-cal-preview-cancel-manual="{{ TrLang::trans('admin.manual_booking.cancel') }}"
        data-cal-preview-cancel-manual-confirm="{{ TrLang::trans('admin.manual_booking.cancel_confirm') }}"
        data-cal-preview-cancel-manual-success="{{ TrLang::trans('admin.manual_booking.canceled') }}"
        data-cal-preview-view-profile="{{ TrLang::trans('admin.crm.action_view_profile') }}"
        data-cal-preview-send-reminder="{{ TrLang::trans('admin.crm.action_send_reminder') }}"
        data-cal-preview-resend-reminder="{{ TrLang::trans('admin.crm.action_resend_reminder') }}"
        data-cal-preview-reminder-sent="{{ TrLang::trans('admin.crm.reminder_sent_label') }}"
        data-cal-preview-reminder-due="{{ TrLang::trans('admin.crm.reminder_due_label') }}"
        data-cal-preview-reminder-sending="{{ TrLang::trans('admin.crm.reminder_sending') }}"
        data-cal-preview-reminder-failed="{{ TrLang::trans('admin.crm.reminder_failed') }}"
        data-cal-preview-whatsapp-reminder-customer="{{ TrLang::trans('admin.crm.whatsapp_reminder_customer') }}"
        data-cal-preview-whatsapp-reminder-beautician="{{ TrLang::trans('admin.crm.whatsapp_reminder_beautician') }}"
        data-cal-preview-beautician-reminder-sent="{{ TrLang::trans('admin.crm.beautician_reminder_sent_label') }}"
        data-cal-preview-beautician-reminder-failed="{{ TrLang::trans('admin.crm.beautician_reminder_failed') }}"
        data-cal-preview-resend-beautician-reminder="{{ TrLang::trans('admin.crm.action_resend_reminder') }}"
        @hasAccess('admin.treatment_reservations.edit')
            data-crm-can-edit="1"
        @endHasAccess
        data-cal-preview-duration="{{ TrLang::trans('admin.calendar.preview_duration') }}"
        data-cal-preview-payment="{{ TrLang::trans('admin.calendar.preview_payment') }}"
        data-cal-preview-payment-receipt="{{ TrLang::trans('admin.calendar.preview_payment_receipt') }}"
        data-cal-preview-view-receipt="{{ TrLang::trans('admin.calendar.preview_view_receipt') }}"
        data-cal-preview-total="{{ TrLang::trans('admin.calendar.preview_total') }}"
        data-cal-preview-source="{{ TrLang::trans('admin.calendar.preview_source') }}"
        data-cal-preview-branch="{{ TrLang::trans('admin.calendar.preview_branch') }}"
        data-cal-preview-booking-id="{{ TrLang::trans('admin.calendar.preview_booking_id') }}"
        data-cal-preview-booking-id-title="{{ TrLang::trans('admin.calendar.preview_booking_id_title') }}"
        data-cal-preview-duration-minutes="{{ TrLang::trans('admin.calendar.preview_duration_value') }}"
        data-cal-preview-duration-hour="{{ TrLang::trans('admin.calendar.preview_duration_hour') }}"
        data-cal-preview-duration-hours="{{ TrLang::trans('admin.calendar.preview_duration_hours') }}"
        data-cal-preview-duration-session="{{ TrLang::trans('admin.calendar.preview_duration_session') }}"
        data-cal-preview-duration-badge-minutes="{{ TrLang::trans('admin.calendar.preview_duration_badge_minutes') }}"
        data-cal-preview-duration-badge-hour="{{ TrLang::trans('admin.calendar.preview_duration_badge_hour') }}"
        data-cal-preview-duration-badge-hours="{{ TrLang::trans('admin.calendar.preview_duration_badge_hours') }}"
        data-cal-preview-duration-badge-hours-minutes="{{ TrLang::trans('admin.calendar.preview_duration_badge_hours_minutes') }}"
        data-cal-preview-section-schedule="{{ TrLang::trans('admin.calendar.preview_section_schedule') }}"
        data-cal-preview-section-customer="{{ TrLang::trans('admin.calendar.preview_section_customer') }}"
        data-cal-preview-section-treatment="{{ TrLang::trans('admin.calendar.preview_section_treatment') }}"
        data-cal-preview-section-notes="{{ TrLang::trans('admin.calendar.preview_section_notes') }}"
        data-cal-preview-section-staff="{{ TrLang::trans('admin.calendar.preview_section_staff') }}"
        data-cal-preview-session="{{ TrLang::trans('admin.calendar.preview_session') }}"
        data-cal-preview-status="{{ TrLang::trans('admin.calendar.preview_status') }}"
        data-cal-preview-status-title="{{ TrLang::trans('admin.calendar.preview_status_title') }}"
        data-cal-preview-reschedule="{{ TrLang::trans('admin.crm.action_reschedule') }}"
        data-cal-preview-action-profile-short="{{ TrLang::trans('admin.calendar.preview_action_profile_short') }}"
        data-cal-preview-action-customer-short="{{ TrLang::trans('admin.calendar.preview_action_customer_short') }}"
        data-cal-preview-action-consultation-short="{{ TrLang::trans('admin.calendar.preview_action_consultation_short') }}"
        data-cal-preview-action-beautician-short="{{ TrLang::trans('admin.calendar.preview_action_beautician_short') }}"
        data-cal-preview-action-reschedule-short="{{ TrLang::trans('admin.calendar.preview_action_reschedule_short') }}"
        data-cal-preview-schedule-tba="{{ TrLang::trans('admin.tba.schedule') }}"
        data-tba-schedule-url="{{ route('admin.treatment_reservations.tba.schedule', ['id' => '__ID__']) }}"
        data-tba-slots-url="{{ route('admin.treatment_reservations.manual_bookings.slots') }}"
        data-reschedule-slots-url="{{ route('admin.treatment_reservations.manual_bookings.slots') }}"
        data-reschedule-dates-url="{{ route('admin.treatment_reservations.reschedule_dates', ['id' => '__ID__']) }}"
        data-reschedule-url="{{ route('admin.treatment_reservations.reschedule', ['id' => '__ID__']) }}"
        data-reschedule-date-prompt="{{ TrLang::trans('admin.reschedule.date_prompt') }}"
        data-reschedule-times-prompt="{{ TrLang::trans('admin.reschedule.available_times', ['times' => '__TIMES__']) }}"
        data-reschedule-load-failed="{{ TrLang::trans('admin.reschedule.load_failed') }}"
        data-reschedule-save-failed="{{ TrLang::trans('admin.reschedule.save_failed') }}"
        data-reschedule-no-slots="{{ TrLang::trans('admin.reschedule.no_slots') }}"
        data-reschedule-labels='@json(trans('treatmentreservation::admin.reschedule'))'
        data-cal-preview-status-update-failed="{{ TrLang::trans('admin.crm.agenda_status_update_failed') }}"
        @hasAccess('admin.treatment_reservations.edit')
            data-manual-booking-edit="1"
            data-manual-booking-update-url="{{ route('admin.treatment_reservations.manual_bookings.update', ['booking' => '__ID__']) }}"
            data-manual-booking-cancel-url="{{ route('admin.treatment_reservations.manual_bookings.cancel', ['booking' => '__ID__']) }}"
        @endHasAccess
        data-initial-month="{{ $filters['month'] }}"
        data-initial-beautician="{{ $filters['beautician_id'] }}"
        data-initial-category="{{ $filters['treatment_category_id'] }}"
        data-initial-spa-branch="{{ $filters['spa_branch_id'] }}"
        data-agenda-locale="{{ str_replace('_', '-', locale()) }}"
    >
        @if ($isCrmPipelineView)
            @php
                $crmDateFilter = $filters['date_filter'] ?? 'today';
                $crmPickerDate = ($crmDateFilter === 'custom' && ! empty($filters['filter_date']))
                    ? $filters['filter_date']
                    : match ($crmDateFilter) {
                        'tomorrow' => now()->addDay()->toDateString(),
                        'yesterday' => now()->subDay()->toDateString(),
                        'today' => now()->toDateString(),
                        default => '',
                    };
            @endphp
            <header class="tr-crm-page-header">
                <div class="tr-crm-page-header__intro">
                    <div class="tr-crm-page-header__icon" aria-hidden="true">
                        <i class="fa {{ $activeView === 'kanban' ? 'fa-columns' : 'fa-clipboard' }}"></i>
                    </div>
                    <div>
                        <span class="tr-crm-page-header__eyebrow">{{ TrLang::trans('admin.crm.eyebrow') }}</span>
                        <h1 class="tr-crm-page-header__title">
                            {{ $activeView === 'kanban'
                                ? TrLang::trans('admin.crm.pipeline_title')
                                : trans('treatmentreservation::sidebar.agenda') }}
                        </h1>
                        <p class="tr-crm-page-header__lead">
                            {{ $activeView === 'kanban'
                                ? TrLang::trans('admin.crm.pipeline_lead_long')
                                : TrLang::trans('admin.crm.subtitle') }}
                        </p>
                    </div>
                </div>
                <div class="tr-crm-page-header__status">
                    <span aria-hidden="true"></span>
                    {{ TrLang::trans('admin.crm.live_status') }}
                </div>
            </header>
        @else
        <header class="tr-reservations-hero">
            <div class="tr-reservations-hero__main">
                <div class="tr-reservations-hero__icon" aria-hidden="true">
                    <i class="fa fa-calendar-check-o"></i>
                </div>
                <div class="tr-reservations-hero__text">
                    <h1 class="tr-reservations-hero__title">{{ TrLang::trans('admin.reservations') }}</h1>
                    <p class="tr-reservations-hero__lead">{{ TrLang::trans('admin.subtitle') }}</p>
                </div>
            </div>

            <div class="tr-reservations-hero__actions">
                @if ($activeView !== 'calendar')
                    @hasAccess('admin.treatment_reservations.create')
                        <button
                            type="button"
                            class="btn btn-primary btn-sm tr-manual-booking-open-btn"
                            data-toggle="modal"
                            data-target="#tr-manual-booking-modal"
                        >
                            <i class="fa fa-plus"></i>
                            {{ TrLang::trans('admin.manual_booking.open') }}
                        </button>
                    @endHasAccess
                @endif

                <div class="tr-reservations-hero__pipeline" role="list" aria-label="{{ TrLang::trans('admin.hero.pipeline_aria') }}">
                    <div
                        class="tr-reservations-hero__metric tr-reservations-hero__metric--pending"
                        role="listitem"
                        aria-label="{{ TrLang::trans('admin.kanban.pending') }}: {{ number_format($stats['pending']) }}"
                    >
                        <div class="tr-reservations-hero__metric-head">
                            <span class="tr-reservations-hero__metric-icon" aria-hidden="true"><i class="fa fa-clock-o"></i></span>
                            <span class="tr-reservations-hero__metric-label">{{ TrLang::trans('admin.kanban.pending') }}</span>
                        </div>
                        <div class="tr-reservations-hero__metric-body">
                            <span class="tr-reservations-hero__metric-value">{{ number_format($stats['pending']) }}</span>
                            <span class="tr-reservations-hero__metric-hint">{{ TrLang::trans('admin.hero.pending_hint') }}</span>
                        </div>
                    </div>
                    <div
                        class="tr-reservations-hero__metric tr-reservations-hero__metric--progress"
                        role="listitem"
                        aria-label="{{ TrLang::trans('admin.kanban.in_progress') }}: {{ number_format($stats['inProgress']) }}"
                    >
                        <div class="tr-reservations-hero__metric-head">
                            <span class="tr-reservations-hero__metric-icon" aria-hidden="true"><i class="fa fa-play-circle"></i></span>
                            <span class="tr-reservations-hero__metric-label">{{ TrLang::trans('admin.kanban.in_progress') }}</span>
                        </div>
                        <div class="tr-reservations-hero__metric-body">
                            <span class="tr-reservations-hero__metric-value">{{ number_format($stats['inProgress']) }}</span>
                            <span class="tr-reservations-hero__metric-hint">{{ TrLang::trans('admin.hero.progress_hint') }}</span>
                        </div>
                    </div>
                    <div
                        class="tr-reservations-hero__metric tr-reservations-hero__metric--completed"
                        role="listitem"
                        aria-label="{{ TrLang::trans('admin.kanban.completed') }}: {{ number_format($stats['completed']) }}"
                    >
                        <div class="tr-reservations-hero__metric-head">
                            <span class="tr-reservations-hero__metric-icon" aria-hidden="true"><i class="fa fa-check-circle"></i></span>
                            <span class="tr-reservations-hero__metric-label">{{ TrLang::trans('admin.kanban.completed') }}</span>
                        </div>
                        <div class="tr-reservations-hero__metric-body">
                            <span class="tr-reservations-hero__metric-value">{{ number_format($stats['completed']) }}</span>
                            <span class="tr-reservations-hero__metric-hint">{{ TrLang::trans('admin.hero.completed_hint') }}</span>
                        </div>
                    </div>
                    @if (isset($todayBookings))
                        <div
                            class="tr-reservations-hero__metric tr-reservations-hero__metric--today"
                            role="listitem"
                            aria-label="{{ TrLang::trans('admin.hero.today') }}: {{ number_format($todayBookings) }}"
                        >
                            <div class="tr-reservations-hero__metric-head">
                                <span class="tr-reservations-hero__metric-icon" aria-hidden="true"><i class="fa fa-calendar"></i></span>
                                <span class="tr-reservations-hero__metric-label">{{ TrLang::trans('admin.hero.today') }}</span>
                            </div>
                            <div class="tr-reservations-hero__metric-body">
                                <span class="tr-reservations-hero__metric-value">{{ number_format($todayBookings) }}</span>
                                <span class="tr-reservations-hero__metric-hint">{{ TrLang::trans('admin.hero.today_hint') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </header>
        @endif

        @hasAnyAccess('admin.treatment_reservations.create', 'admin.treatment_reservations.edit')
            @include('treatmentreservation::admin.reservations.partials.manual-booking-modal', [
                'beauticianPickerOptions' => $beauticianPickerOptions,
                'manualBookingProductCatalog' => $manualBookingProductCatalog,
                'slotsUrl' => route('admin.treatment_reservations.manual_bookings.slots'),
                'storeUrl' => route('admin.treatment_reservations.manual_bookings.store'),
                'customersUrl' => route('admin.treatment_reservations.manual_bookings.customers'),
                'updateUrlTemplate' => route('admin.treatment_reservations.manual_bookings.update', ['booking' => '__ID__']),
                'cancelUrlTemplate' => route('admin.treatment_reservations.manual_bookings.cancel', ['booking' => '__ID__']),
            ])
        @endHasAnyAccess

        @if ($activeView === 'calendar')
            @include('treatmentreservation::admin.reservations.partials.filters-calendar')
        @endif

        <div class="tab-content tr-tab-panels">
            @if ($activeView === 'dashboard')
                @include('treatmentreservation::admin.reservations.partials.dashboard', [
                    'stats' => $stats,
                    'analytics' => $analytics,
                    'analyticsCharts' => $analyticsCharts,
                    'dashboardData' => $dashboardData,
                    'urgency' => $urgency,
                    'crmToolbarView' => 'treatmentreservation::admin.reservations.partials.dashboard.crm-toolbar',
                    'crmFilterBeauticians' => $beauticians,
                ])
            @endif

            @if ($activeView === 'calendar')
                @include('treatmentreservation::admin.reservations.partials.calendar')
                @include('treatmentreservation::admin.reservations.partials.dashboard.customer-profile-drawer')
            @endif

            @if ($activeView === 'kanban')
                @include('treatmentreservation::admin.reservations.partials.dashboard', [
                    'dashboardData' => $dashboardData,
                    'pipelineOnly' => true,
                    'crmToolbarView' => 'treatmentreservation::admin.reservations.partials.dashboard.crm-toolbar',
                    'crmFilterBeauticians' => $beauticians,
                ])
            @endif

            @if ($activeView === 'reports')
                @include('treatmentreservation::admin.reservations.partials.reports')
            @endif
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/TreatmentReservation/Resources/assets/admin/sass/main.scss',
        'modules/TreatmentReservation/Resources/assets/admin/js/main.js',
    ])
@endpush
