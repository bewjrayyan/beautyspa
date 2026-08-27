@php
    $enabledDaysCount = collect($days)->filter(
        fn ($label, $index) => $workingHours->firstWhere('day_of_week', $index)
    )->count();

    $availabilityRoutes = $availabilityRoutes ?? [
        'hours' => route('admin.treatment_reservations.portal.availability.hours'),
        'blocks' => route('admin.treatment_reservations.portal.availability.blocks'),
    ];
    $destroyBlockUrl = function (int $blockId) use ($adminPortalPreview, $beautician) {
        if (! empty($adminPortalPreview)) {
            return route('admin.beauticians.portal.availability.blocks.destroy', [
                'id' => $beautician->id,
                'blockId' => $blockId,
            ]);
        }

        return route('admin.treatment_reservations.portal.availability.blocks.destroy', $blockId);
    };

    $heroInsights = [
        [
            'icon' => 'fa-calendar-check-o',
            'label' => trans('treatmentreservation::admin.availability.hero_days_available'),
            'value' => trans('treatmentreservation::admin.availability.hero_days_available_value', [
                'count' => $enabledDaysCount,
            ]),
        ],
        [
            'icon' => 'fa-ban',
            'label' => trans('treatmentreservation::admin.availability.hero_upcoming_blocks'),
            'value' => trans('treatmentreservation::admin.availability.hero_upcoming_blocks_value', [
                'count' => $blockedTimes->count(),
            ]),
        ],
        [
            'icon' => 'fa-clock-o',
            'label' => trans('treatmentreservation::admin.availability.hero_slot_duration'),
            'value' => trans('treatmentreservation::admin.availability.hero_slot_duration_value'),
        ],
        [
            'icon' => 'fa-calendar',
            'label' => trans('treatmentreservation::admin.availability.hero_default_hours'),
            'value' => trans('treatmentreservation::admin.availability.hero_default_hours_value'),
        ],
    ];

    $heroStats = [
        [
            'label' => trans('treatmentreservation::admin.availability.hero_days_available'),
            'value' => $enabledDaysCount,
        ],
        [
            'label' => trans('treatmentreservation::admin.availability.hero_upcoming_blocks'),
            'value' => $blockedTimes->count(),
        ],
        [
            'label' => trans('treatmentreservation::admin.availability.hero_slot_duration'),
            'value' => trans('treatmentreservation::admin.availability.hero_slot_duration_short'),
        ],
    ];
@endphp

@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', $beautician->name)
    @slot('subtitle', trans('treatmentreservation::admin.availability.title'))

    @if (! empty($adminPortalPreview))
        <li>
            <a href="{{ route('admin.beauticians.index') }}">{{ trans('beautician::beauticians.beauticians') }}</a>
        </li>
        <li>
            <a href="{{ route('admin.beauticians.edit', $beautician) }}">{{ trans('beautician::beauticians.form.edit_profile') }}</a>
        </li>
        <li class="active">{{ trans('treatmentreservation::admin.availability.title') }}</li>
    @else
        <li>
            <a href="{{ route('admin.treatment_reservations.portal') }}">{{ trans('treatmentreservation::admin.portal.title') }}</a>
        </li>
        <li class="active">{{ trans('treatmentreservation::admin.availability.title') }}</li>
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
        class="tr-portal-profile-page"
        data-day-available="{{ trans('treatmentreservation::admin.availability.day_available') }}"
        data-day-off="{{ trans('treatmentreservation::admin.availability.day_off') }}"
    >
        @include('treatmentreservation::admin.portal.partials.profile-hero', [
            'beautician' => $beautician,
            'user' => $user,
            'heroInsights' => $heroInsights,
            'heroStats' => $heroStats,
        ])

        <div class="row bp-layout">
            <div class="col-lg-3 bp-layout-sidebar">
                <div class="bp-card">
                    <div class="bp-card-header">
                        <h3>{{ trans('treatmentreservation::admin.portal.quick_links') }}</h3>
                        <p>{{ trans('treatmentreservation::admin.portal.quick_links_help') }}</p>
                    </div>
                    <div class="bp-card-body">
                        <nav class="bp-quick-links">
                            <a href="{{ route('admin.treatment_reservations.portal') }}" class="bp-quick-link">
                                <span class="bp-quick-link__icon"><i class="fa fa-columns"></i></span>
                                <span class="bp-quick-link__body">
                                    <strong>{{ trans('treatmentreservation::admin.portal.tab_kanban') }}</strong>
                                    <span>{{ trans('treatmentreservation::admin.portal.subtitle') }}</span>
                                </span>
                                <i class="fa fa-chevron-right bp-quick-link__arrow"></i>
                            </a>
                            <a href="{{ route('admin.treatment_reservations.portal.account') }}" class="bp-quick-link">
                                <span class="bp-quick-link__icon"><i class="fa fa-user"></i></span>
                                <span class="bp-quick-link__body">
                                    <strong>{{ trans('treatmentreservation::admin.portal.account_title') }}</strong>
                                    <span>{{ trans('treatmentreservation::admin.portal.account_subtitle') }}</span>
                                </span>
                                <i class="fa fa-chevron-right bp-quick-link__arrow"></i>
                            </a>
                        </nav>
                    </div>
                </div>

                <div class="bp-card">
                    <div class="bp-card-header">
                        <h3>{{ trans('treatmentreservation::admin.availability.summary_title') }}</h3>
                        <p>{{ trans('treatmentreservation::admin.availability.summary_help') }}</p>
                    </div>
                    <div class="bp-card-body">
                        <dl class="bp-info-list">
                            <div class="bp-info-list__item">
                                <dt>{{ trans('treatmentreservation::admin.availability.hero_days_available') }}</dt>
                                <dd>{{ trans('treatmentreservation::admin.availability.hero_days_available_value', ['count' => $enabledDaysCount]) }}</dd>
                            </div>
                            <div class="bp-info-list__item">
                                <dt>{{ trans('treatmentreservation::admin.availability.hero_upcoming_blocks') }}</dt>
                                <dd>{{ trans('treatmentreservation::admin.availability.hero_upcoming_blocks_value', ['count' => $blockedTimes->count()]) }}</dd>
                            </div>
                            <div class="bp-info-list__item">
                                <dt>{{ trans('treatmentreservation::admin.availability.hero_slot_duration') }}</dt>
                                <dd>{{ trans('treatmentreservation::admin.availability.hero_slot_duration_value') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 bp-layout-main">
                @include('treatmentreservation::admin.portal.partials.availability-settings', [
                    'workingHours' => $workingHours,
                    'blockedTimes' => $blockedTimes,
                    'days' => $days,
                    'availabilityRoutes' => $availabilityRoutes,
                    'destroyBlockUrl' => $destroyBlockUrl,
                ])
            </div>
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/TreatmentReservation/Resources/assets/admin/sass/main.scss',
        'modules/TreatmentReservation/Resources/assets/admin/js/main.js',
    ])
@endpush
