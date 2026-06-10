@component('setting::admin.settings.partials.settings-wrap')
    <div class="st-enable-card">
        {{ Form::checkbox('google_recaptcha_enabled', trans('setting::attributes.google_recaptcha_enabled'), trans('setting::settings.form.enable_google_recaptcha'), $errors, $settings) }}
    </div>

    <div class="{{ old('google_recaptcha_enabled', array_get($settings, 'google_recaptcha_enabled')) ? '' : 'hide' }}" id="google-recaptcha-fields">
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
        @endcomponent
    </div>
@endcomponent
