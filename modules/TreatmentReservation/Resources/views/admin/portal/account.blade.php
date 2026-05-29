@php
    $profileColor = $beautician->profile_color ?? '#6366f1';
    $isActive = (bool) $beautician->is_active;
    $memberSince = $beautician->created_at
        ? $beautician->created_at->timezone(config('app.timezone'))->format('d M Y')
        : null;

    $heroInsights = [
        [
            'type' => 'swatch',
            'swatch' => $profileColor,
            'label' => trans('treatmentreservation::admin.portal.hero_accent_color'),
            'value' => strtoupper($profileColor),
        ],
        [
            'icon' => 'fa-calendar',
            'label' => trans('treatmentreservation::admin.portal.hero_member_since'),
            'value' => $memberSince ?: '—',
        ],
        [
            'icon' => 'fa-shopping-cart',
            'label' => trans('treatmentreservation::admin.portal.hero_checkout'),
            'value' => $isActive
                ? trans('beautician::beauticians.form.hero_visible_at_checkout')
                : trans('beautician::beauticians.form.hero_hidden_at_checkout'),
            'value_class' => 'bp-hero-insight-checkout ' . ($isActive ? 'is-active' : 'is-inactive'),
        ],
        [
            'icon' => 'fa-calendar-check-o',
            'label' => trans('treatmentreservation::admin.ical.title'),
            'value' => trans('treatmentreservation::admin.portal.hero_calendar_sync'),
        ],
    ];
@endphp

@extends('admin::layout')

@section('title', trans('treatmentreservation::admin.portal.account_title'))

@section('content_header')
    <h3>{{ $beautician->name }}</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.treatment_reservations.portal') }}">{{ trans('treatmentreservation::admin.portal.title') }}</a></li>
        <li class="active">{{ trans('treatmentreservation::admin.portal.account_title') }}</li>
    </ol>
@endsection

