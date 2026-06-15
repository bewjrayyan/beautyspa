@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
    use Modules\User\Services\OneSenderWhatsAppService;
@endphp

@extends('admin::layout')

@section('title', TrLang::trans('admin.reservations'))

@section('content_header')
@endsection

@section('content')
    @if ($activeView !== 'dashboard')
        @include('treatmentreservation::admin.partials.urgency-alerts')
    @endif

    <div class="tr-reservations tr-reservations-page tr-reservations--view-{{ $activeView }}{{ $activeView === 'dashboard' ? ' tr-reservations--crm-dashboard tr-reservations--mockup' : '' }}" id="tr-reservations-app"
        data-active-view="{{ $activeView }}"
        data-calendar-legend-label="{{ TrLang::trans('admin.calendar.legend_label') }}"
        data-cal-preview-title="{{ TrLang::trans('admin.calendar.preview_title') }}"
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
        data-cal-status-pending="{{ TrLang::trans('admin.kanban.pending') }}"
        data-cal-status-in-progress="{{ TrLang::trans('admin.kanban.in_progress') }}"
        data-cal-status-completed="{{ TrLang::trans('admin.kanban.completed') }}"
        data-cal-empty-label="{{ TrLang::trans('admin.calendar.no_bookings') }}"
        data-calendar-url="{{ route('admin.treatment_reservations.calendar') }}"
        data-kanban-url="{{ route('admin.treatment_reservations.kanban') }}"
        data-status-url="{{ route('admin.treatment_reservations.update_status', ['id' => '__ID__']) }}"
        data-whatsapp-url="{{ route('admin.treatment_reservations.send_whatsapp', ['id' => '__ID__']) }}"
        data-reminder-url="{{ route('admin.treatment_reservations.send_reminder', ['id' => '__ID__']) }}"
        data-beautician-reminder-url="{{ route('admin.treatment_reservations.send_beautician_reminder', ['id' => '__ID__']) }}"
        data-whatsapp-configured="{{ OneSenderWhatsAppService::isConfigured() ? '1' : '0' }}"
        data-cal-preview-whatsapp-sending="{{ TrLang::trans('admin.calendar.preview_whatsapp_sending') }}"
        data-cal-preview-whatsapp-sent="{{ TrLang::trans('admin.calendar.preview_whatsapp_sent') }}"
        data-cal-preview-whatsapp-failed="{{ TrLang::trans('admin.calendar.preview_whatsapp_failed') }}"
        data-cal-preview-whatsapp-not-configured="{{ TrLang::trans('admin.calendar.whatsapp_not_configured') }}"
        data-cal-preview-whatsapp-customer="{{ TrLang::trans('admin.calendar.preview_whatsapp_customer') }}"
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
        data-cal-preview-total="{{ TrLang::trans('admin.calendar.preview_total') }}"
        data-cal-preview-source="{{ TrLang::trans('admin.calendar.preview_source') }}"
        data-cal-preview-branch="{{ TrLang::trans('admin.calendar.preview_branch') }}"
        data-cal-preview-booking-id="{{ TrLang::trans('admin.calendar.preview_booking_id') }}"
        data-cal-preview-duration-minutes="{{ TrLang::trans('admin.calendar.preview_duration_value') }}"
        data-cal-preview-section-schedule="{{ TrLang::trans('admin.calendar.preview_section_schedule') }}"
        data-cal-preview-section-customer="{{ TrLang::trans('admin.calendar.preview_section_customer') }}"
        data-cal-preview-section-treatment="{{ TrLang::trans('admin.calendar.preview_section_treatment') }}"
        data-cal-preview-section-notes="{{ TrLang::trans('admin.calendar.preview_section_notes') }}"
        data-cal-preview-section-staff="{{ TrLang::trans('admin.calendar.preview_section_staff') }}"
        data-cal-preview-session="{{ TrLang::trans('admin.calendar.preview_session') }}"
        data-cal-preview-status="{{ TrLang::trans('admin.calendar.preview_status') }}"
        data-cal-preview-reschedule="{{ TrLang::trans('admin.crm.action_reschedule') }}"
        data-cal-preview-status-update-failed="{{ TrLang::trans('admin.crm.agenda_status_update_failed') }}"
        @hasAccess('admin.treatment_reservations.edit')
            data-manual-booking-edit="1"
            data-manual-booking-update-url="{{ route('admin.treatment_reservations.manual_bookings.update', ['booking' => '__ID__']) }}"
            data-manual-booking-cancel-url="{{ route('admin.treatment_reservations.manual_bookings.cancel', ['booking' => '__ID__']) }}"
        @endHasAccess
        data-initial-month="{{ $filters['month'] }}"
        data-initial-beautician="{{ $filters['beautician_id'] }}"
        data-initial-category="{{ $filters['treatment_category_id'] }}"
    >
        @if ($activeView === 'dashboard')
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
                    <h1 class="tr-crm-page-header__title">{{ TrLang::trans('admin.reservations') }}</h1>
                    <p class="tr-crm-page-header__lead">{{ TrLang::trans('admin.crm.subtitle') }}</p>

                    <div class="tr-crm-page-header__toolbar">
                        <div class="tr-crm-toolbar">
                            <form class="tr-crm-toolbar__filters-form" method="get" action="{{ route('admin.treatment_reservations.index') }}" id="tr-crm-header-form">
                                <input type="hidden" name="view" value="dashboard">
                                <input type="hidden" name="date_filter" id="tr-crm-date-filter" value="{{ $crmDateFilter }}">
                                <input type="hidden" name="filter_date" id="tr-crm-filter-date" value="{{ $filters['filter_date'] ?? '' }}">
                                <input type="hidden" name="beautician_id" id="tr-crm-hidden-beautician" value="{{ $filters['beautician_id'] }}">
                                <input type="hidden" name="treatment_category_id" id="tr-crm-hidden-category" value="{{ $filters['treatment_category_id'] }}">

                                @if ($spaBranches->isNotEmpty())
                                    <div class="tr-crm-toolbar__field tr-crm-toolbar__field--branch">
                                        <span class="tr-crm-toolbar__field-icon" aria-hidden="true">
                                            <i class="fa fa-map-marker"></i>
                                        </span>
                                        <label class="sr-only" for="tr-crm-filter-branch">{{ TrLang::trans('admin.filters.spa_branch') }}</label>
                                        <select class="tr-crm-toolbar__select" id="tr-crm-filter-branch" name="spa_branch_id" onchange="this.form.requestSubmit()">
                                            <option value="">{{ TrLang::trans('admin.filters.all_branches') }}</option>
                                            @foreach ($spaBranches as $branchId => $branchName)
                                                <option value="{{ $branchId }}" @selected($filters['spa_branch_id'] == $branchId)>
                                                    {{ $branchName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span class="tr-crm-toolbar__divider" aria-hidden="true"></span>
                                @endif

                                <div class="tr-crm-toolbar__field tr-crm-toolbar__field--dates">
                                    <span class="tr-crm-toolbar__field-icon" aria-hidden="true">
                                        <i class="fa fa-calendar-o"></i>
                                    </span>
                                    <div class="tr-crm-toolbar__dates" role="group" aria-label="{{ TrLang::trans('admin.crm.date_filter_aria') }}">
                                        @foreach (['all' => 'date_all', 'today' => 'date_today', 'tomorrow' => 'date_tomorrow'] as $value => $labelKey)
                                            <button
                                                type="button"
                                                class="tr-crm-toolbar__date-pill{{ $crmDateFilter === $value ? ' is-active' : '' }}"
                                                data-date-filter="{{ $value }}"
                                            >
                                                {{ TrLang::trans('admin.crm.' . $labelKey) }}
                                            </button>
                                        @endforeach

                                        <label class="tr-crm-toolbar__date-picker{{ $crmDateFilter === 'custom' ? ' is-active' : '' }}">
                                            <i class="fa fa-calendar" aria-hidden="true"></i>
                                            <input
                                                type="text"
                                                id="tr-crm-date-picker"
                                                class="tr-crm-toolbar__date-input"
                                                value="{{ $crmPickerDate }}"
                                                placeholder="{{ TrLang::trans('admin.crm.date_pick_placeholder') }}"
                                                autocomplete="off"
                                                aria-label="{{ TrLang::trans('admin.crm.date_pick_aria') }}"
                                                readonly
                                            >
                                        </label>
                                    </div>
                                </div>
                            </form>

                            <div class="tr-crm-toolbar__search">
                                <i class="fa fa-search" aria-hidden="true"></i>
                                <input
                                    type="search"
                                    id="tr-crm-search"
                                    placeholder="{{ TrLang::trans('admin.crm.search_placeholder') }}"
                                    autocomplete="off"
                                    enterkeyhint="search"
                                >
                            </div>

                            @hasAccess('admin.treatment_reservations.create')
                                <div class="tr-crm-toolbar__actions">
                                    <button
                                        type="button"
                                        class="tr-crm-toolbar__new tr-manual-booking-open-btn"
                                        data-toggle="modal"
                                        data-target="#tr-manual-booking-modal"
                                    >
                                        <span class="tr-crm-toolbar__new-icon" aria-hidden="true">
                                            <i class="fa fa-plus"></i>
                                        </span>
                                        {{ TrLang::trans('admin.crm.new_reservation') }}
                                    </button>
                                </div>
                            @endHasAccess
                        </div>
                    </div>
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

                <div class="tr-reservations-hero__pipeline">
                <div class="tr-reservations-hero__metric tr-reservations-hero__metric--pending">
                    <span class="tr-reservations-hero__metric-value">{{ number_format($stats['pending']) }}</span>
                    <span class="tr-reservations-hero__metric-label">{{ TrLang::trans('admin.kanban.pending') }}</span>
                </div>
                <div class="tr-reservations-hero__metric tr-reservations-hero__metric--progress">
                    <span class="tr-reservations-hero__metric-value">{{ number_format($stats['inProgress']) }}</span>
                    <span class="tr-reservations-hero__metric-label">{{ TrLang::trans('admin.kanban.in_progress') }}</span>
                </div>
                <div class="tr-reservations-hero__metric tr-reservations-hero__metric--completed">
                    <span class="tr-reservations-hero__metric-value">{{ number_format($stats['completed']) }}</span>
                    <span class="tr-reservations-hero__metric-label">{{ TrLang::trans('admin.kanban.completed') }}</span>
                </div>
                @if (isset($todayBookings))
                    <div class="tr-reservations-hero__metric tr-reservations-hero__metric--today">
                        <span class="tr-reservations-hero__metric-value">{{ number_format($todayBookings) }}</span>
                        <span class="tr-reservations-hero__metric-label">{{ TrLang::trans('admin.hero.today') }}</span>
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

        @if ($activeView !== 'dashboard')
            <p class="tr-view-back">
                <a href="{{ route('admin.treatment_reservations.index', ['view' => 'dashboard']) }}" class="tr-view-back__link">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    {{ TrLang::trans('admin.dashboard.back') }}
                </a>
            </p>
        @endif

        @if ($activeView === 'calendar')
            @include('treatmentreservation::admin.reservations.partials.filters-calendar')
        @elseif ($activeView === 'kanban')
            @include('treatmentreservation::admin.reservations.partials.filters')
        @endif

        <div class="tab-content tr-tab-panels">
            @if ($activeView === 'dashboard')
                @include('treatmentreservation::admin.reservations.partials.dashboard', [
                    'stats' => $stats,
                    'analytics' => $analytics,
                    'analyticsCharts' => $analyticsCharts,
                    'dashboardData' => $dashboardData,
                    'urgency' => $urgency,
                ])
            @endif

            @if ($activeView === 'calendar')
                @include('treatmentreservation::admin.reservations.partials.calendar')
            @endif

            @if ($activeView === 'kanban')
                @include('treatmentreservation::admin.reservations.partials.kanban')
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
