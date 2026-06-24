@php
    use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;

    $statuses = GoogleSheetsStatusConfig::statuses();
@endphp

<div class="google-sheets-status-tabs box-content clearfix">
    <h4 class="section-title">{{ trans('setting::settings.form.google_sheets_status_tabs_title') }}</h4>
    <p class="help-block text-muted">{{ trans('setting::settings.form.google_sheets_status_tabs_intro') }}</p>

    <div class="table-responsive">
        <table class="table table-striped google-sheets-status-tabs__table">
            <thead>
                <tr>
                    <th>{{ trans('setting::settings.form.google_sheets_status_column') }}</th>
                    <th class="text-center">{{ trans('setting::settings.form.google_sheets_sync_column') }}</th>
                    <th>{{ trans('setting::settings.form.google_sheets_tab_column') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statuses as $status => $label)
                    @php
                        $enabledKey = GoogleSheetsStatusConfig::enabledKey($status);
                        $tabKey = GoogleSheetsStatusConfig::tabKey($status);
                        $defaultTab = GoogleSheetsStatusConfig::defaults()[$status]['tab'] ?? 'Orders';
                    @endphp
                    <tr>
                        <td class="google-sheets-status-tabs__status">{{ $label }}</td>
                        <td class="text-center google-sheets-status-tabs__enabled">
                            {{ Form::checkbox($enabledKey, ' ', trans('setting::settings.form.google_sheets_sync_status'), $errors, $settings, ['labelCol' => 0]) }}
                        </td>
                        <td class="google-sheets-status-tabs__tab">
                            {{ Form::text($tabKey, ' ', $errors, $settings, [
                                'placeholder' => $defaultTab,
                                'labelCol' => 0,
                            ]) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="help-block text-muted">{{ trans('setting::settings.form.google_sheets_status_tabs_help') }}</p>

    <div class="google-sheets-sync-all-wrap">
        <button
            type="button"
            class="btn btn-primary"
            id="google-sheets-sync-all-btn"
            data-sync-url="{{ route('admin.settings.google_sheets.sync_all') }}"
            data-syncing-text="{{ trans('setting::settings.form.google_sheets_sync_all_running') }}"
            data-confirm-text="{{ trans('setting::settings.form.google_sheets_sync_all_confirm') }}"
        >
            <i class="fa fa-refresh" aria-hidden="true"></i>
            {{ trans('setting::settings.form.google_sheets_sync_all') }}
        </button>
        <p class="help-block text-muted">{{ trans('setting::settings.form.google_sheets_sync_all_help') }}</p>
        <div id="google-sheets-sync-all-result" class="google-sheets-test-result hide" role="status" aria-live="polite"></div>
    </div>
</div>
