@extends('admin::layout')

@section('title', trans('treatmentreservation::admin.reservations'))

@section('content_header')
@endsection

@section('content')
    @include('treatmentreservation::admin.partials.urgency-alerts')

    <div class="tr-reservations tr-reservations-page tr-reservations--view-{{ $activeView }}" id="tr-reservations-app"
        data-active-view="{{ $activeView }}"
        data-calendar-legend-label="{{ trans('treatmentreservation::admin.calendar.legend_label') }}"
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
        data-cal-preview-activity-title="{{ trans('treatmentreservation::admin.activity.title') }}"
        data-cal-status-pending="{{ trans('treatmentreservation::admin.kanban.pending') }}"
        data-cal-status-in-progress="{{ trans('treatmentreservation::admin.kanban.in_progress') }}"
        data-cal-status-completed="{{ trans('treatmentreservation::admin.kanban.completed') }}"
        data-cal-empty-label="{{ trans('treatmentreservation::admin.calendar.no_bookings') }}"
        data-calendar-url="{{ route('admin.treatment_reservations.calendar') }}"
        data-kanban-url="{{ route('admin.treatment_reservations.kanban') }}"
        data-status-url="{{ route('admin.treatment_reservations.update_status', ['id' => '__ID__']) }}"
        data-whatsapp-url="{{ route('admin.treatment_reservations.send_whatsapp', ['id' => '__ID__']) }}"
        data-cal-preview-whatsapp-sending="{{ trans('treatmentreservation::admin.calendar.preview_whatsapp_sending') }}"
        data-cal-preview-whatsapp-sent="{{ trans('treatmentreservation::admin.calendar.preview_whatsapp_sent') }}"
        data-cal-preview-whatsapp-failed="{{ trans('treatmentreservation::admin.calendar.preview_whatsapp_failed') }}"
        data-cal-preview-whatsapp-customer="{{ trans('treatmentreservation::admin.calendar.preview_whatsapp_customer') }}"
        data-initial-month="{{ $filters['month'] }}"
        data-initial-beautician="{{ $filters['beautician_id'] }}"
        data-initial-category="{{ $filters['treatment_category_id'] }}"
    >
        <header class="tr-reservations-hero">
            <div class="tr-reservations-hero__main">
                <div class="tr-reservations-hero__icon" aria-hidden="true">
                    <i class="fa fa-calendar-check-o"></i>
                </div>
                <div class="tr-reservations-hero__text">
                    <h1 class="tr-reservations-hero__title">{{ trans('treatmentreservation::admin.reservations') }}</h1>
                    <p class="tr-reservations-hero__lead">{{ trans('treatmentreservation::admin.subtitle') }}</p>
                </div>
            </div>

            <div class="tr-reservations-hero__pipeline">
                <div class="tr-reservations-hero__metric tr-reservations-hero__metric--pending">
                    <span class="tr-reservations-hero__metric-value">{{ number_format($stats['pending']) }}</span>
                    <span class="tr-reservations-hero__metric-label">{{ trans('treatmentreservation::admin.kanban.pending') }}</span>
                </div>
                <div class="tr-reservations-hero__metric tr-reservations-hero__metric--progress">
                    <span class="tr-reservations-hero__metric-value">{{ number_format($stats['inProgress']) }}</span>
                    <span class="tr-reservations-hero__metric-label">{{ trans('treatmentreservation::admin.kanban.in_progress') }}</span>
                </div>
                <div class="tr-reservations-hero__metric tr-reservations-hero__metric--completed">
                    <span class="tr-reservations-hero__metric-value">{{ number_format($stats['completed']) }}</span>
                    <span class="tr-reservations-hero__metric-label">{{ trans('treatmentreservation::admin.kanban.completed') }}</span>
                </div>
                @if (isset($todayBookings))
                    <div class="tr-reservations-hero__metric tr-reservations-hero__metric--today">
                        <span class="tr-reservations-hero__metric-value">{{ number_format($todayBookings) }}</span>
                        <span class="tr-reservations-hero__metric-label">{{ trans('treatmentreservation::admin.hero.today') }}</span>
                    </div>
                @endif
            </div>
        </header>

        @if ($activeView !== 'dashboard')
            <p class="tr-view-back">
                <a href="{{ route('admin.treatment_reservations.index', ['view' => 'dashboard']) }}" class="tr-view-back__link">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    {{ trans('treatmentreservation::admin.dashboard.back') }}
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
