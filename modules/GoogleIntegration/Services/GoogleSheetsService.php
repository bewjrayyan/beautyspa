<?php

namespace Modules\GoogleIntegration\Services;

use Exception;

class GoogleSheetsService
{
    public function __construct(
        private readonly GoogleServiceAccountClient $client,
        private readonly CompletedOrderRowBuilder $rowBuilder,
    ) {
    }


    public static function isEnabled(): bool
    {
        return GoogleServiceAccountClient::isConfigured()
            && (bool) setting('google_sheets_enabled', false)
            && trim((string) setting('google_spreadsheet_id', '')) !== '';
    }


    public function appendOrderRow(array $row): void
    {
        $spreadsheetId = trim((string) setting('google_spreadsheet_id', ''));
        $sheetName = $this->sheetName();

        $this->ensureHeaders($spreadsheetId, $sheetName);

        $range = rawurlencode($this->sheetRange($sheetName, 'A1'));

        $response = $this->client->http()
            ->withQueryParameters([
                'valueInputOption' => 'USER_ENTERED',
                'insertDataOption' => 'INSERT_ROWS',
            ])
            ->post(
                "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$range}:append",
                ['values' => [$row]]
            );

        if ($response->failed()) {
            throw new Exception(
                'Google Sheets append failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }
    }


    public function buildRow($order): array
    {
        return $this->rowBuilder->row($order);
    }


    /**
     * @return array{spreadsheet_title: string, sheet_name: string}
     */
    public function spreadsheetMetadata(
        string $spreadsheetId,
        ?string $sheetGid,
        string $sheetNameOverride,
        ?GoogleServiceAccountClient $client = null,
    ): array {
        $http = ($client ?? $this->client)->http();

        $response = $http->get(
            "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}",
            ['fields' => 'properties.title,sheets.properties']
        );

        if ($response->failed()) {
            throw new Exception(
                'Google Sheets metadata failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }

        $sheetName = $this->resolveSheetName(
            $response->json('sheets', []),
            $sheetGid,
            $sheetNameOverride,
        );

        return [
            'spreadsheet_title' => (string) ($response->json('properties.title') ?? $spreadsheetId),
            'sheet_name' => $sheetName,
        ];
    }


    private function ensureHeaders(string $spreadsheetId, string $sheetName): void
    {
        $headerRange = rawurlencode($this->sheetRange($sheetName, 'A1:T1'));

        $response = $this->client->http()->get(
            "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$headerRange}"
        );

        $values = $response->json('values.0') ?? [];

        if ($values === $this->rowBuilder->headers()) {
            return;
        }

        if (! empty($values)) {
            return;
        }

        $updateRange = rawurlencode($this->sheetRange($sheetName, 'A1'));

        $write = $this->client->http()
            ->withQueryParameters(['valueInputOption' => 'USER_ENTERED'])
            ->put(
                "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$updateRange}",
                ['values' => [$this->rowBuilder->headers()]]
            );

        if ($write->failed()) {
            throw new Exception(
                'Google Sheets header write failed: ' . ($write->json('error.message') ?? $write->body())
            );
        }
    }


    private function sheetName(): string
    {
        $name = trim((string) setting('google_sheet_name', ''));

        if ($name !== '') {
            return $name;
        }

        $gid = trim((string) setting('google_sheet_gid', ''));

        if ($gid !== '') {
            $resolved = $this->resolveSheetNameByGid(
                trim((string) setting('google_spreadsheet_id', '')),
                $gid
            );

            if ($resolved !== '') {
                return $resolved;
            }
        }

        return 'Completed Bookings';
    }


    /**
     * @param array<int, array<string, mixed>> $sheets
     */
    private function resolveSheetName(array $sheets, ?string $gid, string $sheetNameOverride): string
    {
        if ($sheetNameOverride !== '') {
            return $sheetNameOverride;
        }

        if ($gid !== null && $gid !== '') {
            foreach ($sheets as $sheet) {
                $properties = $sheet['properties'] ?? [];

                if ((string) ($properties['sheetId'] ?? '') === (string) $gid) {
                    $title = (string) ($properties['title'] ?? '');

                    if ($title !== '') {
                        return $title;
                    }
                }
            }
        }

        return 'Completed Bookings';
    }


    private function resolveSheetNameByGid(string $spreadsheetId, string $gid): string
    {
        if ($spreadsheetId === '') {
            return '';
        }

        $response = $this->client->http()->get(
            "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}",
            ['fields' => 'sheets.properties']
        );

        if ($response->failed()) {
            return '';
        }

        foreach ($response->json('sheets', []) as $sheet) {
            $properties = $sheet['properties'] ?? [];

            if ((string) ($properties['sheetId'] ?? '') === (string) $gid) {
                return (string) ($properties['title'] ?? '');
            }
        }

        return '';
    }


    private function sheetRange(string $sheetName, string $cells): string
    {
        $escaped = str_replace("'", "''", $sheetName);

        return "'{$escaped}'!{$cells}";
    }
}
