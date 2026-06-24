<?php

namespace Modules\GoogleIntegration\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\GoogleIntegration\Services\GoogleSheetsConnectionTester;

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
}
