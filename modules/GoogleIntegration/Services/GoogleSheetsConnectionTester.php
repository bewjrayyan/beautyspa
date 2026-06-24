<?php

namespace Modules\GoogleIntegration\Services;

use Exception;
use Modules\GoogleIntegration\Support\GoogleSpreadsheetUrlParser;

class GoogleSheetsConnectionTester
{
    public function __construct(
        private readonly GoogleServiceAccountClient $client,
        private readonly GoogleSheetsService $sheets,
    ) {
    }


    /**
     * @param array{
     *   google_service_account_json?: string|null,
     *   google_spreadsheet_id?: string|null,
     *   google_sheet_name?: string|null
     * } $input
     *
     * @return array{
     *   ok: bool,
     *   message: string,
     *   client_email?: string,
     *   spreadsheet_title?: string,
     *   sheet_name?: string
     * }
     */
    public function test(array $input = []): array
    {
        $json = trim((string) ($input['google_service_account_json'] ?? setting('google_service_account_json', '')));

        if ($json === '') {
            return [
                'ok' => false,
                'message' => trans('setting::messages.google_sheets_test_missing_json'),
            ];
        }

        $credentials = GoogleServiceAccountClient::credentialsFromJson($json);

        if ($credentials === null) {
            return [
                'ok' => false,
                'message' => trans('setting::messages.google_sheets_test_invalid_json'),
            ];
        }

        try {
            $this->client->usingCredentials($credentials)->accessToken();
        } catch (Exception $exception) {
            return [
                'ok' => false,
                'message' => trans('setting::messages.google_sheets_test_auth_failed', [
                    'error' => $exception->getMessage(),
                ]),
            ];
        }

        $spreadsheetInput = trim((string) ($input['google_spreadsheet_id'] ?? setting('google_spreadsheet_id', '')));

        if ($spreadsheetInput === '') {
            return [
                'ok' => true,
                'message' => trans('setting::messages.google_sheets_test_auth_ok'),
                'client_email' => $credentials['client_email'],
            ];
        }

        $parsed = GoogleSpreadsheetUrlParser::parse($spreadsheetInput);
        $spreadsheetId = $parsed['spreadsheet_id'];

        if ($spreadsheetId === '') {
            return [
                'ok' => false,
                'message' => trans('setting::messages.google_sheets_test_invalid_spreadsheet'),
                'client_email' => $credentials['client_email'],
            ];
        }

        try {
            $metadata = $this->sheets->spreadsheetMetadata(
                $spreadsheetId,
                $parsed['sheet_gid'],
                trim((string) ($input['google_sheet_name'] ?? setting('google_sheet_name', ''))),
                $this->client->usingCredentials($credentials),
            );
        } catch (Exception $exception) {
            return [
                'ok' => false,
                'message' => trans('setting::messages.google_sheets_test_spreadsheet_failed', [
                    'error' => $exception->getMessage(),
                ]),
                'client_email' => $credentials['client_email'],
            ];
        }

        return [
            'ok' => true,
            'message' => trans('setting::messages.google_sheets_test_success', [
                'spreadsheet' => $metadata['spreadsheet_title'],
                'sheet' => $metadata['sheet_name'],
            ]),
            'client_email' => $credentials['client_email'],
            'spreadsheet_title' => $metadata['spreadsheet_title'],
            'sheet_name' => $metadata['sheet_name'],
        ];
    }
}