@section('content')
    @include('treatmentreservation::admin.partials.urgency-alerts', [
        'urgencyAlertsAsModal' => true,
    ])

    <div class="tr-portal-profile-page">
        @include('treatmentreservation::admin.portal.partials.profile-hero', [
            'beautician' => $beautician,
            'user' => $user,
            'heroInsights' => $heroInsights,
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
                            <a href="{{ route('admin.treatment_reservations.portal.availability') }}" class="bp-quick-link">
                                <span class="bp-quick-link__icon"><i class="fa fa-clock-o"></i></span>
                                <span class="bp-quick-link__body">
                                    <strong>{{ trans('treatmentreservation::admin.availability.title') }}</strong>
                                    <span>{{ trans('treatmentreservation::admin.availability.subtitle') }}</span>
                                </span>
                                <i class="fa fa-chevron-right bp-quick-link__arrow"></i>
                            </a>
                        </nav>
                    </div>
                </div>

                <div class="bp-card">
                    <div class="bp-card-header">
                        <h3>{{ trans('treatmentreservation::admin.portal.contact_info') }}</h3>
                        <p>{{ trans('treatmentreservation::admin.portal.contact_info_help') }}</p>
                    </div>
                    <div class="bp-card-body">
                        <dl class="bp-info-list">
                            <div class="bp-info-list__item">
                                <dt>{{ trans('user::attributes.users.first_name') }}</dt>
                                <dd>{{ $beautician->first_name }}</dd>
                            </div>
                            <div class="bp-info-list__item">
                                <dt>{{ trans('user::attributes.users.last_name') }}</dt>
                                <dd>{{ $beautician->last_name }}</dd>
                            </div>
                            <div class="bp-info-list__item">
                                <dt>{{ trans('treatmentreservation::admin.portal.login_email') }}</dt>
                                <dd>{{ $user->email }}</dd>
                            </div>
                            @if ($user->phone)
                                <div class="bp-info-list__item">
                                    <dt>{{ trans('beautician::attributes.phone') }}</dt>
                                    <dd>{{ $user->phone }}</dd>
                                </div>
                            @endif
                            @if ($beautician->job_title)
                                <div class="bp-info-list__item">
                                    <dt>{{ trans('beautician::attributes.job_title') }}</dt>
                                    <dd>{{ $beautician->job_title }}</dd>
                                </div>
                            @endif
                            @if ($user->date_of_birth)
                                <div class="bp-info-list__item">
                                    <dt>{{ trans('treatmentreservation::admin.portal.date_of_birth') }}</dt>
                                    <dd>{{ $user->date_of_birth->format('d M Y') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 bp-layout-main">
                <div class="bp-card">
                    <div class="bp-card-header">
                        <h3>{{ trans('treatmentreservation::admin.portal.profile_details') }}</h3>
                        <p>{{ trans('treatmentreservation::admin.portal.profile_details_help') }}</p>
                    </div>
                    <div class="bp-card-body">
                        <form method="POST" action="{{ route('admin.treatment_reservations.portal.account.profile') }}" class="bp-account-form">
                            @csrf
                            @method('PUT')

                            <div class="bp-account-form__fields">
                                <div class="bp-form-row">
                                    {{ Form::text('first_name', trans('user::attributes.users.first_name'), $errors, $beautician, [
                                        'required' => true,
                                        'class' => 'bp-input',
                                    ]) }}

                                    {{ Form::text('last_name', trans('user::attributes.users.last_name'), $errors, $beautician, [
                                        'required' => true,
                                        'class' => 'bp-input',
                                    ]) }}
                                </div>

                                {{ Form::email('email', trans('treatmentreservation::admin.portal.login_email'), $errors, $user, [
                                    'required' => true,
                                    'class' => 'bp-input',
                                ]) }}

                                {{ Form::phone('phone', trans('beautician::attributes.phone'), $errors, $user, [
                                    'required' => true,
                                    'class' => 'bp-input',
                                    'help' => trans('treatmentreservation::admin.portal.phone_help'),
                                ]) }}

                                @include('beautician::admin.partials.job_title_field', ['beautician' => $beautician])

                                <div class="form-group {{ $errors->has('date_of_birth') ? 'has-error' : '' }}">
                                    <label for="portal_date_of_birth">{{ trans('treatmentreservation::admin.portal.date_of_birth') }}</label>
                                    <input
                                        type="date"
                                        name="date_of_birth"
                                        id="portal_date_of_birth"
                                        class="form-control bp-input"
                                        value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                                        max="{{ now()->subDay()->format('Y-m-d') }}"
                                    >
                                    <p class="help-block">{{ trans('treatmentreservation::admin.portal.date_of_birth_help') }}</p>
                                    {!! $errors->first('date_of_birth', '<span class="help-block text-red">:message</span>') !!}
                                </div>
                            </div>

                            <div class="bp-form-actions">
                                <button type="submit" class="btn btn-primary">
                                    {{ trans('treatmentreservation::admin.portal.save_profile') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bp-card">
                    <div class="bp-card-header">
                        <h3>{{ trans('treatmentreservation::admin.ical.title') }}</h3>
                        <p>{{ trans('treatmentreservation::admin.ical.help') }}</p>
                    </div>
                    <div class="bp-card-body">
                        <div class="bp-ical-field">
                            <label for="tr-ical-url">{{ trans('treatmentreservation::admin.portal.calendar_feed_url') }}</label>
                            <div class="bp-ical-field__input">
                                <input type="text" class="form-control" id="tr-ical-url" value="{{ $icalUrl }}" readonly>
                                <button type="button" class="btn btn-default" id="tr-ical-copy-btn" data-copy-success="{{ trans('treatmentreservation::admin.ical.copied') }}">
                                    <i class="fa fa-copy"></i>
                                    {{ trans('treatmentreservation::admin.ical.copy') }}
                                </button>
                            </div>
                        </div>

                        <div
                            class="bp-ical-actions"
                            id="tr-ical-actions"
                            data-webcal-url="{{ $icalWebcalUrl }}"
                            data-google-url="{{ $icalGoogleUrl }}"
                            data-outlook-url="{{ $icalOutlookUrl }}"
                        >
                            <a
                                href="{{ $icalWebcalUrl }}"
                                class="btn btn-primary"
                                id="tr-ical-add-btn"
                            >
                                <i class="fa fa-calendar-plus-o"></i>
                                {{ trans('treatmentreservation::admin.ical.add_to_calendar') }}
                            </a>

                            <p class="bp-ical-actions__hint">{{ trans('treatmentreservation::admin.ical.add_to_calendar_hint') }}</p>

                            <div class="bp-ical-actions__platforms">
                                <a href="{{ $icalGoogleUrl }}" class="btn btn-default" target="_blank" rel="noopener noreferrer">
                                    <i class="fa fa-google"></i>
                                    {{ trans('treatmentreservation::admin.ical.google_calendar') }}
                                </a>
                                <a href="{{ $icalWebcalUrl }}" class="btn btn-default">
                                    <i class="fa fa-apple"></i>
                                    {{ trans('treatmentreservation::admin.ical.apple_calendar') }}
                                </a>
                                <a href="{{ $icalOutlookUrl }}" class="btn btn-default" target="_blank" rel="noopener noreferrer">
                                    <i class="fa fa-windows"></i>
                                    {{ trans('treatmentreservation::admin.ical.outlook_calendar') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bp-card">
                    <div class="bp-card-header">
                        <h3>{{ trans('treatmentreservation::admin.portal.change_password') }}</h3>
                        <p>{{ trans('treatmentreservation::admin.portal.change_password_help') }}</p>
                    </div>
                    <div class="bp-card-body">
                        <form method="POST" action="{{ route('admin.treatment_reservations.portal.account.password') }}" class="bp-account-form">
                            @csrf
                            @method('PUT')

                            <div class="bp-account-form__fields bp-form-row bp-form-row--3 bp-form-row--divided">
                                <div class="form-group {{ $errors->has('current_password') ? 'has-error' : '' }}">
                                    <label for="current_password">{{ trans('treatmentreservation::admin.portal.current_password') }}</label>
                                    <input
                                        type="password"
                                        name="current_password"
                                        id="current_password"
                                        class="form-control bp-input"
                                        autocomplete="current-password"
                                        required
                                    >
                                    {!! $errors->first('current_password', '<span class="help-block text-red">:message</span>') !!}
                                </div>

                                <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                                    <label for="password">{{ trans('treatmentreservation::admin.portal.new_password') }}</label>
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control bp-input"
                                        autocomplete="new-password"
                                        required
                                    >
                                    {!! $errors->first('password', '<span class="help-block text-red">:message</span>') !!}
                                </div>

                                <div class="form-group {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                                    <label for="password_confirmation">{{ trans('treatmentreservation::admin.portal.confirm_password') }}</label>
                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        id="password_confirmation"
                                        class="form-control bp-input"
                                        autocomplete="new-password"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="bp-form-actions">
                                <button type="submit" class="btn btn-primary">
                                    {{ trans('treatmentreservation::admin.portal.update_password') }}
                                </button>
                            </div>
                        </form>
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
