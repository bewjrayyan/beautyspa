@php
    $provider = $provider ?? 'facebook';
    $enableLabel = $enableLabel ?? trans("setting::settings.form.enable_{$provider}_login");
    $fieldsId = $fieldsId ?? "{$provider}-login-fields";
@endphp

@component('setting::admin.settings.partials.settings-wrap')
    <div class="social-login-panel">
        <div class="st-enable-card">
            {{ Form::checkbox("{$provider}_login_enabled", trans("setting::attributes.{$provider}_login_enabled"), $enableLabel, $errors, $settings, ['labelCol' => 0]) }}
        </div>

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-sign-in',
            'title' => trans('setting::settings.sections.social_login_display'),
            'class' => 'st-section--compact',
        ])
            {{ Form::text("translatable[{$provider}_login_label]", trans("setting::attributes.translatable.{$provider}_login_label"), $errors, $settings) }}
        @endcomponent

        <div id="{{ $fieldsId }}">
            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-key',
                'title' => trans('setting::settings.sections.social_login_credentials'),
            ])
                @component('setting::admin.settings.partials.fields-grid')
                    @slot('left')
                        @if ($provider === 'google')
                            {{ Form::text('google_login_client_id', trans('setting::attributes.google_login_client_id'), $errors, $settings) }}
                        @else
                            {{ Form::text('facebook_login_app_id', trans('setting::attributes.facebook_login_app_id'), $errors, $settings) }}
                        @endif
                    @endslot
                    @slot('right')
                        @if ($provider === 'google')
                            {{ Form::password('google_login_client_secret', trans('setting::attributes.google_login_client_secret'), $errors, $settings) }}
                        @else
                            {{ Form::password('facebook_login_app_secret', trans('setting::attributes.facebook_login_app_secret'), $errors, $settings) }}
                        @endif
                    @endslot
                @endcomponent
            @endcomponent
        </div>

        @include('setting::admin.settings.partials.social_login_setup_guide', ['provider' => $provider])
    </div>
@endcomponent
