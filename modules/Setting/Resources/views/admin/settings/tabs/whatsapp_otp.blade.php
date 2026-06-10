@component('setting::admin.settings.partials.settings-wrap')
    <div class="st-enable-card">
        {{ Form::checkbox('whatsapp_otp_login_enabled', trans('setting::attributes.whatsapp_otp_login_enabled'), trans('setting::settings.form.enable_whatsapp_otp_login'), $errors, $settings) }}
    </div>

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-sign-in',
        'title' => trans('setting::settings.sections.display'),
        'class' => 'st-section--compact',
    ])
        {{ Form::text('translatable[whatsapp_otp_login_label]', trans('setting::attributes.translatable.whatsapp_otp_login_label'), $errors, $settings) }}
    @endcomponent

    <div class="{{ old('whatsapp_otp_login_enabled', array_get($settings, 'whatsapp_otp_login_enabled')) ? '' : 'hide' }}" id="whatsapp-otp-fields">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-clock-o',
            'title' => trans('setting::settings.sections.otp_timing'),
        ])
            @component('setting::admin.settings.partials.fields-grid')
                @slot('left')
                    {{ Form::number('whatsapp_otp_expiry_minutes', trans('setting::attributes.whatsapp_otp_expiry_minutes'), $errors, $settings, [
                        'min' => 1,
                        'max' => 30,
                        'value' => old('whatsapp_otp_expiry_minutes', array_get($settings, 'whatsapp_otp_expiry_minutes', 5)),
                    ]) }}
                @endslot
                @slot('right')
                    <p class="help-block text-muted st-fields-grid__help">{{ trans('setting::settings.form.whatsapp_otp_uses_onesender_help') }}</p>
                @endslot
            @endcomponent
        @endcomponent
    </div>
@endcomponent
