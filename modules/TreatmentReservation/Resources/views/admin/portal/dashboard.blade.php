@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
    use Modules\User\Services\OneSenderWhatsAppService;

    $crmRoutes = $crmRoutes ?? [];
    $portalFilterContext = $portalFilterContext ?? ['locked' => false];
    $crmDateFilter = $filters['date_filter'] ?? 'today';
    $crmPickerDate = ($crmDateFilter === 'custom' && ! empty($filters['filter_date']))
        ? $filters['filter_date']
        : match ($crmDateFilter) {
            'tomorrow' => now()->addDay()->toDateString(),
            'yesterday' => now()->subDay()->toDateString(),
            'today' => now()->toDateString(),
            default => '',
        };
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
        'completedAt' => trans('treatmentreservation::admin.calendar.work_log_completed_at'),
        'customerNote' => trans('treatmentreservation::admin.calendar.work_log_customer_note'),
        'customerNoteHelp' => trans('treatmentreservation::admin.calendar.work_log_customer_note_help'),
        'generateSummary' => trans('treatmentreservation::admin.calendar.work_log_generate_summary'),
        'noCompletedItems' => trans('treatmentreservation::admin.calendar.work_log_no_completed_items'),
            'emptyChecklistItem' => trans('treatmentreservation::admin.calendar.work_log_empty_checklist_item'),
        'summaryPrefix' => trans('treatmentreservation::admin.calendar.work_log_summary_prefix'),
        'presets' => trans('treatmentreservation::admin.calendar.work_log_presets'),
    ];
@endphp

@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', TrLang::trans('admin.portal.dashboard_title'))

    @if (! empty($adminPortalPreview))
        <li>
            <a href="{{ route('admin.beauticians.index') }}">{{ trans('beautician::beauticians.beauticians') }}</a>
        </li>
        <li>
            <a href="{{ route('admin.beauticians.edit', $beautician) }}">{{ $beautician->name }}</a>
        </li>
        <li class="active">{{ TrLang::trans('admin.portal.dashboard_title') }}</li>
    @else
        <li class="active">{{ TrLang::trans('admin.portal.dashboard_title') }}</li>
    @endif
@endcomponent

