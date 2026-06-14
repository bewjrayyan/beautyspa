@extends('admin::layout')

@section('title', trans('treatmentreservation::admin.portal.title'))

@section('content_header')
@endsection

@section('content')
    @if (! empty($adminPortalPreview))
        <div class="alert alert-info tr-portal-admin-preview">
            <i class="fa fa-eye"></i>
            @if (admin_portal_preview()?->isActive())
                {{ trans('beautician::beauticians.form.admin_portal_preview_banner', ['name' => $beautician->name]) }}
            @else
                {{ trans('beautician::beauticians.form.admin_portal_preview_no_user') }}
            @endif
        </div>
    @endif

    @include('treatmentreservation::admin.partials.urgency-alerts', [
        'urgencyAlertsAsModal' => true,
    ])

    <div
        class="tr-portal tr-reservations tr-portal-dashboard{{ $calendarFocus ? ' tr-portal--calendar-focus' : '' }}"
        id="tr-portal-app"
        style="--tr-portal-accent: {{ $beautician->profile_color ?? '#6366f1' }};"
        data-active-view="{{ $activeView }}"
        data-calendar-url="{{ $portalApiRoutes['calendar'] }}"
        data-kanban-url="{{ $portalApiRoutes['kanban'] }}"
        data-status-url="{{ $portalApiRoutes['update_status'] }}"
        data-notes-url="{{ $portalApiRoutes['update_notes'] }}"
        data-whatsapp-url="{{ $portalApiRoutes['send_whatsapp'] }}"
        data-initial-bookings='@json($todayBookingsPayload)'
        data-initial-month="{{ now()->format('Y-m') }}"
        data-initial-beautician="{{ $beautician->id }}"
        data-cal-empty-label="{{ trans('treatmentreservation::admin.calendar.no_bookings') }}"
        data-cal-preview-date="{{ trans('treatmentreservation::admin.calendar.preview_date') }}"
        data-cal-preview-time="{{ trans('treatmentreservation::admin.calendar.preview_time') }}"
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
        data-cal-preview-whatsapp-customer="{{ trans('treatmentreservation::admin.calendar.preview_whatsapp_customer') }}"
        data-cal-preview-whatsapp-sending="{{ trans('treatmentreservation::admin.calendar.preview_whatsapp_sending') }}"
        data-cal-preview-whatsapp-sent="{{ trans('treatmentreservation::admin.calendar.preview_whatsapp_sent') }}"
        data-cal-preview-whatsapp-failed="{{ trans('treatmentreservation::admin.calendar.preview_whatsapp_failed') }}"
        data-cal-status-pending="{{ trans('treatmentreservation::admin.kanban.pending') }}"
        data-cal-status-in-progress="{{ trans('treatmentreservation::admin.kanban.in_progress') }}"
        data-cal-status-completed="{{ trans('treatmentreservation::admin.kanban.completed') }}"
        data-cal-preview-edit-manual="{{ trans('treatmentreservation::admin.manual_booking.edit_title') }}"
        data-cal-preview-cancel-manual="{{ trans('treatmentreservation::admin.manual_booking.cancel') }}"
        data-cal-preview-cancel-manual-confirm="{{ trans('treatmentreservation::admin.manual_booking.cancel_confirm') }}"
        data-cal-preview-cancel-manual-success="{{ trans('treatmentreservation::admin.manual_booking.canceled') }}"
        @hasAccess('admin.treatment_reservations.portal.create')
            data-manual-booking-edit="1"
            data-manual-booking-update-url="{{ route('admin.treatment_reservations.portal.manual_bookings.update', ['booking' => '__ID__']) }}"
            data-manual-booking-cancel-url="{{ route('admin.treatment_reservations.portal.manual_bookings.cancel', ['booking' => '__ID__']) }}"
        @endHasAccess
    >
        @include('treatmentreservation::admin.portal.partials.dashboard-hero', [
            'beautician' => $beautician,
            'stats' => $stats,
            'performanceStats' => $performanceStats,
            'todayAppointments' => $todayAppointments,
            'adminPortalPreview' => $adminPortalPreview ?? false,
            'backUrl' => $backUrl ?? null,
        ])

        <div class="tr-portal-dashboard__overview">
            <div class="tr-portal-dashboard__grid">
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
                                <li
                                    class="tr-portal-today__item tr-portal-today__item--clickable tr-portal-today__item--{{ $appointment->status }}"
                                    data-booking-id="{{ $appointment->id }}"
                                    role="button"
                                    tabindex="0"
                                >
                                    <span class="tr-portal-today__time">{{ $appointment->appointment_time ?: '—' }}</span>
                                    <div class="tr-portal-today__body">
                                        <strong>{{ $appointment->customer_full_name }}</strong>
                                        <span>{{ $appointment->product?->name }}</span>
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
            </div>

            <section class="tr-stats-panel">
                <header class="tr-stats-panel__header">
                    <div>
                        <h4>{{ trans('treatmentreservation::admin.portal.performance_title') }}</h4>
                        <p>{{ trans('treatmentreservation::admin.portal.performance_subtitle') }}</p>
                    </div>
                </header>

                <div class="tr-stats-panel__section">
                    <h5 class="tr-stats-panel__section-title">{{ trans('treatmentreservation::admin.stats.performance_period_title') }}</h5>
                    <div class="tr-stat-grid tr-stat-grid--performance">
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

                        @include('treatmentreservation::admin.portal.partials.portal-stat', [
                            'tone' => 'revenue',
                            'icon' => 'fa-line-chart',
                            'label' => trans('treatmentreservation::admin.stats.treatment_revenue'),
                            'value' => $performanceStats['treatmentRevenue']->format(),
                            'hint' => trans('treatmentreservation::admin.stats.treatment_revenue_hint'),
                            'featured' => true,
                        ])
                    </div>
                </div>

                <div class="tr-stats-panel__section">
                    <h5 class="tr-stats-panel__section-title">{{ trans('treatmentreservation::admin.stats.pipeline_title') }}</h5>
                    <div class="tr-stat-grid tr-stat-grid--pipeline">
                        @include('treatmentreservation::admin.portal.partials.portal-stat', [
                            'tone' => 'pending',
                            'icon' => 'fa-clock-o',
                            'label' => trans('treatmentreservation::admin.kanban.pending'),
                            'value' => number_format($stats['pending']),
                            'hint' => trans('treatmentreservation::admin.stats.pending_hint'),
                        ])

                        @include('treatmentreservation::admin.portal.partials.portal-stat', [
                            'tone' => 'progress',
                            'icon' => 'fa-spinner',
                            'label' => trans('treatmentreservation::admin.kanban.in_progress'),
                            'value' => number_format($stats['inProgress']),
                            'hint' => trans('treatmentreservation::admin.stats.in_progress_hint'),
                        ])

                        @include('treatmentreservation::admin.portal.partials.portal-stat', [
                            'tone' => 'completed',
                            'icon' => 'fa-check-circle',
                            'label' => trans('treatmentreservation::admin.kanban.completed'),
                            'value' => number_format($stats['completed']),
                            'hint' => trans('treatmentreservation::admin.stats.completed_hint'),
                        ])
                    </div>
                </div>
            </section>
        </div>

        <section class="tr-portal-dashboard__schedule" id="tr-portal-schedule">
            <div class="tr-portal-dashboard__schedule-head">
                <div>
                    <h2>{{ trans('treatmentreservation::admin.portal.schedule_title') }}</h2>
                    <p>{{ trans('treatmentreservation::admin.portal.schedule_subtitle') }}</p>
                </div>
            </div>

            <div class="tr-portal-dashboard__schedule-tabs">
                <ul class="nav nav-tabs tr-portal-tabs tr-portal-tabs--modern" role="tablist">
                    <li class="active">
                        <a href="#" data-schedule-view="kanban">
                            <i class="fa fa-columns"></i>
                            {{ trans('treatmentreservation::admin.portal.tab_kanban') }}
                        </a>
                    </li>
                    <li>
                        <a href="#" data-schedule-view="calendar">
                            <i class="fa fa-calendar"></i>
                            {{ trans('treatmentreservation::admin.portal.tab_calendar') }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="tr-portal-panels">
                <div class="tr-portal-panel" data-schedule-panel="kanban">
                    @include('treatmentreservation::admin.reservations.partials.kanban', ['embedded' => true])
                </div>

                <div class="tr-portal-panel" data-schedule-panel="calendar" hidden>
                    @include('treatmentreservation::admin.reservations.partials.calendar', [
                        'embedded' => true,
                        'fullViewUrl' => ! empty($adminPortalPreview)
                            ? ($calendarFocus
                                ? route('admin.beauticians.portal', $beautician->id)
                                : route('admin.beauticians.portal', ['id' => $beautician->id, 'view' => 'calendar', 'focus' => 1]))
                            : ($calendarFocus
                                ? route('admin.treatment_reservations.portal')
                                : route('admin.treatment_reservations.portal', ['view' => 'calendar', 'focus' => 1])),
                        'fullViewIcon' => $calendarFocus ? 'fa-compress' : 'fa-expand',
                        'fullViewLabel' => $calendarFocus
                            ? trans('treatmentreservation::admin.portal.back_to_job_sheet')
                            : trans('treatmentreservation::admin.calendar.full_view'),
                    ])
                </div>
            </div>
        </section>

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
@endsection

@push('globals')
    @vite([
        'modules/TreatmentReservation/Resources/assets/admin/sass/main.scss',
        'modules/TreatmentReservation/Resources/assets/admin/js/main.js',
    ])
@endpush
