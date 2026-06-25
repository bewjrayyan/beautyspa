@component('setting::admin.settings.partials.settings-wrap')
    @php
        $recaptchaTypeOptions = [
            'v2' => trans('setting::settings.form.google_recaptcha_type_v2'),
            'v3' => trans('setting::settings.form.google_recaptcha_type_v3'),
        ];
    @endphp

    <div class="st-enable-card">
        {{ Form::checkbox('google_recaptcha_enabled', trans('setting::attributes.google_recaptcha_enabled'), trans('setting::settings.form.enable_google_recaptcha'), $errors, $settings) }}
    </div>

    <div class="{{ old('google_recaptcha_enabled', array_get($settings, 'google_recaptcha_enabled')) ? '' : 'hide' }}" id="google-recaptcha-fields">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-sliders',
            'title' => trans('setting::settings.form.google_recaptcha_type'),
        ])
            {{ Form::select('google_recaptcha_type', trans('setting::attributes.google_recaptcha_type'), $errors, $recaptchaTypeOptions, $settings, ['required' => true]) }}

            <p class="help-block text-muted gs-settings__field-hint">{{ trans('setting::settings.form.google_recaptcha_type_help') }}</p>

            <div class="{{ old('google_recaptcha_type', array_get($settings, 'google_recaptcha_type', 'v2')) === 'v3' ? '' : 'hide' }}" id="google-recaptcha-v3-fields">
                {{ Form::text('google_recaptcha_v3_score_threshold', trans('setting::attributes.google_recaptcha_v3_score_threshold'), $errors, $settings, [
                    'required' => true,
                    'placeholder' => '0.5',
                ]) }}
                <p class="help-block text-muted gs-settings__field-hint">{{ trans('setting::settings.form.google_recaptcha_v3_score_help') }}</p>
            </div>
        @endcomponent

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-key',
            'title' => trans('setting::settings.sections.credentials'),
        ])
            @component('setting::admin.settings.partials.fields-grid')
                @slot('left')
                    {{ Form::text('google_recaptcha_site_key', trans('setting::attributes.google_recaptcha_site_key'), $errors, $settings, ['required' => true]) }}
                @endslot
                @slot('right')
                    {{ Form::password('google_recaptcha_secret_key', trans('setting::attributes.google_recaptcha_secret_key'), $errors, $settings, ['required' => true]) }}
                @endslot
            @endcomponent

            <p class="help-block text-muted gs-settings__field-hint">{{ trans('setting::settings.form.google_recaptcha_keys_help') }}</p>
        @endcomponent
    </div>
@endcomponent
