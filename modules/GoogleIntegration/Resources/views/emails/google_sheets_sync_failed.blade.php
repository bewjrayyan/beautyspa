<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ trans('setting::messages.google_sheets_alert_email_subject', ['order' => $order->id]) }}</title>
</head>
<body>
    <p>{{ trans('setting::messages.google_sheets_alert_email_intro', ['order' => $order->id]) }}</p>
    <p><strong>{{ trans('setting::settings.form.google_sheets_sync_log_trigger') }}:</strong> {{ trans('setting::settings.form.google_sheets_sync_triggers.' . $trigger) }}</p>
    <p><strong>{{ trans('admin::admin.table.status') }}:</strong> {{ $order->status() }}</p>
    <p><strong>{{ trans('setting::messages.google_sheets_alert_email_error') }}:</strong> {{ $errorMessage }}</p>
    <p>{{ trans('setting::messages.google_sheets_alert_email_footer') }}</p>
</body>
</html>