@section('content')
    @if (! empty($adminPortalPreview))
        @include('treatmentreservation::admin.portal.partials.admin-preview-banner', [
            'beautician' => $beautician,
        ])
    @endif

    @include('treatmentreservation::admin.partials.urgency-alerts', [
        'urgencyAlertsAsModal' => true,
    ])

    <div
        class="tr-portal tr-reservations tr-reservations-page tr-reservations--view-dashboard tr-reservations--crm-dashboard tr-reservations--mockup tr-portal-crm-dashboard tr-portal-saas"
        id="tr-reservations-app"
        style="--tr-portal-accent: {{ $beautician->profile_color ?? '#6366f1' }};"
        data-active-view="dashboard"
        data-calendar-legend-label="{{ TrLang::trans('admin.calendar.legend_label') }}"
        data-cal-preview-title="{{ TrLang::trans('admin.calendar.preview_title') }}"
        data-cal-preview-details-load-failed="{{ TrLang::trans('admin.calendar.preview_details_load_failed') }}"
        data-cal-preview-date="{{ TrLang::trans('admin.calendar.preview_date') }}"
        data-cal-preview-time="{{ TrLang::trans('admin.calendar.preview_time') }}"
        data-cal-preview-customer="{{ TrLang::trans('admin.calendar.preview_customer') }}"
        data-cal-preview-treatment="{{ TrLang::trans('admin.calendar.preview_treatment') }}"
        data-cal-preview-category="{{ TrLang::trans('admin.calendar.preview_category') }}"
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
        data-calendar-url="{{ $crmRoutes['calendar'] ?? '' }}"
        data-calendar-details-url="{{ $crmRoutes['bookingDetails'] ?? '' }}"
        data-holidays-range-url="{{ route('admin.treatment_reservations.holidays_range') }}"
        data-status-url="{{ $crmRoutes['updateStatus'] ?? '' }}"
        data-schedule-before-start="{{ TrLang::trans('admin.crm.error_schedule_before_start') }}"
        data-notes-url="{{ $crmRoutes['updateNotes'] ?? '' }}"
        data-whatsapp-url="{{ $crmRoutes['whatsapp'] ?? '' }}"
        data-consultation-url="{{ $crmRoutes['consultation'] ?? '' }}"
        data-reschedule-url="{{ $crmRoutes['reschedule'] ?? '' }}"
        data-tba-slots-url="{{ $crmRoutes['manualBookingSlots'] ?? '' }}"
        data-reschedule-slots-url="{{ $crmRoutes['rescheduleSlots'] ?? '' }}"
        data-reschedule-dates-url="{{ $crmRoutes['rescheduleDates'] ?? '' }}"
        data-reschedule-date-prompt="{{ TrLang::trans('admin.reschedule.date_prompt') }}"
        data-reschedule-times-prompt="{{ TrLang::trans('admin.reschedule.available_times', ['times' => '__TIMES__']) }}"
        data-reschedule-load-failed="{{ TrLang::trans('admin.reschedule.load_failed') }}"
        data-reschedule-save-failed="{{ TrLang::trans('admin.reschedule.save_failed') }}"
        data-reschedule-no-slots="{{ TrLang::trans('admin.reschedule.no_slots') }}"
        data-reschedule-labels='@json(trans('treatmentreservation::admin.reschedule'))'
        data-cal-preview-consultation="{{ trans('account::consultation.request.action') }}"
        data-cal-preview-consultation-preparing="{{ trans('account::consultation.request.preparing') }}"
        data-cal-preview-consultation-ready="{{ trans('account::consultation.request.ready') }}"
        data-cal-preview-consultation-failed="{{ trans('account::consultation.request.failed') }}"
        data-reminder-url="{{ $crmRoutes['reminder'] ?? '' }}"
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
        data-cal-preview-view-profile="{{ TrLang::trans('admin.crm.action_view_profile') }}"
        data-cal-preview-send-reminder="{{ TrLang::trans('admin.crm.action_send_reminder') }}"
        data-cal-preview-resend-reminder="{{ TrLang::trans('admin.crm.action_resend_reminder') }}"
        data-cal-preview-reminder-sent="{{ TrLang::trans('admin.crm.reminder_sent_label') }}"
        data-cal-preview-reminder-due="{{ TrLang::trans('admin.crm.reminder_due_label') }}"
        data-cal-preview-reminder-sending="{{ TrLang::trans('admin.crm.reminder_sending') }}"
        data-cal-preview-reminder-failed="{{ TrLang::trans('admin.crm.reminder_failed') }}"
        @if (! empty($crmCanEdit))
            data-crm-can-edit="1"
        @endif
        @if (! empty($crmCanCreate))
            data-manual-booking-edit="1"
            data-manual-booking-update-url="{{ $crmRoutes['manualBookingUpdate'] ?? '' }}"
            data-manual-booking-cancel-url="{{ $crmRoutes['manualBookingCancel'] ?? '' }}"
        @endif
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
        data-cal-preview-tba-badge="{{ TrLang::trans('admin.tba.badge') }}"
        data-cal-preview-action-profile-short="{{ TrLang::trans('admin.calendar.preview_action_profile_short') }}"
        data-cal-preview-action-customer-short="{{ TrLang::trans('admin.calendar.preview_action_customer_short') }}"
        data-cal-preview-whatsapp-reminder-customer="{{ TrLang::trans('admin.crm.whatsapp_reminder_customer') }}"
        data-cal-preview-whatsapp-reminder-beautician="{{ TrLang::trans('admin.crm.whatsapp_reminder_beautician') }}"
        data-cal-preview-action-consultation-short="{{ TrLang::trans('admin.calendar.preview_action_consultation_short') }}"
        data-cal-preview-action-beautician-short="{{ TrLang::trans('admin.calendar.preview_action_beautician_short') }}"
        data-cal-preview-action-reschedule-short="{{ TrLang::trans('admin.calendar.preview_action_reschedule_short') }}"
        data-cal-preview-status="{{ TrLang::trans('admin.calendar.preview_status') }}"
        data-cal-preview-status-title="{{ TrLang::trans('admin.calendar.preview_status_title') }}"
        data-cal-preview-status-update-failed="{{ TrLang::trans('admin.crm.agenda_status_update_failed') }}"
        data-initial-month="{{ $filters['month'] ?? now()->format('Y-m') }}"
        data-initial-spa-branch="{{ $filters['spa_branch_id'] ?? '' }}"
        data-initial-beautician=""
        data-portal-beautician-id="{{ $beautician->id }}"
        data-initial-category="{{ $filters['treatment_category_id'] ?? '' }}"
    >
        @include('treatmentreservation::admin.portal.partials.job-sheet-hero', [
            'beautician' => $beautician,
            'stats' => $heroStats ?? $stats,
            'todayAppointments' => $todayAppointments,
            'adminPortalPreview' => $adminPortalPreview ?? false,
            'backUrl' => $backUrl ?? null,
            'activePortalNav' => 'dashboard',
            'portalCanCreate' => $portalCanCreate ?? ($crmCanCreate ?? false),
            'manualBookingModalId' => 'tr-portal-manual-booking-modal',
        ])

        @if (! empty($crmCanCreate))
            @include('treatmentreservation::admin.reservations.partials.manual-booking-modal', [
                'beauticianPickerOptions' => $beauticianPickerOptions,
                'manualBookingProductCatalog' => $manualBookingProductCatalog,
                'slotsUrl' => $crmRoutes['manualBookingSlots'] ?? '',
                'storeUrl' => $crmRoutes['manualBookingStore'] ?? '',
                'customersUrl' => $crmRoutes['manualBookingCustomers'] ?? '',
                'updateUrlTemplate' => $crmRoutes['manualBookingUpdate'] ?? '',
                'cancelUrlTemplate' => $crmRoutes['manualBookingCancel'] ?? '',
                'portalMode' => true,
                'lockedBeautician' => $beautician,
                'defaultBeauticianId' => $beautician->id,
                'defaultSpaBranchId' => $filters['spa_branch_id'] ?? null,
                'modalId' => 'tr-portal-manual-booking-modal',
            ])
        @endif

        <div class="tab-content tr-tab-panels">
            @include('treatmentreservation::admin.reservations.partials.dashboard', [
                'stats' => $stats,
                'analytics' => $analytics,
                'analyticsCharts' => $analyticsCharts,
                'dashboardData' => $dashboardData,
                'urgency' => $urgency,
                'crmCanEdit' => $crmCanEdit ?? true,
                'crmSpecialistToggleEnabled' => true,
                'crmSpecialistToggleUrl' => $crmRoutes['specialistAvailability'] ?? '',
                'crmCustomerProfileUrl' => $crmRoutes['customerProfile'] ?? '',
                'crmReminderUrlTemplate' => $crmRoutes['reminder'] ?? '',
                'crmSpecialistProfileUrl' => $crmSpecialistProfileUrl ?? null,
                'calendarFullViewUrl' => $crmRoutes['calendarFullView'] ?? null,
                'crmSelfScoped' => $crmSelfScoped ?? true,
                'crmToolbarView' => 'treatmentreservation::admin.reservations.partials.dashboard.crm-toolbar-portal',
            ])
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/TreatmentReservation/Resources/assets/admin/sass/main.scss',
        'modules/TreatmentReservation/Resources/assets/admin/js/main.js',
    ])
@endpush
