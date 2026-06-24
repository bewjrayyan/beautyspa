<?php

namespace Modules\GoogleIntegration\Services;

use Exception;
use Illuminate\Support\Str;
use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;
use Modules\Order\Entities\Order;

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
            && trim((string) setting('google_spreadsheet_id', '')) !== ''
            && GoogleSheetsStatusConfig::enabledStatuses() !== [];
    }


    public function syncOrder(Order $order): void
    {
        if (! self::isEnabled()) {
            return;
        }

        if (! GoogleSheetsStatusConfig::isStatusEnabled($order->status)) {
            return;
        }

        $spreadsheetId = $this->spreadsheetId();
        $targetTab = GoogleSheetsStatusConfig::tabForStatus($order->status);
        $rowData = $this->buildRow($order);

        $this->ensureTabExists($spreadsheetId, $targetTab);
        $this->ensureHeaders($spreadsheetId, $targetTab);

        $storedTab = trim((string) $order->google_sheets_tab);
        $storedRow = (int) $order->google_sheets_row;

        if ($storedTab !== '' && $storedRow > 0) {
            if ($storedTab === $targetTab) {
                $this->updateOrderRowAt($spreadsheetId, $targetTab, $storedRow, $rowData);
                $this->markSynced($order, $targetTab, $storedRow);

                return;
            }

            $this->deleteOrderRowAt($spreadsheetId, $storedTab, $storedRow);
        }

        $rowNumber = $this->appendOrderRowToTab($spreadsheetId, $targetTab, $rowData);
        $this->markSynced($order, $targetTab, $rowNumber);
    }


    public function buildRow(Order $order): array
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


  /**
     * @return array{synced: int, failed: int, skipped: int}
     */
    public function syncAllOrders(?int $limit = null, string $trigger = 'bulk'): array
    {
        $statuses = GoogleSheetsStatusConfig::enabledStatuses();

        if ($statuses === []) {
            return ['synced' => 0, 'failed' => 0, 'skipped' => 0];
        }

        $query = Order::query()
            ->whereIn('status', $statuses)
            ->orderBy('id');

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        $synced = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($query->cursor() as $order) {
            try {
                app(OrderGoogleSyncService::class)->sync($order->fresh(), trigger: $trigger);
                $order->refresh();

                if ($order->google_sheets_synced_at && ! $order->google_sheets_sync_error) {
                    $synced++;
                } else {
                    $failed++;
                }
            } catch (Exception $exception) {
                report($exception);
                $failed++;
            }
        }

        return compact('synced', 'failed', 'skipped');
    }


    public function removeOrderFromSheet(Order $order): void
    {
        if (! self::isEnabled()) {
            return;
        }

        $storedTab = trim((string) $order->google_sheets_tab);
        $storedRow = (int) $order->google_sheets_row;

        if ($storedTab === '' || $storedRow < 1) {
            $this->clearSyncState($order);

            return;
        }

        $this->deleteOrderRowAt($this->spreadsheetId(), $storedTab, $storedRow);
        $this->clearSyncState($order);
    }


    public function hasSheetRow(Order $order): bool
    {
        return trim((string) $order->google_sheets_tab) !== ''
            && (int) $order->google_sheets_row > 0;
    }


    public function markSyncFailed(Order $order, string $message): void
    {
        $order->forceFill([
            'google_sheets_sync_error' => Str::limit($message, 1000),
        ])->save();
    }


    public function clearSyncState(Order $order): void
    {
        $order->forceFill([
            'google_sheets_tab' => null,
            'google_sheets_row' => null,
            'google_sheets_synced_at' => null,
            'google_sheets_sync_error' => null,
        ])->save();
    }


    private function markSynced(Order $order, string $tab, int $row): void
    {
        $order->forceFill([
            'google_sheets_tab' => $tab,
            'google_sheets_row' => $row,
            'google_sheets_synced_at' => now(),
            'google_sheets_sync_error' => null,
        ])->save();
    }


    private function spreadsheetId(): string
    {
        return trim((string) setting('google_spreadsheet_id', ''));
    }


    /**
     * @param array<int, string|int|float|null> $row
     */
    private function appendOrderRowToTab(string $spreadsheetId, string $tabName, array $row): int
    {
        $range = rawurlencode($this->sheetRange($tabName, 'A1'));

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

        $rowNumber = $this->parseRowFromUpdatedRange((string) $response->json('updates.updatedRange'));

        if ($rowNumber === null) {
            throw new Exception('Google Sheets append succeeded but row number was not returned.');
        }

        return $rowNumber;
    }


    /**
     * @param array<int, string|int|float|null> $row
     */
    private function updateOrderRowAt(string $spreadsheetId, string $tabName, int $rowNumber, array $row): void
    {
        $range = rawurlencode($this->sheetRange($tabName, "A{$rowNumber}"));

        $response = $this->client->http()
            ->withQueryParameters(['valueInputOption' => 'USER_ENTERED'])
            ->put(
                "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$range}",
                ['values' => [$row]]
            );

        if ($response->failed()) {
            throw new Exception(
                'Google Sheets update failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }
    }


    private function deleteOrderRowAt(string $spreadsheetId, string $tabName, int $rowNumber): void
    {
        if ($rowNumber < 2) {
            return;
        }

        $sheetId = $this->sheetIdByTitle($spreadsheetId, $tabName);

        if ($sheetId === null) {
            return;
        }

        $response = $this->client->http()->post(
            "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}:batchUpdate",
            [
                'requests' => [[
                    'deleteDimension' => [
                        'range' => [
                            'sheetId' => $sheetId,
                            'dimension' => 'ROWS',
                            'startIndex' => $rowNumber - 1,
                            'endIndex' => $rowNumber,
                        ],
                    ],
                ]],
            ]
        );

        if ($response->failed()) {
            throw new Exception(
                'Google Sheets row delete failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }

        Order::query()
            ->where('google_sheets_tab', $tabName)
            ->where('google_sheets_row', '>', $rowNumber)
            ->decrement('google_sheets_row');
    }


    private function ensureTabExists(string $spreadsheetId, string $tabName): void
    {
        if ($this->sheetIdByTitle($spreadsheetId, $tabName) !== null) {
            return;
        }

        $response = $this->client->http()->post(
            "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}:batchUpdate",
            [
                'requests' => [[
                    'addSheet' => [
                        'properties' => [
                            'title' => $tabName,
                        ],
                    ],
                ]],
            ]
        );

        if ($response->failed()) {
            throw new Exception(
                'Google Sheets tab create failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }
    }


    private function ensureHeaders(string $spreadsheetId, string $sheetName): void
    {
        $headers = $this->rowBuilder->headers();
        $columnCount = max(1, count($headers));
        $lastColumn = $this->columnLetter($columnCount);
        $headerRange = rawurlencode($this->sheetRange($sheetName, "A1:{$lastColumn}1"));

        $response = $this->client->http()->get(
            "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$headerRange}"
        );

        $values = $response->json('values.0') ?? [];

        if ($values === $headers) {
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
                ['values' => [$headers]]
            );

        if ($write->failed()) {
            throw new Exception(
                'Google Sheets header write failed: ' . ($write->json('error.message') ?? $write->body())
            );
        }
    }


    private function columnLetter(int $index): string
    {
        $letter = '';

        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intdiv($index, 26);
        }

        return $letter !== '' ? $letter : 'A';
    }


    private function sheetIdByTitle(string $spreadsheetId, string $tabName): ?int
    {
        $response = $this->client->http()->get(
            "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}",
            ['fields' => 'sheets.properties']
        );

        if ($response->failed()) {
            return null;
        }

        foreach ($response->json('sheets', []) as $sheet) {
            $properties = $sheet['properties'] ?? [];

            if ((string) ($properties['title'] ?? '') === $tabName) {
                return isset($properties['sheetId']) ? (int) $properties['sheetId'] : null;
            }
        }

        return null;
    }


    private function parseRowFromUpdatedRange(string $updatedRange): ?int
    {
        if ($updatedRange === '') {
            return null;
        }

        if (preg_match('/![A-Z]+(\d+)/', $updatedRange, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
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

        return GoogleSheetsStatusConfig::tabForStatus(Order::COMPLETED);
    }


    private function sheetRange(string $sheetName, string $cells): string
    {
        $escaped = str_replace("'", "''", $sheetName);

        return "'{$escaped}'!{$cells}";
    }
}
