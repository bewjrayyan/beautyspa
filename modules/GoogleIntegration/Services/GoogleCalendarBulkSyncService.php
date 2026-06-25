<?php

namespace Modules\GoogleIntegration\Services;

use Modules\Order\Entities\Order;

class GoogleCalendarBulkSyncService
{
    public function __construct(
        private readonly OrderGoogleSyncService $sync,
    ) {
    }


    public function totalAppointments(): int
    {
        if (! GoogleCalendarService::isEnabled()) {
            return 0;
        }

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
        $total = $this->totalAppointments();
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
            if ($this->sync->syncCalendarAppointment($order->fresh(), 'bulk')) {
                $synced++;
            } else {
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
        return Order::query()
            ->where('status', Order::COMPLETED)
            ->whereNotNull('appointment_date')
            ->whereNull('google_calendar_event_id');
    }
}
