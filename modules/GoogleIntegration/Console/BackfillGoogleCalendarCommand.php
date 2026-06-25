<?php

namespace Modules\GoogleIntegration\Console;

use Illuminate\Console\Command;
use Modules\GoogleIntegration\Services\GoogleCalendarService;
use Modules\GoogleIntegration\Services\OrderGoogleSyncService;
use Modules\Order\Entities\Order;

class BackfillGoogleCalendarCommand extends Command
{
    protected $signature = 'google-calendar:backfill
                            {--limit=0 : Maximum number of orders to sync (0 = no limit)}
                            {--order= : Sync a single order ID only}';

    protected $description = 'Create Google Calendar events for completed orders with appointments';

    public function handle(OrderGoogleSyncService $sync): int
    {
        if (! GoogleCalendarService::isEnabled()) {
            $this->error('Google Calendar sync is disabled or not configured.');

            return self::FAILURE;
        }

        $orderId = $this->option('order');

        if ($orderId) {
            return $this->syncSingleOrder((int) $orderId, $sync);
        }

        $query = Order::query()
            ->where('status', Order::COMPLETED)
            ->whereNotNull('appointment_date')
            ->orderBy('id');

        $limit = max(0, (int) $this->option('limit'));

        if ($limit > 0) {
            $query->limit($limit);
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->info('No completed orders with appointment dates found.');

            return self::SUCCESS;
        }

        $synced = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($orders as $order) {
            $result = $sync->syncCalendarAppointment($order->fresh(), 'backfill', true);

            if ($result['ok']) {
                if ($result['skipped']) {
                    $skipped++;
                    $this->line("Skipped order #{$order->id} — calendar event already exists.");
                } else {
                    $synced++;
                    $this->line("Synced order #{$order->id} → calendar event {$result['event_id']}");
                }
            } else {
                $failed++;
                $this->warn("Failed order #{$order->id}: " . ($result['error'] ?? 'Unknown error'));
            }
        }

        $this->info("Done. Created: {$synced}, skipped: {$skipped}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }


    private function syncSingleOrder(int $orderId, OrderGoogleSyncService $sync): int
    {
        $order = Order::query()->find($orderId);

        if (! $order) {
            $this->error("Order #{$orderId} not found.");

            return self::FAILURE;
        }

        $result = $sync->syncCalendarAppointment($order->fresh(), 'backfill', true);

        if ($result['ok']) {
            if ($result['skipped']) {
                $this->info("Order #{$orderId} already has a calendar event ({$result['event_id']}).");

                return self::SUCCESS;
            }

            $this->info("Synced order #{$orderId} → calendar event {$result['event_id']}.");

            return self::SUCCESS;
        }

        $this->error("Could not create calendar event for order #{$orderId}: " . ($result['error'] ?? 'Unknown error'));

        return self::FAILURE;
    }
}
