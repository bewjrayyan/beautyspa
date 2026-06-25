@php
    $showTitle = $showTitle ?? true;
@endphp

<div class="google-sheets-alerts">
    @if ($showTitle)
        <h4 class="section-title">{{ trans('setting::settings.form.google_sheets_sync_alert_title') }}</h4>
        <p class="help-block text-muted gs-settings__field-hint">{{ trans('setting::settings.form.google_sheets_sync_alert_intro') }}</p>
    @endif

    <div class="gs-alert-items">
        @component('setting::admin.settings.partials.wa-notification-item', [
            'enabledName' => 'google_sheets_sync_alert_enabled',
            'enabledLabel' => trans('setting::settings.form.google_sheets_sync_alert_enable'),
            'hint' => null,
        ])
        @endcomponent

        @component('setting::admin.settings.partials.wa-notification-item', [
            'enabledName' => 'google_sheets_sync_alert_whatsapp_enabled',
            'enabledLabel' => trans('setting::settings.form.google_sheets_sync_alert_whatsapp_enable'),
            'hint' => trans('setting::settings.form.google_sheets_sync_alert_help'),
        ])
        @endcomponent
    </div>
</div>
