@php
    $spreadsheetId = array_get($settings, 'google_spreadsheet_id');
    $sheetGid = array_get($settings, 'google_sheet_gid');
    $spreadsheetUrl = $spreadsheetId
        ? \Modules\GoogleIntegration\Support\GoogleSpreadsheetUrlParser::toUrl($spreadsheetId, $sheetGid)
        : '';
@endphp

@component('setting::admin.settings.partials.settings-wrap')
    <div class="st-fields-grid st-fields-grid--sections">
        <div class="st-fields-grid__col">
            <div class="box-content clearfix">
                <h4 class="section-title">{{ trans('setting::settings.form.google_excel_document_settings') }}</h4>
                <p class="help-block text-muted">{{ trans('setting::settings.form.google_excel_document_intro') }}</p>
                {{ Form::textarea('google_service_account_json', trans('setting::attributes.google_service_account_json'), $errors, $settings, [
                    'rows' => 12,
                    'placeholder' => '{ "type": "service_account", "client_email": "...", ... }',
                ]) }}
                <p class="help-block text-muted">{{ trans('setting::settings.form.google_service_account_help') }}</p>
            </div>
        </div>

        <div class="st-fields-grid__col">
            <div class="box-content clearfix">
                <h4 class="section-title">{{ trans('setting::settings.form.google_sales_sheet_settings') }}</h4>
                {{ Form::checkbox('google_sheets_enabled', trans('setting::attributes.google_sheets_enabled'), trans('setting::settings.form.enable_google_sheets_sync'), $errors, $settings) }}

                <div class="{{ old('google_sheets_enabled', array_get($settings, 'google_sheets_enabled')) ? '' : 'hide' }}" id="google-sheets-fields">
                    {{ Form::text('google_spreadsheet_id', trans('setting::attributes.google_spreadsheet_url'), $errors, $settings, [
                        'value' => old('google_spreadsheet_id', $spreadsheetUrl),
                        'placeholder' => 'https://docs.google.com/spreadsheets/d/.../edit?gid=...',
                    ]) }}
                    <p class="help-block text-muted">{{ trans('setting::settings.form.google_spreadsheet_url_help') }}</p>
                    {{ Form::text('google_sheet_name', trans('setting::attributes.google_sheet_name'), $errors, $settings, [
                        'placeholder' => trans('setting::settings.form.google_sheet_name_optional_placeholder'),
                    ]) }}
                    <p class="help-block text-muted">{{ trans('setting::settings.form.google_sheet_name_help') }}</p>
                    <p class="help-block text-muted">{{ trans('setting::settings.form.google_sheets_share_help') }}</p>
                </div>
            </div>
        </div>
    </div>
@endcomponent

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.querySelector('[name="google_sheets_enabled"]');
            const panel = document.getElementById('google-sheets-fields');

            if (!toggle || !panel) {
                return;
            }

            toggle.addEventListener('change', () => {
                panel.classList.toggle('hide', !toggle.checked);
            });
        });
    </script>
@endpush
