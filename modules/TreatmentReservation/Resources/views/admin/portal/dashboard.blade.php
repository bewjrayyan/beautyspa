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
@endphp

@extends('admin::layout')

@section('title', TrLang::trans('admin.portal.dashboard_title'))

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
        class="tr-reservations tr-reservations-page tr-reservations--view-dashboard tr-reservations--crm-dashboard tr-reservations--mockup tr-portal-crm-dashboard"
        id="tr-reservations-app"
        data-active-view="dashboard"
        data-calendar-legend-label="{{ TrLang::trans('admin.calendar.legend_label') }}"
        data-cal-preview-title="{{ TrLang::trans('admin.calendar.preview_title') }}"
        data-cal-preview-date="{{ TrLang::trans('admin.calendar.preview_date') }}"
        data-cal-preview-time="{{ TrLang::trans('admin.calendar.preview_time') }}"
        data-cal-preview-customer="{{ TrLang::trans('admin.calendar.preview_customer') }}"
        data-cal-preview-treatment="{{ TrLang::trans('admin.calendar.preview_treatment') }}"
        data-cal-preview-category="{{ TrLang::trans('admin.calendar.preview_category') }}"
        data-cal-preview-phone="{{ TrLang::trans('admin.calendar.preview_phone') }}"
        data-cal-preview-email="{{ TrLang::trans('admin.calendar.preview_email') }}"
        data-cal-preview-order-notes="{{ TrLang::trans('admin.calendar.preview_order_notes') }}"
        data-cal-preview-beautician-notes="{{ TrLang::trans('admin.calendar.preview_beautician_notes') }}"
        data-cal-status-pending="{{ TrLang::trans('admin.kanban.pending') }}"
        data-cal-status-in-progress="{{ TrLang::trans('admin.kanban.in_progress') }}"
        data-cal-status-completed="{{ TrLang::trans('admin.kanban.completed') }}"
        data-cal-empty-label="{{ TrLang::trans('admin.calendar.no_bookings') }}"
        data-calendar-url="{{ $crmRoutes['calendar'] ?? '' }}"
        data-status-url="{{ $crmRoutes['updateStatus'] ?? '' }}"
        data-whatsapp-url="{{ $crmRoutes['whatsapp'] ?? '' }}"
        data-reminder-url="{{ $crmRoutes['reminder'] ?? '' }}"
        data-whatsapp-configured="{{ OneSenderWhatsAppService::isConfigured() ? '1' : '0' }}"
        data-cal-preview-whatsapp-sending="{{ TrLang::trans('admin.calendar.preview_whatsapp_sending') }}"
        data-cal-preview-whatsapp-sent="{{ TrLang::trans('admin.calendar.preview_whatsapp_sent') }}"
        data-cal-preview-whatsapp-failed="{{ TrLang::trans('admin.calendar.preview_whatsapp_failed') }}"
        data-cal-preview-whatsapp-not-configured="{{ TrLang::trans('admin.calendar.whatsapp_not_configured') }}"
        data-cal-preview-whatsapp-customer="{{ TrLang::trans('admin.calendar.preview_whatsapp_customer') }}"
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
        data-cal-preview-duration-minutes="{{ TrLang::trans('admin.calendar.preview_duration_value') }}"
        data-cal-preview-status-update-failed="{{ TrLang::trans('admin.crm.agenda_status_update_failed') }}"
        data-initial-month="{{ $filters['month'] ?? now()->format('Y-m') }}"
        data-initial-beautician="{{ $filters['beautician_id'] ?? '' }}"
        data-initial-category="{{ $filters['treatment_category_id'] ?? '' }}"
    >
        <header class="tr-crm-page-header">
            <div class="tr-crm-page-header__intro">
                <h1 class="tr-crm-page-header__title">{{ TrLang::trans('admin.portal.dashboard_title') }}</h1>
                <p class="tr-crm-page-header__lead">{{ TrLang::trans('admin.portal.dashboard_lead') }}</p>

                <div class="tr-crm-page-header__toolbar">
                    <div class="tr-crm-toolbar">
                        <form class="tr-crm-toolbar__filters-form" method="get" action="{{ $crmRoutes['formAction'] ?? '' }}" id="tr-crm-header-form">
                            <input type="hidden" name="date_filter" id="tr-crm-date-filter" value="{{ $crmDateFilter }}">
                            <input type="hidden" name="filter_date" id="tr-crm-filter-date" value="{{ $filters['filter_date'] ?? '' }}">
                            <input type="hidden" name="treatment_category_id" id="tr-crm-hidden-category" value="{{ $filters['treatment_category_id'] ?? '' }}">
                            <input type="hidden" name="beautician_id" value="{{ $filters['beautician_id'] ?? $beautician->id }}">

                            @if (! empty($portalFilterContext['locked']))
                                <div class="tr-crm-toolbar__context tr-crm-toolbar__context--beautician">
                                    <span class="tr-crm-toolbar__field-icon" aria-hidden="true">
                                        <i class="fa fa-user"></i>
                                    </span>
                                    <span class="tr-crm-toolbar__context-chip">
                                        @if (! empty($portalFilterContext['beautician_avatar']))
                                            <img
                                                src="{{ $portalFilterContext['beautician_avatar'] }}"
                                                alt=""
                                                class="tr-crm-toolbar__context-avatar"
                                            >
                                        @else
                                            <span
                                                class="tr-crm-toolbar__context-avatar tr-crm-toolbar__context-avatar--initial"
                                                style="background-color: {{ $portalFilterContext['beautician_color'] ?? '#6366f1' }}"
                                            >{{ $portalFilterContext['beautician_initial'] ?? '?' }}</span>
                                        @endif
                                        <span class="tr-crm-toolbar__context-label">{{ $portalFilterContext['beautician_name'] ?? $beautician->name }}</span>
                                    </span>
                                </div>
                                <span class="tr-crm-toolbar__divider" aria-hidden="true"></span>
                            @endif

                            @if (! empty($portalFilterContext['branch_locked']) && ! empty($portalFilterContext['branch_name']))
                                <input type="hidden" name="spa_branch_id" value="{{ $filters['spa_branch_id'] ?? '' }}">
                                <div class="tr-crm-toolbar__context tr-crm-toolbar__context--branch">
                                    <span class="tr-crm-toolbar__field-icon" aria-hidden="true">
                                        <i class="fa fa-map-marker"></i>
                                    </span>
                                    <span class="tr-crm-toolbar__context-chip tr-crm-toolbar__context-chip--branch">
                                        <span class="tr-crm-toolbar__context-label">{{ $portalFilterContext['branch_name'] }}</span>
                                    </span>
                                </div>
                                <span class="tr-crm-toolbar__divider" aria-hidden="true"></span>
                            @elseif (! empty($portalFilterContext['branch_picker']) && $spaBranches->isNotEmpty())
                                <div class="tr-crm-toolbar__field tr-crm-toolbar__field--branch">
                                    <span class="tr-crm-toolbar__field-icon" aria-hidden="true">
                                        <i class="fa fa-map-marker"></i>
                                    </span>
                                    <label class="sr-only" for="tr-crm-filter-branch">{{ TrLang::trans('admin.filters.spa_branch') }}</label>
                                    <select class="tr-crm-toolbar__select" id="tr-crm-filter-branch" name="spa_branch_id" onchange="this.form.requestSubmit()">
                                        <option value="">{{ TrLang::trans('admin.portal.filter_my_branches') }}</option>
                                        @foreach ($spaBranches as $branchId => $branchName)
                                            <option value="{{ $branchId }}" @selected(($filters['spa_branch_id'] ?? null) == $branchId)>
                                                {{ $branchName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <span class="tr-crm-toolbar__divider" aria-hidden="true"></span>
                            @elseif (empty($portalFilterContext['locked']) && $spaBranches->isNotEmpty())
                                <div class="tr-crm-toolbar__field tr-crm-toolbar__field--branch">
                                    <span class="tr-crm-toolbar__field-icon" aria-hidden="true">
                                        <i class="fa fa-map-marker"></i>
                                    </span>
                                    <label class="sr-only" for="tr-crm-filter-branch">{{ TrLang::trans('admin.filters.spa_branch') }}</label>
                                    <select class="tr-crm-toolbar__select" id="tr-crm-filter-branch" name="spa_branch_id" onchange="this.form.requestSubmit()">
                                        <option value="">{{ TrLang::trans('admin.filters.all_branches') }}</option>
                                        @foreach ($spaBranches as $branchId => $branchName)
                                            <option value="{{ $branchId }}" @selected(($filters['spa_branch_id'] ?? null) == $branchId)>
                                                {{ $branchName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <span class="tr-crm-toolbar__divider" aria-hidden="true"></span>
                            @endif

                            @if ($categories->isNotEmpty())
                                <div class="tr-crm-toolbar__field">
                                    <span class="tr-crm-toolbar__field-icon" aria-hidden="true">
                                        <i class="fa fa-tags"></i>
                                    </span>
                                    <label class="sr-only" for="tr-crm-filter-category">{{ TrLang::trans('admin.filters.category') }}</label>
                                    <select class="tr-crm-toolbar__select" id="tr-crm-filter-category" name="treatment_category_id" onchange="this.form.requestSubmit()">
                                        <option value="">{{ TrLang::trans('admin.filters.all_categories') }}</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected(($filters['treatment_category_id'] ?? null) == $category->id)>
                                                {{ $category->name }}
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

                        @if (! empty($crmCanCreate))
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
                        @endif
                    </div>
                </div>
            </div>
        </header>

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
                'defaultBeauticianId' => $beautician->id,
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
