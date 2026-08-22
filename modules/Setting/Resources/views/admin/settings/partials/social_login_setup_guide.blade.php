@php
    $provider = $provider ?? 'google';
    $callbackUrl = route('login.callback', ['provider' => $provider]);
    $redirectUrl = route('login.redirect', ['provider' => $provider]);

    $links = $provider === 'google'
        ? [
            'console' => 'https://console.cloud.google.com/',
            'consent' => 'https://console.cloud.google.com/apis/credentials/consent',
            'credentials' => 'https://console.cloud.google.com/apis/credentials',
            'docs' => 'https://developers.google.com/identity/protocols/oauth2/web-server',
        ]
        : [
            'developers' => 'https://developers.facebook.com/apps/',
            'docs' => 'https://developers.facebook.com/docs/facebook-login/web',
            'login_settings' => 'https://developers.facebook.com/docs/facebook-login/guides/advanced/manual-flow#login',
        ];
@endphp

<div class="social-login-callback">
    <div class="social-login-callback__meta">
        <span class="social-login-callback__label">
            {{ trans("setting::settings.form.{$provider}_login_callback_title") }}
        </span>
        <span class="social-login-callback__hint">
            {{ trans("setting::settings.form.{$provider}_login_callback_hint") }}
        </span>
    </div>

    <div class="social-login-callback__row">
        <code class="social-login-callback__url" id="{{ $provider }}-login-callback-url">{{ $callbackUrl }}</code>
        <button
            type="button"
            class="btn btn-primary btn-sm social-login-callback__copy"
            data-copy-target="#{{ $provider }}-login-callback-url"
            data-copied-label="{{ e(trans('setting::settings.form.social_login_copied')) }}"
        >
            <i class="fa fa-copy" aria-hidden="true"></i>
            <span>{{ trans('setting::settings.form.social_login_copy_callback') }}</span>
        </button>
    </div>
</div>

<details class="social-login-help">
    <summary class="social-login-help__summary">
        <span class="social-login-help__summary-main">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            {{ trans("setting::settings.form.{$provider}_login_setup_title") }}
        </span>
        <span
            class="social-login-help__summary-hint"
            data-closed-label="{{ e(trans('setting::settings.form.social_login_help_toggle')) }}"
            data-open-label="{{ e(trans('setting::settings.form.social_login_help_toggle_open')) }}"
        >
            {{ trans('setting::settings.form.social_login_help_toggle') }}
        </span>
    </summary>

    <div class="social-login-help__body">
        <p class="social-login-help__intro">
            {{ trans("setting::settings.form.{$provider}_login_setup_intro") }}
        </p>

        <ol class="social-login-help__steps">
            @if ($provider === 'google')
                <li>{!! trans('setting::settings.form.google_login_setup_step_1', $links) !!}</li>
                <li>{!! trans('setting::settings.form.google_login_setup_step_2', $links) !!}</li>
                <li>{!! trans('setting::settings.form.google_login_setup_step_3', array_merge($links, ['callback' => $callbackUrl])) !!}</li>
                <li>{!! trans('setting::settings.form.google_login_setup_step_4', $links) !!}</li>
                <li>{{ trans('setting::settings.form.google_login_setup_step_5') }}</li>
                <li>{!! trans('setting::settings.form.google_login_setup_step_6', ['redirect' => $redirectUrl]) !!}</li>
            @else
                <li>{!! trans('setting::settings.form.facebook_login_setup_step_1', $links) !!}</li>
                <li>{!! trans('setting::settings.form.facebook_login_setup_step_2', $links) !!}</li>
                <li>{!! trans('setting::settings.form.facebook_login_setup_step_3', array_merge($links, ['callback' => $callbackUrl])) !!}</li>
                <li>{!! trans('setting::settings.form.facebook_login_setup_step_4', $links) !!}</li>
                <li>{{ trans('setting::settings.form.facebook_login_setup_step_5') }}</li>
                <li>{!! trans('setting::settings.form.facebook_login_setup_step_6', ['redirect' => $redirectUrl]) !!}</li>
            @endif
        </ol>

        <p class="social-login-help__security">
            <i class="fa fa-lock" aria-hidden="true"></i>
            {{ trans("setting::settings.form.{$provider}_login_setup_security") }}
        </p>
    </div>
</details>
