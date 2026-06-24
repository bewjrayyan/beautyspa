<?php

namespace Modules\GoogleIntegration\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\JsonResponse;
use Modules\GoogleIntegration\Services\CompletedOrderGoogleSync;
use Modules\GoogleIntegration\Services\GoogleSheetsService;
use Modules\Order\Entities\Order;

class OrderGoogleSheetsController
{
    public function sync(Order $order, CompletedOrderGoogleSync $sync): JsonResponse
    {
        if ($order->status !== Order::COMPLETED) {
            return response()->json([
                'message' => trans('order::messages.google_sheets_sync_not_completed'),
            ], 422);
        }

        if (! GoogleSheetsService::isEnabled()) {
            return response()->json([
                'message' => trans('order::messages.google_sheets_sync_disabled'),
            ], 422);
        }

        if ($order->google_sheets_synced_at) {
            return response()->json([
                'message' => trans('order::messages.google_sheets_sync_already_synced', [
                    'time' => $order->google_sheets_synced_at->format('d M Y, H:i'),
                ]),
                'synced_at' => $order->google_sheets_synced_at->toIso8601String(),
            ]);
        }

        try {
            $sync->sync($order->fresh());
            $order->refresh();
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'message' => trans('order::messages.google_sheets_sync_failed', [
                    'error' => $exception->getMessage(),
                ]),
            ], 500);
        }

        if (! $order->google_sheets_synced_at) {
            return response()->json([
                'message' => trans('order::messages.google_sheets_sync_failed', [
                    'error' => trans('order::messages.google_sheets_sync_unknown_error'),
                ]),
            ], 500);
        }

        return response()->json([
            'message' => trans('order::messages.google_sheets_sync_success', [
                'time' => $order->google_sheets_synced_at->format('d M Y, H:i'),
            ]),
            'synced_at' => $order->google_sheets_synced_at->toIso8601String(),
        ]);
    }
}
