@extends('storefront::public.auth.layout')

@section('title', trans('beautician::beauticians.self_registration.title'))

@push('globals')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --beautician-ink: #241b2e;
            --beautician-muted: #786f7e;
            --beautician-plum: #6f2948;
            --beautician-rose: #d85f88;
            --beautician-blush: #fff4f6;
            --beautician-line: #eadfe4;
        }

        body { background: #f8f4f5 !important; }
        .login-page { overflow: visible !important; }

        .beautician-registration {
            position: relative;
            min-height: 100vh;
            color: var(--beautician-ink);
            background:
                radial-gradient(circle at 96% 4%, rgba(216, 95, 136, .13), transparent 24%),
                #f8f4f5;
        }

        .beautician-registration__shell {
            display: grid;
            min-height: 100vh;
            grid-template-columns: minmax(390px, 42%) minmax(0, 1fr);
        }

        .beautician-registration__story {
            position: sticky;
            top: 0;
            display: flex;
            height: 100vh;
            min-height: 720px;
            padding: clamp(42px, 5vw, 82px);
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-color: rgba(255, 255, 255, .28) transparent;
            scrollbar-width: thin;
            color: #fff;
            flex-direction: column;
            justify-content: space-between;
            background:
                linear-gradient(150deg, rgba(52, 21, 41, .15), rgba(52, 21, 41, .82)),
                linear-gradient(135deg, #ad476e 0%, #6f2948 52%, #301929 100%);
        }

        .beautician-registration__story::before,
        .beautician-registration__story::after {
            position: absolute;
            content: "";
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 50%;
        }

        .beautician-registration__story::before {
            top: -170px;
            right: -190px;
            width: 480px;
            height: 480px;
            box-shadow: 0 0 0 65px rgba(255, 255, 255, .025), 0 0 0 130px rgba(255, 255, 255, .02);
        }

        .beautician-registration__story::after {
            right: -75px;
            bottom: -95px;
            width: 270px;
            height: 270px;
            background: rgba(255, 255, 255, .035);
        }

        .beautician-registration__brand,
        .beautician-registration__story-content,
        .beautician-registration__story-footer { position: relative; z-index: 2; }

        .beautician-registration__brand {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
        }

        .beautician-registration__brand:hover { color: #fff; }
        .beautician-registration__brand-mark { display: grid; width: 46px; height: 46px; place-items: center; color: var(--beautician-plum); background: #fff; border-radius: 50%; box-shadow: 0 9px 25px rgba(31, 10, 22, .22); font-size: 12px; font-weight: 800; letter-spacing: -.03em; }
        .beautician-registration__brand strong { font-size: 20px; letter-spacing: -.02em; }

        .beautician-registration__eyebrow,
        .beautician-registration__form-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .beautician-registration__eyebrow { margin-bottom: 20px; color: #ffdbe7; }
        .beautician-registration__eyebrow::before { width: 28px; height: 1px; content: ""; background: currentColor; }

        .beautician-registration__story h1 {
            max-width: 610px;
            margin-bottom: 20px;
            color: #fff;
            font-size: clamp(42px, 4.4vw, 68px);
            font-weight: 700;
            line-height: 1.02;
            letter-spacing: -.045em;
        }

        .beautician-registration__story-lead {
            max-width: 560px;
            color: rgba(255, 255, 255, .76);
            font-size: 17px;
            line-height: 1.75;
        }

        .beautician-registration__benefits {
            display: grid;
            margin-top: 40px;
            gap: 12px;
        }

        .beautician-registration__benefit {
            display: flex;
            max-width: 520px;
            padding: 15px 17px;
            align-items: center;
            gap: 13px;
            color: rgba(255, 255, 255, .9);
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .11);
            border-radius: 14px;
            backdrop-filter: blur(10px);
        }

        .beautician-registration__benefit-icon {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            place-items: center;
            color: #6f2948;
            background: #fff;
            border-radius: 50%;
            font-weight: 700;
        }

        .beautician-registration__story-footer {
            display: flex;
            padding-top: 26px;
            gap: 22px;
            border-top: 1px solid rgba(255, 255, 255, .16);
        }

        .beautician-registration__story-step { display: flex; align-items: center; gap: 10px; color: rgba(255, 255, 255, .72); font-size: 13px; }
        .beautician-registration__story-step b { display: grid; width: 29px; height: 29px; place-items: center; color: #fff; border: 1px solid rgba(255, 255, 255, .35); border-radius: 50%; }

        @media (min-width: 901px) and (max-height: 1050px) {
            .beautician-registration__story { padding: 28px clamp(32px, 4vw, 56px); }
            .beautician-registration__brand-mark { width: 42px; height: 42px; }
            .beautician-registration__eyebrow { margin-bottom: 12px; }
            .beautician-registration__story h1 { margin-bottom: 14px; font-size: clamp(44px, 3.5vw, 54px); }
            .beautician-registration__story-lead { font-size: 15px; line-height: 1.55; }
            .beautician-registration__benefits { margin-top: 22px; gap: 8px; }
            .beautician-registration__benefit { padding: 10px 14px; }
            .beautician-registration__benefit-icon { width: 30px; height: 30px; flex-basis: 30px; }
            .beautician-registration__story-footer { padding-top: 16px; gap: 14px; }
        }

        @media (min-width: 901px) and (max-height: 820px) {
            .beautician-registration__story { padding-top: 20px; padding-bottom: 20px; }
            .beautician-registration__brand-mark { width: 38px; height: 38px; }
            .beautician-registration__brand strong { font-size: 18px; }
            .beautician-registration__story h1 { font-size: 42px; }
            .beautician-registration__story-lead { font-size: 14px; line-height: 1.45; }
            .beautician-registration__benefits { margin-top: 16px; gap: 6px; }
            .beautician-registration__benefit { padding: 8px 12px; font-size: 13px; }
            .beautician-registration__story-footer { padding-top: 12px; }
        }

        .beautician-registration__main {
            display: flex;
            min-width: 0;
            padding: 54px clamp(34px, 6vw, 92px) 70px;
            justify-content: center;
        }

        .beautician-registration__form-wrap { width: min(780px, 100%); }

        .beautician-registration__topbar {
            display: flex;
            min-height: 44px;
            margin-bottom: 48px;
            align-items: center;
            justify-content: flex-end;
            gap: 20px;
        }

        .beautician-registration__topbar .dropdown { z-index: 50; }
        .beautician-registration__login { color: var(--beautician-plum); font-size: 14px; font-weight: 600; text-decoration: none; }

        .beautician-registration__form-header { margin-bottom: 34px; }
        .beautician-registration__form-eyebrow { margin-bottom: 12px; color: var(--beautician-rose); }
        .beautician-registration__form-header h2 { margin-bottom: 10px; color: var(--beautician-ink); font-size: clamp(30px, 3vw, 42px); letter-spacing: -.035em; }
        .beautician-registration__form-header p { max-width: 650px; color: var(--beautician-muted); font-size: 16px; line-height: 1.7; }

        .beautician-registration__notice {
            display: flex;
            margin: 0 0 28px;
            padding: 16px 18px;
            align-items: flex-start;
            gap: 12px;
            color: #764223;
            background: #fff8ed;
            border: 1px solid #f4dfbd;
            border-radius: 14px;
            line-height: 1.55;
        }

        .beautician-registration__notice-icon { display: grid; width: 26px; height: 26px; flex: 0 0 26px; place-items: center; color: #fff; background: #d58c42; border-radius: 50%; font-weight: 700; }

        .beautician-registration__section {
            margin-bottom: 22px;
            padding: 26px;
            background: #fff;
            border: 1px solid var(--beautician-line);
            border-radius: 20px;
            box-shadow: 0 14px 40px rgba(64, 35, 51, .055);
        }

        .beautician-registration__section-heading {
            display: flex;
            margin-bottom: 24px;
            align-items: center;
            gap: 13px;
        }

        .beautician-registration__section-number {
            display: grid;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            place-items: center;
            color: #fff;
            background: linear-gradient(135deg, var(--beautician-rose), var(--beautician-plum));
            border-radius: 12px;
            font-weight: 700;
            box-shadow: 0 7px 18px rgba(111, 41, 72, .2);
        }

        .beautician-registration__section-heading h3 { margin-bottom: 2px; color: var(--beautician-ink); font-size: 19px; }
        .beautician-registration__section-heading p { color: var(--beautician-muted); font-size: 13px; }

        .beautician-registration__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
        .beautician-registration__wide { grid-column: 1 / -1; }
        .beautician-registration .form-group { min-width: 0; margin: 0; }
        .beautician-registration fieldset.form-group { padding: 0; border: 0; }
        .beautician-registration legend.input-label { float: none; width: auto; padding: 0; border: 0; }
        .beautician-registration .input-label { display: block; margin-bottom: 8px; color: #413648; font-size: 13px; font-weight: 600; }
        .beautician-registration .input-label > span { color: var(--beautician-rose); }

        .beautician-registration .form-control,
        .beautician-registration select.form-control {
            width: 100%;
            height: 52px;
            padding: 0 15px;
            color: var(--beautician-ink);
            background-color: #fcfafb;
            border: 1px solid #ded3d8;
            border-radius: 12px;
            outline: 0;
            box-shadow: none;
            transition: .2s ease;
        }

        .beautician-registration .form-control:focus {
            background: #fff;
            border-color: var(--beautician-rose);
            box-shadow: 0 0 0 4px rgba(216, 95, 136, .1);
        }

        .beautician-registration__branches { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }

        .beautician-registration__branch {
            position: relative;
            display: flex;
            min-height: 54px;
            padding: 12px 14px;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            color: #554a59;
            background: #fcfafb;
            border: 1px solid #ded3d8;
            border-radius: 12px;
            transition: .2s ease;
        }

        .beautician-registration__branch:hover { border-color: #d8a1b4; transform: translateY(-1px); }
        .beautician-registration__branch:has(input:checked) { color: var(--beautician-plum); background: var(--beautician-blush); border-color: var(--beautician-rose); box-shadow: 0 0 0 3px rgba(216, 95, 136, .08); }
        .beautician-registration__branch input { width: 17px; height: 17px; accent-color: var(--beautician-rose); }

        .beautician-registration__privacy {
            display: flex;
            margin: 26px 0 20px;
            align-items: flex-start;
            gap: 11px;
            color: #5d5361;
            line-height: 1.55;
        }

        .beautician-registration__privacy input { width: 18px; height: 18px; margin-top: 2px; accent-color: var(--beautician-rose); }
        .beautician-registration__privacy a { color: var(--beautician-plum); font-weight: 600; }

        .beautician-registration__actions { display: grid; gap: 14px; text-align: center; }
        .beautician-registration__actions .btn {
            display: inline-flex;
            width: 100%;
            min-height: 56px;
            padding: 13px 22px;
            align-items: center;
            justify-content: center;
            gap: 11px;
            color: #fff;
            background: linear-gradient(110deg, var(--beautician-plum), var(--beautician-rose));
            border: 0;
            border-radius: 14px;
            box-shadow: 0 14px 30px rgba(111, 41, 72, .22);
            font-weight: 700;
            transition: .2s ease;
        }

        .beautician-registration__actions .btn:hover { color: #fff; box-shadow: 0 17px 34px rgba(111, 41, 72, .3); transform: translateY(-2px); }
        .beautician-registration__actions > a { color: var(--beautician-plum); font-size: 14px; font-weight: 600; }
        .beautician-registration .help-block { display: block; margin-top: 7px; font-size: 12px; }

        @media (max-width: 1180px) {
            .beautician-registration__shell { grid-template-columns: 38% minmax(0, 1fr); }
            .beautician-registration__story { padding: 42px 34px; }
            .beautician-registration__story h1 { font-size: 44px; }
            .beautician-registration__story-footer { flex-direction: column; gap: 10px; }
            .beautician-registration__main { padding-right: 36px; padding-left: 36px; }
        }

        @media (max-width: 900px) {
            .beautician-registration__shell { display: block; }
            .beautician-registration__story { position: relative; height: auto; min-height: 0; padding: 38px 30px 42px; }
            .beautician-registration__story-content { margin-top: 46px; }
            .beautician-registration__story h1 { max-width: 650px; font-size: 44px; }
            .beautician-registration__benefits { grid-template-columns: repeat(3, 1fr); }
            .beautician-registration__benefit { padding: 12px; align-items: flex-start; }
            .beautician-registration__story-footer { display: none; }
            .beautician-registration__main { padding: 36px 24px 60px; }
            .beautician-registration__topbar { margin-bottom: 32px; }
        }

        @media (max-width: 640px) {
            .beautician-registration__story { padding: 26px 20px 32px; }
            .beautician-registration__brand-mark { width: 42px; height: 42px; }
            .beautician-registration__story-content { margin-top: 38px; }
            .beautician-registration__story h1 { margin-bottom: 14px; font-size: 36px; }
            .beautician-registration__story-lead { font-size: 15px; line-height: 1.6; }
            .beautician-registration__benefits { grid-template-columns: 1fr; margin-top: 26px; gap: 8px; }
            .beautician-registration__benefit { max-width: none; align-items: center; }
            .beautician-registration__main { padding: 28px 14px 48px; }
            .beautician-registration__topbar { margin-bottom: 28px; justify-content: space-between; }
            .beautician-registration__form-header h2 { font-size: 30px; }
            .beautician-registration__section { padding: 21px 16px; border-radius: 17px; }
            .beautician-registration__grid,
            .beautician-registration__branches { grid-template-columns: 1fr; }
            .beautician-registration__wide { grid-column: auto; }
        }
    </style>
@endpush

@section('content')
    <main class="beautician-registration">
        <div class="beautician-registration__shell">
            <aside class="beautician-registration__story">
                <a href="{{ route('home') }}" class="beautician-registration__brand">
                    <span class="beautician-registration__brand-mark" aria-hidden="true">ISL</span>
                    <strong>{{ setting('store_name') }}</strong>
                </a>

                <div class="beautician-registration__story-content">
                    <span class="beautician-registration__eyebrow">
                        {{ trans('beautician::beauticians.self_registration.hero_eyebrow') }}
                    </span>
                    <h1>{{ trans('beautician::beauticians.self_registration.hero_title') }}</h1>
                    <p class="beautician-registration__story-lead">
                        {{ trans('beautician::beauticians.self_registration.hero_intro') }}
                    </p>

                    <div class="beautician-registration__benefits">
                        <div class="beautician-registration__benefit">
                            <span class="beautician-registration__benefit-icon">✓</span>
                            <span>{{ trans('beautician::beauticians.self_registration.benefit_profile') }}</span>
                        </div>
                        <div class="beautician-registration__benefit">
                            <span class="beautician-registration__benefit-icon">✓</span>
                            <span>{{ trans('beautician::beauticians.self_registration.benefit_schedule') }}</span>
                        </div>
                        <div class="beautician-registration__benefit">
                            <span class="beautician-registration__benefit-icon">✓</span>
                            <span>{{ trans('beautician::beauticians.self_registration.benefit_secure') }}</span>
                        </div>
                    </div>
                </div>

                <div class="beautician-registration__story-footer" aria-label="{{ trans('beautician::beauticians.self_registration.process') }}">
                    <span class="beautician-registration__story-step"><b>1</b> {{ trans('beautician::beauticians.self_registration.process_register') }}</span>
                    <span class="beautician-registration__story-step"><b>2</b> {{ trans('beautician::beauticians.self_registration.process_review') }}</span>
                    <span class="beautician-registration__story-step"><b>3</b> {{ trans('beautician::beauticians.self_registration.process_activate') }}</span>
                </div>
            </aside>

            <section class="beautician-registration__main">
                <div class="beautician-registration__form-wrap">
                    <div class="beautician-registration__topbar">
                        @include('storefront::public.auth.partials.language_picker')
                        <a class="beautician-registration__login" href="{{ route('admin.login') }}">
                            {{ trans('beautician::beauticians.self_registration.sign_in_short') }} →
                        </a>
                    </div>

                    <header class="beautician-registration__form-header">
                        <span class="beautician-registration__form-eyebrow">
                            {{ trans('beautician::beauticians.self_registration.form_eyebrow') }}
                        </span>
                        <h2>{{ trans('beautician::beauticians.self_registration.title') }}</h2>
                        <p>{{ trans('beautician::beauticians.self_registration.intro') }}</p>
                    </header>

                    @include('storefront::public.auth.partials.notification')

                    <div class="beautician-registration__notice">
                        <span class="beautician-registration__notice-icon">i</span>
                        <span>{{ trans('beautician::beauticians.self_registration.approval_notice') }}</span>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('beauticians.register.store') }}"
                        x-data="{ formSubmitting: false }"
                        @submit="formSubmitting = true"
                        @include('storefront::public.partials.google_recaptcha_form_attrs', ['action' => 'register'])
                    >
                        @csrf
                        @honeypot

                        <div class="beautician-registration__section">
                            <div class="beautician-registration__section-heading">
                                <span class="beautician-registration__section-number">1</span>
                                <div>
                                    <h3>{{ trans('beautician::beauticians.self_registration.identity_title') }}</h3>
                                    <p>{{ trans('beautician::beauticians.self_registration.identity_help') }}</p>
                                </div>
                            </div>

                            <div class="beautician-registration__grid">
                                <div class="form-group">
                                    <label class="input-label" for="beautician-first-name">{{ trans('user::auth.first_name') }} <span>*</span></label>
                                    <input class="form-control" id="beautician-first-name" name="first_name" value="{{ old('first_name') }}" autocomplete="given-name" required autofocus>
                                    {!! $errors->first('first_name', '<span class="help-block text-red">:message</span>') !!}
                                </div>

                                <div class="form-group">
                                    <label class="input-label" for="beautician-last-name">{{ trans('user::auth.last_name') }} <span>*</span></label>
                                    <input class="form-control" id="beautician-last-name" name="last_name" value="{{ old('last_name') }}" autocomplete="family-name" required>
                                    {!! $errors->first('last_name', '<span class="help-block text-red">:message</span>') !!}
                                </div>

                                <div class="form-group">
                                    <label class="input-label" for="beautician-email">{{ trans('user::auth.email') }} <span>*</span></label>
                                    <input class="form-control" id="beautician-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                                    {!! $errors->first('email', '<span class="help-block text-red">:message</span>') !!}
                                </div>

                                <div class="form-group">
                                    <label class="input-label" for="beautician-phone">{{ trans('user::auth.phone') }} <span>*</span></label>
                                    @include('storefront::public.partials.phone_input', [
                                        'name' => 'phone',
                                        'id' => 'beautician-phone',
                                        'value' => old('phone'),
                                        'required' => true,
                                        'placeholder' => trans('user::auth.phone'),
                                    ])
                                    {!! $errors->first('phone', '<span class="help-block text-red">:message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <div class="beautician-registration__section">
                            <div class="beautician-registration__section-heading">
                                <span class="beautician-registration__section-number">2</span>
                                <div>
                                    <h3>{{ trans('beautician::beauticians.self_registration.work_title') }}</h3>
                                    <p>{{ trans('beautician::beauticians.self_registration.work_help') }}</p>
                                </div>
                            </div>

                            <div class="beautician-registration__grid">
                                <div class="form-group beautician-registration__wide">
                                    <label class="input-label" for="beautician-job-title">{{ trans('beautician::beauticians.self_registration.job_title') }}</label>
                                    <select class="form-control" id="beautician-job-title" name="job_title">
                                        <option value="">{{ trans('beautician::beauticians.self_registration.choose_job_title') }}</option>
                                        @foreach ($jobTitles as $jobTitle)
                                            <option value="{{ $jobTitle }}" @selected(old('job_title') === $jobTitle)>{{ $jobTitle }}</option>
                                        @endforeach
                                    </select>
                                    {!! $errors->first('job_title', '<span class="help-block text-red">:message</span>') !!}
                                </div>

                                @if (is_module_enabled('SpaBranch'))
                                    <fieldset class="form-group beautician-registration__wide">
                                        <legend class="input-label">{{ trans('beautician::beauticians.self_registration.branches') }} <span>*</span></legend>
                                        @if ($spaBranches->isEmpty())
                                            <p>{{ trans('beautician::beauticians.self_registration.no_branches') }}</p>
                                        @else
                                            <div class="beautician-registration__branches">
                                                @foreach ($spaBranches as $branchId => $branchName)
                                                    <label class="beautician-registration__branch">
                                                        <input
                                                            type="checkbox"
                                                            name="spa_branches[]"
                                                            value="{{ $branchId }}"
                                                            @checked(in_array((int) $branchId, array_map('intval', (array) old('spa_branches', [])), true))
                                                        >
                                                        <span>{{ $branchName }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                        {!! $errors->first('spa_branches', '<span class="help-block text-red">:message</span>') !!}
                                        {!! $errors->first('spa_branches.*', '<span class="help-block text-red">:message</span>') !!}
                                    </fieldset>
                                @endif
                            </div>
                        </div>

                        <div class="beautician-registration__section">
                            <div class="beautician-registration__section-heading">
                                <span class="beautician-registration__section-number">3</span>
                                <div>
                                    <h3>{{ trans('beautician::beauticians.self_registration.security_title') }}</h3>
                                    <p>{{ trans('beautician::beauticians.self_registration.security_help') }}</p>
                                </div>
                            </div>

                            <div class="beautician-registration__grid">
                                <div class="form-group">
                                    <label class="input-label" for="beautician-password">{{ trans('user::auth.password') }} <span>*</span></label>
                                    <div class="beautician-password-input">
                                        <input class="form-control" id="beautician-password" type="password" name="password" autocomplete="new-password" required>
                                        <i class="fa fa-eye beautician-password-toggle" style="cursor: pointer; color: #6f2948; right: 12px;" aria-hidden="true"></i>
                                    </div>
                                    {!! $errors->first('password', '<span class="help-block text-red">:message</span>') !!}
                                </div>

                                <div class="form-group">
                                    <label class="input-label" for="beautician-password-confirmation">{{ trans('user::auth.confirm_password') }} <span>*</span></label>
                                    <div class="beautician-password-input">
                                        <input class="form-control" id="beautician-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                                        <i class="fa fa-eye beautician-password-toggle" style="cursor: pointer; color: #6f2948; right: 12px;" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @include('storefront::public.partials.google_recaptcha')

                        <label class="beautician-registration__privacy" for="beautician-privacy-policy">
                            <input type="hidden" name="privacy_policy" value="0">
                            <input id="beautician-privacy-policy" type="checkbox" name="privacy_policy" value="1" @checked(old('privacy_policy')) required>
                            <span>
                                {{ trans('user::auth.i_agree_to_the') }}
                                <a href="{{ $privacyPageUrl }}" target="_blank" rel="noopener">{{ trans('user::auth.privacy_policy') }}</a>
                            </span>
                        </label>
                        {!! $errors->first('privacy_policy', '<span class="help-block text-red">:message</span>') !!}

                        <div class="beautician-registration__actions">
                            <button type="submit" class="btn" :class="formSubmitting ? 'btn-loading' : ''" :disabled="formSubmitting">
                                <span>{{ trans('beautician::beauticians.self_registration.submit') }}</span>
                                <span aria-hidden="true">→</span>
                            </button>
                            <a href="{{ route('admin.login') }}">{{ trans('beautician::beauticians.self_registration.already_registered') }}</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    @include('storefront::public.partials.google_recaptcha_script')
    <style>
        .beautician-password-input {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .beautician-password-input .form-control {
            padding-right: 40px !important;
        }
        
        .beautician-password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: auto;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for all form groups to be fully loaded
            setTimeout(() => {
                const toggleButtons = document.querySelectorAll('.beautician-password-toggle');
                
                toggleButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        // Find the password input within the same container using closest()
                        const input = this.closest('.beautician-password-input').querySelector('input');
                        if (!input) return;
                        
                        // Toggle password visibility
                        const isPassword = input.type === 'password';
                        input.type = isPassword ? 'text' : 'password';
                        
                        // Update icon states
                        this.classList.toggle('fa-eye');
                        this.classList.toggle('fa-eye-slash');
                    });
                });
            }, 200); // Small delay to ensure elements are rendered
        });
    </script>
@endpush
