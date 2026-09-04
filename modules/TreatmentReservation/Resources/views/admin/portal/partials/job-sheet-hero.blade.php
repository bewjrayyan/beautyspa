@php
    $profileColor = $beautician->profile_color ?? '#6366f1';
    $todayCount = $todayAppointments->count();
    $showPortalActions = empty($adminPortalPreview);
    $onBeauticianRoute = request()->routeIs('admin.beauticians.portal*');

    $portalDashboardUrl = $onBeauticianRoute
        ? route('admin.beauticians.portal.dashboard', $beautician->id)
        : route('admin.treatment_reservations.portal');
    $portalJobSheetUrl = $onBeauticianRoute
        ? route('admin.beauticians.portal', $beautician->id)
        : route('admin.treatment_reservations.portal.job_sheet');
    $portalCalendarUrl = $onBeauticianRoute
        ? route('admin.beauticians.portal.calendar_page', $beautician->id)
        : route('admin.treatment_reservations.portal.calendar_page');
    $portalAccountUrl = $onBeauticianRoute
        ? route('admin.beauticians.portal.account', $beautician->id)
        : route('admin.treatment_reservations.portal.account');
    $activePortalNav = $activePortalNav ?? 'job_sheet';
    $portalCanCreate = $portalCanCreate ?? (
        (! empty($adminPortalPreview) && auth()->user()?->hasAccess('admin.treatment_reservations.create'))
        || (empty($adminPortalPreview) && auth()->user()?->hasAccess('admin.treatment_reservations.portal.create'))
    );
    $manualBookingModalId = $manualBookingModalId ?? 'tr-portal-manual-booking-modal';
    $portalAvailabilityUrl = $onBeauticianRoute
        ? route('admin.beauticians.portal.availability', $beautician->id)
        : route('admin.treatment_reservations.portal.availability');
@endphp

<header class="tr-portal-saas-hero" style="--tr-portal-accent: {{ $profileColor }};">
    <div class="tr-portal-saas-hero__mesh" aria-hidden="true"></div>

    <div class="tr-portal-saas-hero__top">
        <div class="tr-portal-saas-hero__profile">
            @include('treatmentreservation::admin.portal.partials.avatar', [
                'beautician' => $beautician,
                'user' => $beautician->user,
                'class' => ' tr-portal-avatar--lg tr-portal-avatar--hero',
            ])
        </div>

        <div class="tr-portal-saas-hero__intro">
            <h1 class="tr-portal-saas-hero__title">
                {{ trans('treatmentreservation::admin.portal.job_sheet_title', ['name' => $beautician->first_name]) }}
            </h1>
            <p class="tr-portal-saas-hero__lead">
                {{ trans('treatmentreservation::admin.portal.job_sheet_lead') }}
            </p>
        </div>

        <div class="tr-portal-saas-hero__aside">
            <nav class="tr-portal-saas-hero__nav" aria-label="{{ trans('treatmentreservation::admin.portal.job_sheet_nav_aria') }}">
                <a
                    href="{{ $portalDashboardUrl }}"
                    class="tr-portal-saas-hero__nav-link{{ $activePortalNav === 'dashboard' ? ' is-active' : '' }}"
                    @if ($activePortalNav === 'dashboard') aria-current="page" @endif
                >
                    <i class="fa fa-tachometer" aria-hidden="true"></i>
                    {{ trans('treatmentreservation::sidebar.my_job_sheet') }}
                </a>
                <a
                    href="{{ $portalJobSheetUrl }}"
                    class="tr-portal-saas-hero__nav-link{{ $activePortalNav === 'job_sheet' ? ' is-active' : '' }}"
                    @if ($activePortalNav === 'job_sheet') aria-current="page" @endif
                >
                    <i class="fa fa-columns" aria-hidden="true"></i>
                    {{ trans('treatmentreservation::admin.portal.tab_kanban') }}
                </a>
                <a
                    href="{{ $portalCalendarUrl }}"
                    class="tr-portal-saas-hero__nav-link{{ $activePortalNav === 'calendar' ? ' is-active' : '' }}"
                    @if ($activePortalNav === 'calendar') aria-current="page" @endif
                >
                    <i class="fa fa-calendar" aria-hidden="true"></i>
                    {{ trans('treatmentreservation::admin.portal.tab_calendar') }}
                </a>
            </nav>

            <span class="tr-portal-saas-hero__eyebrow">
                <span class="tr-portal-saas-hero__live-dot" aria-hidden="true"></span>
                {{ trans('treatmentreservation::admin.portal.job_sheet_eyebrow') }}
            </span>
        </div>
    </div>

    <div class="tr-portal-saas-hero__toolbar">
        <div class="tr-portal-saas-hero__chips" role="list">
            <span class="tr-portal-saas-hero__chip tr-portal-saas-hero__chip--today" role="listitem">
                <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
                {{ trans('treatmentreservation::admin.portal.job_sheet_chip_today', ['count' => $todayCount]) }}
            </span>
            <span class="tr-portal-saas-hero__chip tr-portal-saas-hero__chip--pending" role="listitem">
                {{ number_format($stats['pending']) }} {{ trans('treatmentreservation::admin.kanban.pending') }}
            </span>
            <span class="tr-portal-saas-hero__chip tr-portal-saas-hero__chip--progress" role="listitem">
                {{ number_format($stats['inProgress']) }} {{ trans('treatmentreservation::admin.kanban.in_progress') }}
            </span>
        </div>

        <div class="tr-portal-saas-hero__actions">
            @if (! empty($portalCanCreate))
                <button
                    type="button"
                    class="tr-portal-saas-hero__btn tr-portal-saas-hero__btn--primary tr-manual-booking-open-btn"
                    data-toggle="modal"
                    data-target="#{{ $manualBookingModalId }}"
                >
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    {{ trans('treatmentreservation::admin.crm.new_reservation') }}
                </button>
            @endif

            @if ($showPortalActions)
                <a href="{{ $portalAvailabilityUrl }}" class="tr-portal-saas-hero__btn">
                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                    {{ trans('treatmentreservation::admin.availability.title') }}
                </a>
                <a href="{{ $portalAccountUrl }}" class="tr-portal-saas-hero__btn tr-portal-saas-hero__btn--ghost">
                    <i class="fa fa-user" aria-hidden="true"></i>
                    {{ trans('treatmentreservation::admin.portal.account_title') }}
                </a>
            @endif
        </div>
    </div>
</header>

@include('treatmentreservation::admin.portal.partials.mobile-navigation', [
    'beautician' => $beautician,
    'activePortalNav' => $activePortalNav,
])
