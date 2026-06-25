@php
    use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;

    $statuses = GoogleSheetsStatusConfig::statuses();
@endphp

<div class="google-sheets-status-tabs">
    <div class="table-responsive gs-table-wrap">
        <table class="table gs-table google-sheets-status-tabs__table">
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
                        $isEnabled = (bool) old($enabledKey, setting($enabledKey, GoogleSheetsStatusConfig::defaults()[$status]['enabled'] ?? false));
                    @endphp
                    <tr class="google-sheets-status-tabs__row {{ $isEnabled ? 'is-enabled' : '' }}">
                        <td class="google-sheets-status-tabs__status">
                            <span class="gs-status-pill {{ $isEnabled ? 'gs-status-pill--on' : 'gs-status-pill--off' }}">
                                {{ $label }}
                            </span>
                        </td>
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

    <p class="help-block text-muted gs-settings__field-hint">{{ trans('setting::settings.form.google_sheets_status_tabs_help') }}</p>

    <div class="google-sheets-sync-all-wrap gs-action-card">
        <div class="gs-action-card__head">
            <div>
                <h6 class="gs-action-card__title">{{ trans('setting::settings.form.google_sheets_sync_all') }}</h6>
                <p class="gs-action-card__desc">{{ trans('setting::settings.form.google_sheets_sync_all_help') }}</p>
            </div>
            <button
                type="button"
                class="btn btn-primary"
                id="google-sheets-sync-all-btn"
                data-sync-url="{{ route('admin.settings.google_sheets.sync_all_chunk') }}"
                data-count-url="{{ route('admin.settings.google_sheets.sync_all_count') }}"
                data-chunk-size="25"
                data-syncing-text="{{ trans('setting::settings.form.google_sheets_sync_all_running') }}"
                data-confirm-text="{{ trans('setting::settings.form.google_sheets_sync_all_confirm') }}"
            >
                <i class="fa fa-refresh" aria-hidden="true"></i>
                {{ trans('setting::settings.form.google_sheets_sync_all') }}
            </button>
        </div>

        <div id="google-sheets-sync-all-progress" class="google-sheets-sync-all-progress hide" aria-hidden="true">
            <div class="progress">
                <div
                    id="google-sheets-sync-all-progress-bar"
                    class="progress-bar progress-bar-striped active"
                    role="progressbar"
                    style="width: 0%"
                >
                    <span id="google-sheets-sync-all-progress-label">0%</span>
                </div>
            </div>
        </div>
        <div id="google-sheets-sync-all-result" class="google-sheets-test-result hide" role="status" aria-live="polite"></div>
    </div>
</div>
