<?php

namespace Modules\GoogleIntegration\Services;

use Illuminate\Support\Str;
use Modules\GoogleIntegration\Entities\GoogleSheetsSyncLog;
use Modules\Order\Entities\Order;

class GoogleSheetsSyncLogger
{
    private const MAX_LOGS = 100;


    public function log(
        ?Order $order,
        string $trigger,
        bool $success,
        ?string $sheetTab = null,
        ?string $message = null,
    ): void {
        GoogleSheetsSyncLog::query()->create([
            'order_id' => $order?->id,
            'trigger' => Str::limit($trigger, 32, ''),
            'status' => $success ? 'success' : 'failed',
            'sheet_tab' => $sheetTab ? Str::limit($sheetTab, 100, '') : null,
            'message' => $message ? Str::limit($message, 2000, '') : null,
        ]);

        $this->pruneOldLogs();
    }


    private function pruneOldLogs(): void
    {
        $keepFromId = GoogleSheetsSyncLog::query()
            ->orderByDesc('id')
            ->skip(self::MAX_LOGS)
            ->value('id');

        if ($keepFromId === null) {
            return;
        }

        GoogleSheetsSyncLog::query()
            ->where('id', '<=', $keepFromId)
            ->delete();
    }
}
