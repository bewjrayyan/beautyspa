<?php

namespace Modules\GoogleIntegration\Console;

use Exception;
use Illuminate\Console\Command;
use Modules\GoogleIntegration\Services\GoogleSheetsService;
use Modules\GoogleIntegration\Services\OrderGoogleSyncService;
use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;
use Modules\Order\Entities\Order;

class BackfillGoogleSheetsCommand extends Command
{
    protected $signature = 'google-sheets:backfill
                            {--limit=0 : Maximum number of orders to sync (0 = no limit)}
                            {--order= : Sync a single order ID only}
                            {--force : Clear sync tracking and re-sync matching orders}';

    protected $description = 'Sync orders in enabled statuses to their Google Sheets tabs';

    public function handle(OrderGoogleSyncService $sync): int
    {
        if (! GoogleSheetsService::isEnabled()) {
            $this->error('Google Sheets sync is disabled or not configured.');

            return self::FAILURE;
        }

        $orderId = $this->option('order');

        if ($orderId) {
            return $this->syncSingleOrder((int) $orderId, $sync);
        }

        $statuses = GoogleSheetsStatusConfig::enabledStatuses();

        if ($statuses === []) {
            $this->error('No order statuses are enabled for Google Sheets sync.');

            return self::FAILURE;
        }

        $query = Order::query()
            ->whereIn('status', $statuses)
            ->orderBy('id');

        if (! $this->option('force')) {
            $query->whereNull('google_sheets_synced_at');
        }

        $limit = max(0, (int) $this->option('limit'));

        if ($limit > 0) {
            $query->limit($limit);
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->info('No orders waiting for Google Sheets sync.');

            return self::SUCCESS;
        }

        $synced = 0;
        $failed = 0;

        foreach ($orders as $order) {
            if ($this->syncOrder($order, $sync, (bool) $this->option('force'))) {
                $synced++;
                $this->line("Synced order #{$order->id} → {$order->google_sheets_tab}");
            } else {
                $failed++;
                $this->warn("Failed order #{$order->id}");
            }
        }

        $this->info("Done. Synced: {$synced}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }


    private function syncSingleOrder(int $orderId, OrderGoogleSyncService $sync): int
    {
        $order = Order::query()->find($orderId);

        if (! $order) {
            $this->error("Order #{$orderId} not found.");

            return self::FAILURE;
        }

        if (! GoogleSheetsStatusConfig::isStatusEnabled($order->status)) {
            $this->error("Order #{$orderId} status is not enabled for Google Sheets sync.");

            return self::FAILURE;
        }

        if ($this->syncOrder($order, $sync, (bool) $this->option('force'))) {
            $this->info("Synced order #{$orderId} → {$order->google_sheets_tab}.");

            return self::SUCCESS;
        }

        $this->error("Failed to sync order #{$orderId}.");

        return self::FAILURE;
    }


    private function syncOrder(Order $order, OrderGoogleSyncService $sync, bool $force): bool
    {
        try {
            $sync->sync($order->fresh(), $force);
            $order->refresh();
        } catch (Exception $exception) {
            report($exception);
            $this->error($exception->getMessage());

            return false;
        }

        return (bool) $order->google_sheets_synced_at;
    }
}
