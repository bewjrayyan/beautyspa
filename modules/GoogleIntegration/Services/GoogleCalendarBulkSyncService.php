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
     *   done: bool,
     *   errors: array<int, array{order_id: int, message: string}>
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
        $errors = [];

        foreach ($orders as $order) {
            $result = $this->sync->syncCalendarAppointment($order->fresh(), 'bulk');

            if ($result['ok']) {
                $synced++;
            } elseif ($result['error']) {
                $failed++;
                $errors[] = [
                    'order_id' => $order->id,
                    'message' => $result['error'],
                ];
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
            'errors' => $errors,
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
