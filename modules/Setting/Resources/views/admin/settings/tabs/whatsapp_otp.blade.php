<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('whatsapp_otp_login_enabled', trans('setting::attributes.whatsapp_otp_login_enabled'), trans('setting::settings.form.enable_whatsapp_otp_login'), $errors, $settings) }}
        {{ Form::text('translatable[whatsapp_otp_login_label]', trans('setting::attributes.translatable.whatsapp_otp_login_label'), $errors, $settings) }}

        <div class="{{ old('whatsapp_otp_login_enabled', array_get($settings, 'whatsapp_otp_login_enabled')) ? '' : 'hide' }}" id="whatsapp-otp-fields">
            {{ Form::number('whatsapp_otp_expiry_minutes', trans('setting::attributes.whatsapp_otp_expiry_minutes'), $errors, $settings, [
                'min' => 1,
                'max' => 30,
                'value' => old('whatsapp_otp_expiry_minutes', array_get($settings, 'whatsapp_otp_expiry_minutes', 5)),
            ]) }}
            <p class="help-block text-muted">
                {{ trans('setting::settings.form.whatsapp_otp_uses_onesender_help') }}
            </p>
        </div>
    </div>
</div>
