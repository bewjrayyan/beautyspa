@php
    use Modules\GoogleIntegration\Entities\GoogleSheetsSyncLog;

    $showTitle = $showTitle ?? true;

    $logs = GoogleSheetsSyncLog::query()
        ->with('order:id')
        ->latest('id')
        ->limit(50)
        ->get();

    $successCount = $logs->where('status', 'success')->count();
    $failedCount = $logs->where('status', 'failed')->count();
@endphp

<div class="google-sheets-sync-log">
    @if ($showTitle)
        <div class="gs-subsection-head gs-subsection-head--actions">
            <div>
                <h4 class="section-title">{{ trans('setting::settings.form.google_sheets_sync_log_title') }}</h4>
                <p class="help-block text-muted gs-settings__field-hint">{{ trans('setting::settings.form.google_sheets_sync_log_intro') }}</p>
            </div>
            <a
                href="{{ route('admin.settings.google_sheets.export_logs') }}"
                class="btn btn-default btn-sm"
            >
                <i class="fa fa-download" aria-hidden="true"></i>
                {{ trans('setting::settings.form.google_sheets_sync_log_export') }}
            </a>
        </div>
    @else
        <div class="gs-log-toolbar">
            <div class="gs-log-toolbar__counts">
                <span class="gs-log-pill gs-log-pill--success">{{ trans('setting::settings.form.google_sheets_sync_log_statuses.success') }}: {{ $successCount }}</span>
                <span class="gs-log-pill gs-log-pill--failed">{{ trans('setting::settings.form.google_sheets_sync_log_statuses.failed') }}: {{ $failedCount }}</span>
            </div>
            <a
                href="{{ route('admin.settings.google_sheets.export_logs') }}"
                class="btn btn-default btn-sm"
            >
                <i class="fa fa-download" aria-hidden="true"></i>
                {{ trans('setting::settings.form.google_sheets_sync_log_export') }}
            </a>
        </div>
    @endif

    @if ($logs->isEmpty())
        <div class="gs-empty-state">
            <i class="fa fa-inbox gs-empty-state__icon" aria-hidden="true"></i>
            <p>{{ trans('setting::settings.form.google_sheets_sync_log_empty') }}</p>
        </div>
    @else
        <div class="table-responsive gs-table-wrap gs-table-wrap--log">
            <table class="table gs-table google-sheets-sync-log__table">
                <thead>
                    <tr>
                        <th>{{ trans('setting::settings.form.google_sheets_sync_log_time') }}</th>
                        <th>{{ trans('setting::settings.form.google_sheets_sync_log_order') }}</th>
                        <th>{{ trans('setting::settings.form.google_sheets_sync_log_trigger') }}</th>
                        <th>{{ trans('setting::settings.form.google_sheets_sync_log_status') }}</th>
                        <th>{{ trans('setting::settings.form.google_sheets_sync_log_tab') }}</th>
                        <th>{{ trans('setting::settings.form.google_sheets_sync_log_message') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr class="{{ $log->status === 'success' ? 'is-success' : 'is-failed' }}">
                            <td class="google-sheets-sync-log__time">{{ $log->created_at?->format('d M Y, H:i') }}</td>
                            <td>
                                @if ($log->order_id)
                                    <a href="{{ route('admin.orders.show', $log->order_id) }}" class="gs-log-order">#{{ $log->order_id }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="gs-log-trigger">{{ trans('setting::settings.form.google_sheets_sync_triggers.' . $log->trigger) }}</span>
                            </td>
                            <td>
                                <span class="gs-log-status gs-log-status--{{ $log->status }}">
                                    {{ trans('setting::settings.form.google_sheets_sync_log_statuses.' . $log->status) }}
                                </span>
                            </td>
                            <td>{{ $log->sheet_tab ?: '—' }}</td>
                            <td class="google-sheets-sync-log__message">{{ $log->message ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
