@extends('admin::layout')

@section('title', trans('admin::dashboard.dashboard'))

@section('content_header')
    <div class="dashboard-modern-header">
        <div>
            <h3>{{ trans('admin::dashboard.dashboard') }}</h3>
            <p class="dashboard-modern-subtitle">{{ trans('admin::dashboard.overview') }}</p>
        </div>

        @if ($beauticianAnalyticsUrl ?? null)
            @hasAccess('admin.beautician_reports.index')
                <a href="{{ $beauticianAnalyticsUrl }}" class="btn btn-default">
                    <i class="fa fa-line-chart"></i>
                    {{ trans('admin::dashboard.beautician_analytics') }}
                </a>
            @endHasAccess
        @endif
    </div>
@endsection

@section('content')
    @if (\Nwidart\Modules\Facades\Module::isEnabled('TreatmentReservation'))
        @include('treatmentreservation::admin.partials.urgency-alerts', [
            'urgencyAlertsAsModal' => true,
        ])
    @endif

    <div class="dashboard-modern">
        @include('admin::dashboard.partials.top_stats')

        @include('admin::dashboard.partials.quick_links')

        @hasAccess('admin.orders.index')
            @include('admin::dashboard.panels.sales_analytics')
        @endHasAccess

        <div class="dashboard-panels__grid">
            @if ($showAppointmentPanels ?? false)
                @hasAccess('admin.orders.index')
                    @include('admin::dashboard.panels.today_appointments')
                    @include('admin::dashboard.panels.upcoming_appointments')
                @endHasAccess
            @endif

            @hasAccess('admin.orders.index')
                @include('admin::dashboard.panels.pending_orders')
                @include('admin::dashboard.panels.latest_orders')
            @endHasAccess

            @hasAccess('admin.products.index')
                @include('admin::dashboard.panels.latest_searches')
            @endHasAccess

            @hasAccess('admin.reviews.index')
                @include('admin::dashboard.panels.latest_reviews')
            @endHasAccess

            @if ($showLoyaltyMembersCard ?? false)
                @hasAccess('admin.loyalty.members.index')
                    @include('admin::dashboard.panels.members')
                @endHasAccess
            @endif

            @hasAccess('admin.users.index')
                @include('admin::dashboard.panels.recent_customers')
            @endHasAccess
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        "modules/Admin/Resources/assets/sass/dashboard.scss",
        "modules/Admin/Resources/assets/js/dashboard.js",
    ])
@endpush
