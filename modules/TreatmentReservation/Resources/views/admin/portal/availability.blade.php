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

@section('title', trans('treatmentreservation::admin.availability.title'))

@section('content_header')
    <h3>{{ $beautician->name }}</h3>

    <ol class="breadcrumb">
        @if (! empty($adminPortalPreview))
            <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
            <li><a href="{{ route('admin.beauticians.index') }}">{{ trans('beautician::beauticians.beauticians') }}</a></li>
            <li><a href="{{ route('admin.beauticians.edit', $beautician) }}">{{ trans('beautician::beauticians.form.edit_profile') }}</a></li>
            <li class="active">{{ trans('treatmentreservation::admin.availability.title') }}</li>
        @else
            <li><a href="{{ route('admin.treatment_reservations.portal') }}">{{ trans('treatmentreservation::admin.portal.title') }}</a></li>
            <li class="active">{{ trans('treatmentreservation::admin.availability.title') }}</li>
        @endif
    </ol>
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
                <form method="POST" action="{{ $availabilityRoutes['hours'] }}" id="tr-availability-hours-form">
                    @csrf
                    @method('PUT')

                    <div class="bp-card">
                        <div class="bp-card-header">
                            <h3>{{ trans('treatmentreservation::admin.availability.weekly_hours') }}</h3>
                            <p>{{ trans('treatmentreservation::admin.availability.weekly_hours_help') }}</p>
                        </div>
                        <div class="bp-card-body">
                            <div class="bp-availability-days">
                                @foreach ($days as $dayIndex => $dayLabel)
                                    @php
                                        $row = $workingHours->firstWhere('day_of_week', $dayIndex);
                                        $isEnabled = (bool) $row;
                                    @endphp
                                    <div class="bp-availability-day {{ $isEnabled ? 'is-enabled' : '' }}" data-availability-day>
                                        <input type="hidden" name="hours[{{ $dayIndex }}][day_of_week]" value="{{ $dayIndex }}">

                                        <div class="bp-availability-day__label">
                                            <span class="bp-availability-day__name">{{ $dayLabel }}</span>
                                            <span class="bp-availability-day__status">
                                                {{ $isEnabled
                                                    ? trans('treatmentreservation::admin.availability.day_available')
                                                    : trans('treatmentreservation::admin.availability.day_off') }}
                                            </span>
                                        </div>

                                        <label class="bp-switch bp-availability-day__switch">
                                            <input
                                                type="checkbox"
                                                name="hours[{{ $dayIndex }}][enabled]"
                                                value="1"
                                                class="bp-availability-day__toggle"
                                                {{ $isEnabled ? 'checked' : '' }}
                                            >
                                            <span class="bp-switch-slider"></span>
                                        </label>

                                        <div class="bp-availability-day__times">
                                            <input
                                                type="time"
                                                name="hours[{{ $dayIndex }}][start_time]"
                                                class="form-control bp-input bp-availability-day__time"
                                                value="{{ $row ? \Illuminate\Support\Str::substr($row->start_time, 0, 5) : '10:00' }}"
                                                {{ $isEnabled ? '' : 'disabled' }}
                                            >
                                            <span class="bp-availability-day__sep">–</span>
                                            <input
                                                type="time"
                                                name="hours[{{ $dayIndex }}][end_time]"
                                                class="form-control bp-input bp-availability-day__time"
                                                value="{{ $row ? \Illuminate\Support\Str::substr($row->end_time, 0, 5) : '18:00' }}"
                                                {{ $isEnabled ? '' : 'disabled' }}
                                            >
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="bp-form-actions">
                                <button type="submit" class="btn btn-primary">
                                    {{ trans('treatmentreservation::admin.availability.save_hours') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="bp-card">
                    <div class="bp-card-header">
                        <h3>{{ trans('treatmentreservation::admin.availability.blocked_times') }}</h3>
                        <p>{{ trans('treatmentreservation::admin.availability.blocked_times_help') }}</p>
                    </div>
                    <div class="bp-card-body">
                        <form method="POST" action="{{ $availabilityRoutes['blocks'] }}" class="bp-block-form">
                            @csrf

                            <div class="bp-block-form__grid">
                                <div class="form-group">
                                    <label for="block_date">{{ trans('treatmentreservation::admin.availability.block_date') }}</label>
                                    <input
                                        type="date"
                                        name="block_date"
                                        id="block_date"
                                        class="form-control bp-input"
                                        required
                                        min="{{ today()->toDateString() }}"
                                    >
                                </div>
                                <div class="form-group">
                                    <label for="block_start_time">{{ trans('treatmentreservation::admin.availability.start') }}</label>
                                    <input type="time" name="start_time" id="block_start_time" class="form-control bp-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="block_end_time">{{ trans('treatmentreservation::admin.availability.end') }}</label>
                                    <input type="time" name="end_time" id="block_end_time" class="form-control bp-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="block_note">{{ trans('treatmentreservation::admin.availability.note') }}</label>
                                    <input type="text" name="note" id="block_note" class="form-control bp-input" maxlength="255">
                                </div>
                            </div>

                            <div class="bp-block-form__actions">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-plus"></i>
                                    {{ trans('treatmentreservation::admin.availability.add_block') }}
                                </button>
                            </div>
                        </form>

                        @if ($blockedTimes->isNotEmpty())
                            <div class="bp-block-list">
                                @foreach ($blockedTimes as $block)
                                    <article class="bp-block-item">
                                        <div class="bp-block-item__main">
                                            <strong>{{ $block->block_date->format('d M Y') }}</strong>
                                            <span class="bp-block-item__time">
                                                {{ \Illuminate\Support\Str::substr($block->start_time, 0, 5) }}
                                                –
                                                {{ \Illuminate\Support\Str::substr($block->end_time, 0, 5) }}
                                            </span>
                                            @if ($block->note)
                                                <span class="bp-block-item__note">{{ $block->note }}</span>
                                            @endif
                                        </div>
                                        <form
                                            method="POST"
                                            action="{{ $destroyBlockUrl($block->id) }}"
                                            onsubmit="return confirm(@json(trans('treatmentreservation::admin.availability.remove_confirm')));"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i>
                                                {{ trans('treatmentreservation::admin.availability.remove') }}
                                            </button>
                                        </form>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <p class="bp-empty-state">{{ trans('treatmentreservation::admin.availability.no_blocks') }}</p>
                        @endif
                    </div>
                </div>
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
