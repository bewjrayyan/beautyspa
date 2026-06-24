<?php

namespace Modules\GoogleIntegration\Services;

use Modules\GoogleIntegration\Entities\GoogleSheetsSyncLog;
use Modules\Order\Entities\Order;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GoogleSheetsSyncLogExporter
{
    public function download(): StreamedResponse
    {
        $filename = 'google-sheets-sync-log-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                trans('setting::settings.form.google_sheets_sync_log_time'),
                trans('setting::settings.form.google_sheets_sync_log_order'),
                trans('setting::settings.form.google_sheets_sync_log_trigger'),
                trans('setting::settings.form.google_sheets_sync_log_status'),
                trans('setting::settings.form.google_sheets_sync_log_tab'),
                trans('setting::settings.form.google_sheets_sync_log_message'),
            ]);

            GoogleSheetsSyncLog::query()
                ->latest('id')
                ->limit(500)
                ->cursor()
                ->each(function (GoogleSheetsSyncLog $log) use ($handle) {
                    fputcsv($handle, [
                        $log->created_at?->format('Y-m-d H:i:s'),
                        $log->order_id,
                        trans('setting::settings.form.google_sheets_sync_triggers.' . $log->trigger),
                        trans('setting::settings.form.google_sheets_sync_log_statuses.' . $log->status),
                        $log->sheet_tab,
                        $log->message,
                    ]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }


    public static function failedOrdersCount(): int
    {
        if (! GoogleSheetsService::isEnabled()) {
            return 0;
        }

        return Order::query()->whereNotNull('google_sheets_sync_error')->count();
    }
}
