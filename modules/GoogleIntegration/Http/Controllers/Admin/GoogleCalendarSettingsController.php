<?php

namespace Modules\GoogleIntegration\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\GoogleIntegration\Services\GoogleCalendarConnectionTester;

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
}
