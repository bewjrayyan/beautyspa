@php
    use Modules\GoogleIntegration\Services\GoogleSheetsSyncLogExporter;
    use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;

    $spreadsheetId = array_get($settings, 'google_spreadsheet_id');
    $sheetGid = array_get($settings, 'google_sheet_gid');
    $spreadsheetUrl = $spreadsheetId
        ? \Modules\GoogleIntegration\Support\GoogleSpreadsheetUrlParser::toUrl($spreadsheetId, $sheetGid)
        : '';
    $sheetsEnabled = (bool) old('google_sheets_enabled', array_get($settings, 'google_sheets_enabled'));
    $failedSyncCount = $sheetsEnabled ? GoogleSheetsSyncLogExporter::failedOrdersCount() : 0;
    $enabledStatusCount = count(GoogleSheetsStatusConfig::enabledStatuses());
@endphp

<div class="st-tab st-tab--google-sheets settings-form" data-google-sheets-settings>
    <p class="st-tab__lead">{{ trans('setting::settings.tab_leads.google_sheets') }}</p>

    @include('setting::admin.settings.partials.google_sheets_setup_guide')

    <div class="gs-settings">
        <div class="gs-settings__intro-grid">
            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-key',
                'title' => trans('setting::settings.form.google_excel_document_settings'),
                'description' => trans('setting::settings.form.google_excel_document_intro'),
                'class' => 'gs-settings__section gs-settings__section--credentials',
            ])
                {{ Form::textarea('google_service_account_json', trans('setting::attributes.google_service_account_json'), $errors, $settings, [
                    'rows' => 10,
                    'placeholder' => '{ "type": "service_account", "client_email": "...", ... }',
                    'class' => 'gs-json-textarea',
                ]) }}
                <p class="help-block text-muted gs-settings__field-hint">{{ trans('setting::settings.form.google_service_account_help') }}</p>
            @endcomponent

            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-table',
                'title' => trans('setting::settings.form.google_sales_sheet_settings'),
                'description' => trans('setting::settings.form.google_sheets_spreadsheet_intro'),
                'class' => 'gs-settings__section gs-settings__section--spreadsheet',
            ])
                <div class="st-enable-card">
                    {{ Form::checkbox('google_sheets_enabled', trans('setting::attributes.google_sheets_enabled'), trans('setting::settings.form.enable_google_sheets_sync'), $errors, $settings) }}
                </div>

                <div class="{{ $sheetsEnabled ? '' : 'hide' }}" id="google-sheets-fields">
                    {{ Form::text('google_spreadsheet_id', trans('setting::attributes.google_spreadsheet_url'), $errors, $settings, [
                        'value' => old('google_spreadsheet_id', $spreadsheetUrl),
                        'placeholder' => 'https://docs.google.com/spreadsheets/d/.../edit?gid=...',
                    ]) }}
                    <p class="help-block text-muted gs-settings__field-hint">{{ trans('setting::settings.form.google_spreadsheet_url_help') }}</p>

                    <div class="st-notice gs-settings__notice">
                        <i class="fa fa-share-alt" aria-hidden="true"></i>
                        <p>{{ trans('setting::settings.form.google_sheets_share_help') }}</p>
                    </div>

                    <div class="gs-settings__actions">
                        <button
                            type="button"
                            class="btn btn-default"
                            id="google-sheets-test-btn"
                            data-test-url="{{ route('admin.settings.google_sheets.test_connection') }}"
                            data-testing-text="{{ trans('setting::settings.form.google_sheets_test_connection_running') }}"
                        >
                            <i class="fa fa-plug" aria-hidden="true"></i>
                            {{ trans('setting::settings.form.google_sheets_test_connection') }}
                        </button>
                        <p class="gs-settings__actions-hint">{{ trans('setting::settings.form.google_sheets_test_connection_help') }}</p>
                    </div>

                    <div id="google-sheets-test-result" class="google-sheets-test-result hide" role="status" aria-live="polite"></div>
                </div>
            @endcomponent
        </div>

        <div class="gs-settings__advanced {{ $sheetsEnabled ? '' : 'hide' }}" id="google-sheets-advanced">
            <div class="gs-settings__stats" aria-label="{{ trans('setting::settings.form.google_sheets_stats_label') }}">
                <div class="gs-settings__stat">
                    <span class="gs-settings__stat-value">{{ $enabledStatusCount }}</span>
                    <span class="gs-settings__stat-label">{{ trans('setting::settings.form.google_sheets_stats_statuses') }}</span>
                </div>
                <div class="gs-settings__stat {{ $failedSyncCount > 0 ? 'gs-settings__stat--danger' : 'gs-settings__stat--success' }}">
                    <span class="gs-settings__stat-value">{{ number_format($failedSyncCount) }}</span>
                    <span class="gs-settings__stat-label">{{ trans('setting::settings.form.google_sheets_stats_failed') }}</span>
                </div>
                @if ($failedSyncCount > 0)
                    <a href="{{ route('admin.orders.index', ['google_sheets_failed' => 1]) }}" class="btn btn-default btn-sm gs-settings__stat-link">
                        <i class="fa fa-external-link" aria-hidden="true"></i>
                        {{ trans('setting::settings.form.google_sheets_dashboard_failed_cta') }}
                    </a>
                @endif
            </div>

            <div class="st-notice gs-settings__notice gs-settings__notice--queue">
                <i class="fa fa-clock-o" aria-hidden="true"></i>
                <p>{{ trans('setting::settings.form.google_sheets_queue_help') }}</p>
            </div>

            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-tags',
                'title' => trans('setting::settings.form.google_sheets_status_tabs_title'),
                'description' => trans('setting::settings.form.google_sheets_status_tabs_intro'),
                'class' => 'gs-settings__section',
            ])
                @include('setting::admin.settings.partials.google_sheets_status_tabs')
            @endcomponent

            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-columns',
                'title' => trans('setting::settings.form.google_sheets_columns_title'),
                'description' => trans('setting::settings.form.google_sheets_columns_intro'),
                'class' => 'gs-settings__section',
            ])
                @include('setting::admin.settings.partials.google_sheets_columns', ['showTitle' => false])
                <p class="help-block text-muted gs-settings__field-hint">{{ trans('setting::settings.form.google_sheets_columns_help') }}</p>
            @endcomponent

            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-sliders',
                'title' => trans('setting::settings.form.google_sheets_per_status_columns_title'),
                'description' => trans('setting::settings.form.google_sheets_per_status_columns_intro'),
                'class' => 'gs-settings__section',
            ])
                @include('setting::admin.settings.partials.google_sheets_per_status_columns', ['showTitle' => false])
            @endcomponent

            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-bell',
                'title' => trans('setting::settings.form.google_sheets_sync_alert_title'),
                'description' => trans('setting::settings.form.google_sheets_sync_alert_intro'),
                'class' => 'gs-settings__section gs-settings__section--alerts',
            ])
                @include('setting::admin.settings.partials.google_sheets_alerts', ['showTitle' => false])
            @endcomponent

            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-history',
                'title' => trans('setting::settings.form.google_sheets_sync_log_title'),
                'description' => trans('setting::settings.form.google_sheets_sync_log_intro'),
                'class' => 'gs-settings__section gs-settings__section--log',
            ])
                @include('googleintegration::admin.settings.partials.google_sheets_sync_log', ['showTitle' => false])
            @endcomponent
        </div>
    </div>
</div>
