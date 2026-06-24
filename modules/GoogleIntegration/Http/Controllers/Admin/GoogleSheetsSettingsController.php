<?php

namespace Modules\GoogleIntegration\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\GoogleIntegration\Services\GoogleSheetsBulkSyncService;
use Modules\GoogleIntegration\Services\GoogleSheetsConnectionTester;
use Modules\GoogleIntegration\Services\GoogleSheetsService;
use Modules\GoogleIntegration\Services\GoogleSheetsSyncLogExporter;
use Modules\GoogleIntegration\Services\OrderGoogleSyncService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GoogleSheetsSettingsController
{
    public function testConnection(Request $request, GoogleSheetsConnectionTester $tester): JsonResponse
    {
        $result = $tester->test($request->only([
            'google_service_account_json',
            'google_spreadsheet_id',
            'google_sheet_name',
        ]));

        return response()->json($result, $result['ok'] ? 200 : 422);
    }


    public function syncAllCount(GoogleSheetsBulkSyncService $bulk): JsonResponse
    {
        if (! GoogleSheetsService::isEnabled()) {
            return response()->json([
                'message' => trans('setting::messages.google_sheets_sync_all_disabled'),
            ], 422);
        }

        return response()->json([
            'total' => $bulk->totalOrders(),
        ]);
    }


    public function syncAllChunk(Request $request, GoogleSheetsBulkSyncService $bulk): JsonResponse
    {
        if (! GoogleSheetsService::isEnabled()) {
            return response()->json([
                'message' => trans('setting::messages.google_sheets_sync_all_disabled'),
            ], 422);
        }

        $result = $bulk->syncChunk(
            (int) $request->input('offset', 0),
            (int) $request->input('limit', 25),
        );

        return response()->json(array_merge([
            'message' => trans('setting::messages.google_sheets_sync_chunk_progress', [
                'current' => min($result['offset'], $result['total']),
                'total' => $result['total'],
            ]),
        ], $result));
    }


    public function syncAll(OrderGoogleSyncService $sync): JsonResponse
    {
        if (! GoogleSheetsService::isEnabled()) {
            return response()->json([
                'message' => trans('setting::messages.google_sheets_sync_all_disabled'),
            ], 422);
        }

        try {
            $result = $sync->syncAll();
        } catch (Exception $exception) {
            report($exception);

            return response()->json([
                'message' => trans('setting::messages.google_sheets_sync_all_failed', [
                    'error' => $exception->getMessage(),
                ]),
            ], 500);
        }

        return response()->json(array_merge([
            'message' => trans('setting::messages.google_sheets_sync_all_success', $result),
        ], $result));
    }


    public function exportLogs(GoogleSheetsSyncLogExporter $exporter): StreamedResponse
    {
        if (! GoogleSheetsService::isEnabled()) {
            abort(404);
        }

        return $exporter->download();
    }
}
