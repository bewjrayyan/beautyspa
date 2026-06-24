@if (($showGoogleSheetsStats ?? false) && ($googleSheetsFailedCount ?? 0) > 0)
    @hasAccess('admin.settings.edit')
        @include('admin::partials.fc_saas_stat', [
            'variant' => 'rose',
            'icon' => 'fa-table',
            'label' => trans('setting::settings.form.google_sheets_dashboard_failed_label'),
            'value' => number_format($googleSheetsFailedCount),
            'hint' => trans('setting::settings.form.google_sheets_dashboard_failed_hint'),
            'url' => $googleSheetsFailedUrl ?? null,
            'cta' => trans('setting::settings.form.google_sheets_dashboard_failed_cta'),
        ])
    @endHasAccess
@endif
