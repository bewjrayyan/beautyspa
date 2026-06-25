<?php

namespace Modules\GoogleIntegration\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\GoogleIntegration\Services\GoogleCalendarBulkSyncService;
use Modules\GoogleIntegration\Services\GoogleCalendarConnectionTester;
use Modules\GoogleIntegration\Services\GoogleCalendarService;

class GoogleCalendarSettingsController
{
    public function testConnection(Request $request, GoogleCalendarConnectionTester $tester): JsonResponse
    {
        $result = $tester->test($request->only([
            'google_service_account_json',
            'google_calendar_id',
        ]));

        return response()->json($result, $result['ok'] ? 200 : 422);
    }


    public function syncAllCount(GoogleCalendarBulkSyncService $bulk): JsonResponse
    {
        if (! GoogleCalendarService::isEnabled()) {
            return response()->json([
                'message' => trans('setting::messages.google_calendar_sync_all_disabled'),
            ], 422);
        }

        return response()->json([
            'total' => $bulk->totalAppointments(),
        ]);
    }


    public function syncAllChunk(Request $request, GoogleCalendarBulkSyncService $bulk): JsonResponse
    {
        if (! GoogleCalendarService::isEnabled()) {
            return response()->json([
                'message' => trans('setting::messages.google_calendar_sync_all_disabled'),
            ], 422);
        }

        $result = $bulk->syncChunk(
            (int) $request->input('offset', 0),
            (int) $request->input('limit', 25),
        );

        return response()->json(array_merge([
            'message' => trans('setting::messages.google_calendar_sync_chunk_progress', [
                'current' => min($result['offset'], $result['total']),
                'total' => $result['total'],
            ]),
        ], $result));
    }
}
