@php
    use Modules\GoogleIntegration\Services\GoogleSheetsService;
    use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;

    $sheetsEnabled = GoogleSheetsService::isEnabled();
    $statusEnabled = GoogleSheetsStatusConfig::isStatusEnabled($order->status);
    $targetTab = GoogleSheetsStatusConfig::tabForStatus($order->status);
@endphp

@if ($sheetsEnabled && $statusEnabled)
    <div class="order-show__card order-show__card--google-sheets" id="order-google-sheets-sync">
        <div class="order-show__card-head">
            <h5><i class="fa fa-table" aria-hidden="true"></i> {{ trans('order::orders.google_sheets_sync_title') }}</h5>
        </div>

        <div class="order-show__google-sheets-body">
            <p class="order-show__google-sheets-tab">
                <span class="order-show__hint">{{ trans('order::orders.google_sheets_target_tab') }}</span>
                <strong>{{ $targetTab }}</strong>
            </p>

            <p class="order-show__google-sheets-status" id="order-google-sheets-status">
                @if ($order->google_sheets_sync_error)
                    <span class="badge order-show__status-badge order-show__status-badge--danger">
                        {{ trans('order::orders.google_sheets_sync_failed_badge') }}
                    </span>
                    <span class="order-show__hint order-show__google-sheets-error">
                        {{ $order->google_sheets_sync_error }}
                    </span>
                @elseif ($order->google_sheets_synced_at)
                    <span class="badge order-show__status-badge order-show__status-badge--success">
                        {{ trans('order::orders.google_sheets_synced') }}
                    </span>
                    <span class="order-show__hint">
                        {{ $order->google_sheets_synced_at->format('d M Y, H:i') }}
                        @if ($order->google_sheets_tab)
                            · {{ trans('order::orders.google_sheets_synced_tab', ['tab' => $order->google_sheets_tab]) }}
                        @endif
                    </span>
                @else
                    <span class="badge order-show__status-badge order-show__status-badge--warning">
                        {{ trans('order::orders.google_sheets_not_synced') }}
                    </span>
                    <span class="order-show__hint">{{ trans('order::orders.google_sheets_not_synced_help') }}</span>
                @endif
            </p>

            @if ($order->google_sheets_sync_attempted_at)
                <p class="order-show__hint order-show__google-sheets-attempt">
                    {{ trans('order::orders.google_sheets_last_attempt', [
                        'time' => $order->google_sheets_sync_attempted_at->format('d M Y, H:i'),
                    ]) }}
                </p>
            @endif

            <button
                type="button"
                class="btn btn-default btn-sm"
                id="order-google-sheets-sync-btn"
                data-order-id="{{ $order->id }}"
                data-sync-url="{{ route('admin.orders.google_sheets.sync', $order) }}"
            >
                <i class="fa fa-refresh" aria-hidden="true"></i>
                {{ trans('order::orders.google_sheets_sync_now') }}
            </button>
        </div>
    </div>
@endif
