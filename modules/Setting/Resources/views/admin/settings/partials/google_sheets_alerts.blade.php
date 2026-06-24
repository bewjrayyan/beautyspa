<div class="google-sheets-alerts box-content clearfix">
    <h4 class="section-title">{{ trans('setting::settings.form.google_sheets_sync_alert_title') }}</h4>
    <p class="help-block text-muted">{{ trans('setting::settings.form.google_sheets_sync_alert_intro') }}</p>

    {{ Form::checkbox('google_sheets_sync_alert_enabled', trans('setting::attributes.google_sheets_sync_alert_enabled'), trans('setting::settings.form.google_sheets_sync_alert_enable'), $errors, $settings) }}
    {{ Form::checkbox('google_sheets_sync_alert_whatsapp_enabled', trans('setting::attributes.google_sheets_sync_alert_whatsapp_enabled'), trans('setting::settings.form.google_sheets_sync_alert_whatsapp_enable'), $errors, $settings) }}

    <p class="help-block text-muted">{{ trans('setting::settings.form.google_sheets_sync_alert_help') }}</p>
</div>
