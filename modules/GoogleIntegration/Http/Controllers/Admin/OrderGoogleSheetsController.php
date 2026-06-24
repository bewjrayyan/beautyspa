<?php

namespace Modules\GoogleIntegration\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\JsonResponse;
use Modules\GoogleIntegration\Services\GoogleSheetsService;
use Modules\GoogleIntegration\Services\OrderGoogleSyncService;
use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;
use Modules\Order\Entities\Order;

class OrderGoogleSheetsController
{
    public function sync(Order $order, OrderGoogleSyncService $sync): JsonResponse
    {
        if (! GoogleSheetsService::isEnabled()) {
            return response()->json([
                'message' => trans('order::messages.google_sheets_sync_disabled'),
            ], 422);
        }

        if (! GoogleSheetsStatusConfig::isStatusEnabled($order->status)) {
            return response()->json([
                'message' => trans('order::messages.google_sheets_sync_status_disabled'),
            ], 422);
        }

        try {
            $sync->sync($order->fresh(), forceSheets: true);
            $order->refresh();
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'message' => trans('order::messages.google_sheets_sync_failed', [
                    'error' => $exception->getMessage(),
                ]),
            ], 500);
        }

        if (! $order->google_sheets_synced_at || $order->google_sheets_sync_error) {
            return response()->json([
                'message' => trans('order::messages.google_sheets_sync_failed', [
                    'error' => $order->google_sheets_sync_error
                        ?: trans('order::messages.google_sheets_sync_unknown_error'),
                ]),
            ], 500);
        }

        return response()->json([
            'message' => trans('order::messages.google_sheets_sync_success', [
                'time' => $order->google_sheets_synced_at->format('d M Y, H:i'),
                'tab' => $order->google_sheets_tab,
            ]),
            'synced_at' => $order->google_sheets_synced_at->toIso8601String(),
            'tab' => $order->google_sheets_tab,
        ]);
    }
}
