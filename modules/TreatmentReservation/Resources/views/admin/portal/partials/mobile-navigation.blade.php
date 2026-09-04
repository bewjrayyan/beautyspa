@php
    $mobilePortalActive = $activePortalNav ?? 'dashboard';
    $onBeauticianRoute = request()->routeIs('admin.beauticians.portal*');
    $mobilePortalRoutes = $onBeauticianRoute
        ? [
            'dashboard' => route('admin.beauticians.portal.dashboard', $beautician->id),
            'job_sheet' => route('admin.beauticians.portal', $beautician->id),
            'calendar' => route('admin.beauticians.portal.calendar_page', $beautician->id),
            'availability' => route('admin.beauticians.portal.availability', $beautician->id),
            'account' => route('admin.beauticians.portal.account', $beautician->id),
        ]
        : [
            'dashboard' => route('admin.treatment_reservations.portal'),
            'job_sheet' => route('admin.treatment_reservations.portal.job_sheet'),
            'calendar' => route('admin.treatment_reservations.portal.calendar_page'),
            'availability' => route('admin.treatment_reservations.portal.availability'),
            'account' => route('admin.treatment_reservations.portal.account'),
        ];
    $mobilePortalItems = [
        'dashboard' => ['icon' => 'fa-tachometer', 'label' => trans('treatmentreservation::admin.portal.mobile_nav_dashboard')],
        'job_sheet' => ['icon' => 'fa-columns', 'label' => trans('treatmentreservation::admin.portal.mobile_nav_jobs')],
        'calendar' => ['icon' => 'fa-calendar', 'label' => trans('treatmentreservation::admin.portal.mobile_nav_calendar')],
        'availability' => ['icon' => 'fa-clock-o', 'label' => trans('treatmentreservation::admin.portal.mobile_nav_availability')],
        'account' => ['icon' => 'fa-user', 'label' => trans('treatmentreservation::admin.portal.mobile_nav_account')],
    ];
@endphp

<nav class="tr-portal-mobile-nav" aria-label="{{ trans('treatmentreservation::admin.portal.mobile_nav_aria') }}">
    <div class="tr-portal-mobile-nav__track">
        @foreach ($mobilePortalItems as $key => $item)
            <a
                href="{{ $mobilePortalRoutes[$key] }}"
                class="tr-portal-mobile-nav__item{{ $mobilePortalActive === $key ? ' is-active' : '' }}"
                @if ($mobilePortalActive === $key) aria-current="page" @endif
            >
                <span class="tr-portal-mobile-nav__icon" aria-hidden="true">
                    <i class="fa {{ $item['icon'] }}"></i>
                </span>
                <span class="tr-portal-mobile-nav__label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
