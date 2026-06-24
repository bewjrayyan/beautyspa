@php
    use Modules\GoogleIntegration\Entities\GoogleSheetsSyncLog;

    $logs = GoogleSheetsSyncLog::query()
        ->with('order:id')
        ->latest('id')
        ->limit(50)
        ->get();
@endphp

<div class="google-sheets-sync-log box-content clearfix">
    <h4 class="section-title">{{ trans('setting::settings.form.google_sheets_sync_log_title') }}</h4>
    <p class="help-block text-muted">{{ trans('setting::settings.form.google_sheets_sync_log_intro') }}</p>

    @if ($logs->isEmpty())
        <p class="text-muted">{{ trans('setting::settings.form.google_sheets_sync_log_empty') }}</p>
    @else
        <div class="table-responsive">
            <table class="table table-striped google-sheets-sync-log__table">
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
                        <tr>
                            <td>{{ $log->created_at?->format('d M Y, H:i') }}</td>
                            <td>
                                @if ($log->order_id)
                                    <a href="{{ route('admin.orders.show', $log->order_id) }}">#{{ $log->order_id }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ trans('setting::settings.form.google_sheets_sync_triggers.' . $log->trigger) }}</td>
                            <td>
                                <span class="badge {{ $log->status === 'success' ? 'badge-success' : 'badge-danger' }}">
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
