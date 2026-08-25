@extends('admin::layout')

@section('title', trans('treatmentreservation::admin.portal.title'))

@section('content_header')
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
            'summaryPrefix' => trans('treatmentreservation::admin.calendar.work_log_summary_prefix'),
            'presets' => trans('treatmentreservation::admin.calendar.work_log_presets'),
        ];
    @endphp

    @if (! empty($adminPortalPreview))
        @include('treatmentreservation::admin.portal.partials.admin-preview-banner', [
            'beautician' => $beautician,
        ])
    @endif

    @include('treatmentreservation::admin.partials.urgency-alerts', [
        'urgencyAlertsAsModal' => true,
    ])

    <div
        class="tr-portal tr-reservations tr-portal-dashboard tr-portal-saas{{ $calendarFocus ? ' tr-portal--calendar-focus' : '' }}"
        id="tr-portal-app"
        style="--tr-portal-accent: {{ $beautician->profile_color ?? '#6366f1' }};"
        data-active-view="{{ $activeView }}"
        data-calendar-url="{{ $portalApiRoutes['calendar'] }}"
        data-calendar-details-url="{{ $portalApiRoutes['calendar_details'] }}"
        data-kanban-url="{{ $portalApiRoutes['kanban'] }}"
        data-status-url="{{ $portalApiRoutes['update_status'] }}"
        data-notes-url="{{ $portalApiRoutes['update_notes'] }}"
        data-whatsapp-url="{{ $portalApiRoutes['send_whatsapp'] }}"
        data-consultation-url="{{ $portalApiRoutes['consultation'] }}"
        data-reschedule-url="{{ $portalApiRoutes['reschedule'] }}"
        data-cal-preview-consultation="{{ trans('account::consultation.request.action') }}"
        data-cal-preview-consultation-preparing="{{ trans('account::consultation.request.preparing') }}"
        data-cal-preview-consultation-ready="{{ trans('account::consultation.request.ready') }}"
        data-cal-preview-consultation-failed="{{ trans('account::consultation.request.failed') }}"
        data-initial-bookings='@json($todayBookingsPayload)'
        data-initial-month="{{ request('month', now()->format('Y-m')) }}"
        data-calendar-focus-booking-id="{{ $calendarFocusBookingId ?? '' }}"
        data-initial-beautician=""
        data-portal-beautician-id="{{ $beautician->id }}"
        data-cal-empty-label="{{ trans('treatmentreservation::admin.calendar.no_bookings') }}"
        data-cal-preview-date="{{ trans('treatmentreservation::admin.calendar.preview_date') }}"
        data-cal-preview-details-load-failed="{{ trans('treatmentreservation::admin.calendar.preview_details_load_failed') }}"
        data-cal-preview-time="{{ trans('treatmentreservation::admin.calendar.preview_time') }}"
        data-cal-preview-duration="{{ trans('treatmentreservation::admin.calendar.preview_duration') }}"
        data-cal-preview-duration-minutes="{{ trans('treatmentreservation::admin.calendar.preview_duration_value') }}"
        data-cal-preview-duration-hour="{{ trans('treatmentreservation::admin.calendar.preview_duration_hour') }}"
        data-cal-preview-duration-hours="{{ trans('treatmentreservation::admin.calendar.preview_duration_hours') }}"
        data-cal-preview-duration-session="{{ trans('treatmentreservation::admin.calendar.preview_duration_session') }}"
        data-cal-preview-duration-badge-minutes="{{ trans('treatmentreservation::admin.calendar.preview_duration_badge_minutes') }}"
        data-cal-preview-duration-badge-hour="{{ trans('treatmentreservation::admin.calendar.preview_duration_badge_hour') }}"
        data-cal-preview-duration-badge-hours="{{ trans('treatmentreservation::admin.calendar.preview_duration_badge_hours') }}"
        data-cal-preview-duration-badge-hours-minutes="{{ trans('treatmentreservation::admin.calendar.preview_duration_badge_hours_minutes') }}"
        data-cal-preview-action-profile-short="{{ trans('treatmentreservation::admin.calendar.preview_action_profile_short') }}"
        data-cal-preview-action-customer-short="{{ trans('treatmentreservation::admin.calendar.preview_action_customer_short') }}"
        data-cal-preview-action-consultation-short="{{ trans('treatmentreservation::admin.calendar.preview_action_consultation_short') }}"
        data-cal-preview-action-beautician-short="{{ trans('treatmentreservation::admin.calendar.preview_action_beautician_short') }}"
        data-cal-preview-action-reschedule-short="{{ trans('treatmentreservation::admin.calendar.preview_action_reschedule_short') }}"
        data-cal-preview-customer="{{ trans('treatmentreservation::admin.calendar.preview_customer') }}"
        data-cal-preview-treatment="{{ trans('treatmentreservation::admin.calendar.preview_treatment') }}"
        data-cal-preview-category="{{ trans('treatmentreservation::admin.calendar.preview_category') }}"
        data-cal-preview-view-order="{{ trans('treatmentreservation::admin.kanban.view_order') }}"
        data-cal-preview-phone="{{ trans('treatmentreservation::admin.calendar.preview_phone') }}"
        data-cal-preview-email="{{ trans('treatmentreservation::admin.calendar.preview_email') }}"
        data-cal-preview-order-notes="{{ trans('treatmentreservation::admin.calendar.preview_order_notes') }}"
        data-cal-preview-beautician-notes="{{ trans('treatmentreservation::admin.calendar.preview_beautician_notes') }}"
        data-cal-preview-save-notes="{{ trans('treatmentreservation::admin.calendar.preview_save_notes') }}"
        data-cal-preview-saving-notes="{{ trans('treatmentreservation::admin.calendar.preview_saving_notes') }}"
        data-cal-preview-notes-saved="{{ trans('treatmentreservation::admin.calendar.preview_notes_saved') }}"
        data-cal-preview-notes-save-failed="{{ trans('treatmentreservation::admin.calendar.preview_notes_save_failed') }}"
        data-cal-work-log-labels='@json($workLogLabels)'
        data-cal-preview-whatsapp-customer="{{ trans('treatmentreservation::admin.calendar.preview_whatsapp_customer') }}"
        data-cal-preview-whatsapp-sending="{{ trans('treatmentreservation::admin.calendar.preview_whatsapp_sending') }}"
        data-cal-preview-whatsapp-sent="{{ trans('treatmentreservation::admin.calendar.preview_whatsapp_sent') }}"
        data-cal-preview-whatsapp-failed="{{ trans('treatmentreservation::admin.calendar.preview_whatsapp_failed') }}"
        data-cal-status-pending="{{ trans('treatmentreservation::admin.kanban.pending') }}"
        data-cal-status-in-progress="{{ trans('treatmentreservation::admin.kanban.in_progress') }}"
        data-cal-status-completed="{{ trans('treatmentreservation::admin.kanban.completed') }}"
        data-cal-preview-edit-manual="{{ trans('treatmentreservation::admin.manual_booking.edit_title') }}"
        data-cal-preview-schedule-tba="{{ trans('treatmentreservation::admin.tba.schedule') }}"
        data-tba-schedule-url="{{ route('admin.treatment_reservations.portal.tba.schedule', ['id' => '__ID__']) }}"
        data-tba-slots-url="{{ $portalApiRoutes['tba_slots'] }}"
        data-reschedule-slots-url="{{ $portalApiRoutes['slots'] }}"
        data-reschedule-dates-url="{{ $portalApiRoutes['dates'] }}"
        data-reschedule-date-prompt="{{ trans('treatmentreservation::admin.reschedule.date_prompt') }}"
        data-reschedule-times-prompt="{{ trans('treatmentreservation::admin.reschedule.available_times', ['times' => '__TIMES__']) }}"
        data-reschedule-load-failed="{{ trans('treatmentreservation::admin.reschedule.load_failed') }}"
        data-reschedule-save-failed="{{ trans('treatmentreservation::admin.reschedule.save_failed') }}"
        data-reschedule-no-slots="{{ trans('treatmentreservation::admin.reschedule.no_slots') }}"
        data-reschedule-labels='@json(trans('treatmentreservation::admin.reschedule'))'
        data-cal-preview-cancel-manual="{{ trans('treatmentreservation::admin.manual_booking.cancel') }}"
        data-cal-preview-cancel-manual-confirm="{{ trans('treatmentreservation::admin.manual_booking.cancel_confirm') }}"
        data-cal-preview-cancel-manual-success="{{ trans('treatmentreservation::admin.manual_booking.canceled') }}"
        @hasAccess('admin.treatment_reservations.portal.create')
            data-manual-booking-edit="1"
            data-manual-booking-update-url="{{ route('admin.treatment_reservations.portal.manual_bookings.update', ['booking' => '__ID__']) }}"
            data-manual-booking-cancel-url="{{ route('admin.treatment_reservations.portal.manual_bookings.cancel', ['booking' => '__ID__']) }}"
        @endHasAccess
    >
        @include('treatmentreservation::admin.portal.partials.job-sheet-hero', [
            'beautician' => $beautician,
            'stats' => $stats,
            'todayAppointments' => $todayAppointments,
            'adminPortalPreview' => $adminPortalPreview ?? false,
            'backUrl' => $backUrl ?? null,
        ])

        <div class="tr-portal-saas__layout">
            <main class="tr-portal-saas__main">
                @include('treatmentreservation::admin.portal.partials.performance-metrics', [
                    'stats' => $stats,
                    'performanceStats' => $performanceStats,
                ])

                <section class="tr-portal-saas-workspace tr-portal-dashboard__schedule" id="tr-portal-schedule">
                    <div class="tr-portal-saas-workspace__head">
                        <div class="tr-portal-saas-workspace__copy">
                            <h2>{{ trans('treatmentreservation::admin.portal.schedule_title') }}</h2>
                            <p>{{ trans('treatmentreservation::admin.portal.schedule_subtitle') }}</p>
                        </div>

                        <div class="tr-portal-saas-workspace__badges" aria-hidden="true">
                            <span class="tr-portal-saas-workspace__badge tr-portal-saas-workspace__badge--pending">
                                {{ number_format($stats['pending']) }} {{ trans('treatmentreservation::admin.kanban.pending') }}
                            </span>
                            <span class="tr-portal-saas-workspace__badge tr-portal-saas-workspace__badge--progress">
                                {{ number_format($stats['inProgress']) }} {{ trans('treatmentreservation::admin.kanban.in_progress') }}
                            </span>
                            <span class="tr-portal-saas-workspace__badge tr-portal-saas-workspace__badge--done">
                                {{ number_format($stats['completed']) }} {{ trans('treatmentreservation::admin.kanban.completed') }}
                            </span>
                        </div>
                    </div>

                    <div class="tr-portal-dashboard__schedule-tabs">
                        <ul class="nav nav-tabs tr-portal-tabs tr-portal-tabs--modern" role="tablist">
                            <li class="{{ $activeView === 'kanban' ? 'active' : '' }}">
                                <a href="#" data-schedule-view="kanban">
                                    <i class="fa fa-columns"></i>
                                    {{ trans('treatmentreservation::admin.portal.tab_kanban') }}
                                </a>
                            </li>
                            <li class="{{ $activeView === 'calendar' ? 'active' : '' }}">
                                <a href="#" data-schedule-view="calendar">
                                    <i class="fa fa-calendar"></i>
                                    {{ trans('treatmentreservation::admin.portal.tab_calendar') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="tr-portal-panels">
                        <div class="tr-portal-panel" data-schedule-panel="kanban" @if ($activeView !== 'kanban') hidden @endif>
                            @include('treatmentreservation::admin.reservations.partials.kanban', ['embedded' => true])
                        </div>

                        <div class="tr-portal-panel" data-schedule-panel="calendar" @if ($activeView !== 'calendar') hidden @endif>
                            @include('treatmentreservation::admin.reservations.partials.calendar', [
                                'embedded' => true,
                                'fullViewUrl' => ! empty($adminPortalPreview)
                                    ? ($calendarFocus
                                        ? route('admin.beauticians.portal', $beautician->id)
                                        : route('admin.beauticians.portal.calendar_page', ['id' => $beautician->id, 'focus' => 1]))
                                    : ($calendarFocus
                                        ? route('admin.treatment_reservations.portal.job_sheet')
                                        : route('admin.treatment_reservations.portal.calendar_page', ['focus' => 1])),
                                'fullViewIcon' => $calendarFocus ? 'fa-compress' : 'fa-expand',
                                'fullViewLabel' => $calendarFocus
                                    ? trans('treatmentreservation::admin.portal.back_to_job_sheet')
                                    : trans('treatmentreservation::admin.calendar.full_view'),
                            ])
                        </div>
                    </div>
                </section>
            </main>

            <aside class="tr-portal-saas__rail">
                <div class="tr-portal-today box">
                    <div class="tr-portal-today__header">
                        <div>
                            <h4>{{ trans('treatmentreservation::admin.portal.today_title') }}</h4>
                            <p>{{ trans('treatmentreservation::admin.portal.today_count', ['count' => $todayAppointments->count()]) }}</p>
                        </div>
                        <span class="tr-portal-today__date">{{ now()->format('d M Y') }}</span>
                    </div>

                    @if ($todayAppointments->isEmpty())
                        <p class="tr-portal-today__empty">{{ trans('treatmentreservation::admin.portal.today_empty') }}</p>
                    @else
                        <ul class="tr-portal-today__list">
                            @foreach ($todayAppointments as $appointment)
                                @php
                                    $isOwnAppointment = (int) $appointment->beautician_id === (int) $beautician->id;
                                @endphp
                                <li
                                    class="tr-portal-today__item tr-portal-today__item--{{ $appointment->status }} tr-portal-today__item--clickable{{ $isOwnAppointment ? '' : ' tr-portal-today__item--others' }}"
                                    data-booking-id="{{ $appointment->id }}"
                                    role="button"
                                    tabindex="0"
                                >
                                    <span class="tr-portal-today__time">{{ $appointment->appointment_time ?: '—' }}</span>
                                    <div class="tr-portal-today__body">
                                        <strong>{{ $appointment->customer_full_name }}</strong>
                                        <span>{{ $appointment->product?->name }}</span>
                                        @if (! $isOwnAppointment && $appointment->beautician)
                                            <em class="tr-portal-today__beautician">{{ $appointment->beautician->name }}</em>
                                        @endif
                                    </div>
                                    <span class="tr-portal-today__status">{{ trans('treatmentreservation::admin.kanban.' . $appointment->status) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="tr-portal-today__hint">{{ trans('treatmentreservation::admin.portal.today_click_hint') }}</p>
                    @endif
                </div>

                @include('treatmentreservation::admin.portal.partials.quick-actions', [
                    'adminPortalPreview' => $adminPortalPreview ?? false,
                ])
            </aside>
        </div>

    </div>

    @if (empty($adminPortalPreview))
        @hasAccess('admin.treatment_reservations.portal.create')
            @include('treatmentreservation::admin.reservations.partials.manual-booking-modal', [
                'portalMode' => true,
                'allowBeauticianSelect' => true,
                'beauticianPickerOptions' => $beauticianPickerOptions,
                'defaultBeauticianId' => $beautician->id,
                'manualBookingProductCatalog' => $manualBookingProductCatalog,
                'modalId' => 'tr-portal-manual-booking-modal',
                'slotsUrl' => route('admin.treatment_reservations.portal.manual_bookings.slots'),
                'storeUrl' => route('admin.treatment_reservations.portal.manual_bookings.store'),
                'customersUrl' => route('admin.treatment_reservations.portal.manual_bookings.customers'),
                'updateUrlTemplate' => route('admin.treatment_reservations.portal.manual_bookings.update', ['booking' => '__ID__']),
                'cancelUrlTemplate' => route('admin.treatment_reservations.portal.manual_bookings.cancel', ['booking' => '__ID__']),
            ])
        @endHasAccess
    @endif

    @include('treatmentreservation::admin.reservations.partials.dashboard.customer-profile-drawer', [
        'crmCustomerProfileUrl' => $portalApiRoutes['customer_profile'] ?? null,
        'crmReminderUrlTemplate' => $portalApiRoutes['reminder'] ?? null,
    ])
@endsection

@push('globals')
    @vite([
        'modules/TreatmentReservation/Resources/assets/admin/sass/main.scss',
        'modules/TreatmentReservation/Resources/assets/admin/js/main.js',
    ])
@endpush
