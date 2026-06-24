<?php

namespace Modules\GoogleIntegration\Services;

use Exception;
use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;
use Modules\Order\Entities\Order;

class GoogleSheetsBulkSyncService
{
    public function __construct(
        private readonly OrderGoogleSyncService $sync,
    ) {
    }


    public function totalOrders(): int
    {
        return $this->baseQuery()->count();
    }


    /**
     * @return array{
     *   offset: int,
     *   limit: int,
     *   total: int,
     *   processed: int,
     *   synced: int,
     *   failed: int,
     *   done: bool
     * }
     */
    public function syncChunk(int $offset, int $limit): array
    {
        $total = $this->totalOrders();
        $limit = max(1, min($limit, 50));
        $offset = max(0, $offset);

        $orders = $this->baseQuery()
            ->orderBy('id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $synced = 0;
        $failed = 0;

        foreach ($orders as $order) {
            try {
                $this->sync->sync($order->fresh(), forceSheets: false, trigger: 'bulk');
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

        $processed = $orders->count();
        $nextOffset = $offset + $processed;

        return [
            'offset' => $nextOffset,
            'limit' => $limit,
            'total' => $total,
            'processed' => $processed,
            'synced' => $synced,
            'failed' => $failed,
            'done' => $nextOffset >= $total || $processed === 0,
        ];
    }


    private function baseQuery()
    {
        $statuses = GoogleSheetsStatusConfig::enabledStatuses();

        return Order::query()->whereIn('status', $statuses);
    }
}
