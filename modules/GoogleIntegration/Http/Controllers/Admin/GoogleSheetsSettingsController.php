<?php

namespace Modules\GoogleIntegration\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\GoogleIntegration\Services\GoogleSheetsConnectionTester;
use Modules\GoogleIntegration\Services\GoogleSheetsService;
use Modules\GoogleIntegration\Services\OrderGoogleSyncService;

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
}
