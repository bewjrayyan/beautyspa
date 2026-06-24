<?php

namespace Modules\GoogleIntegration\Console;

use Exception;
use Illuminate\Console\Command;
use Modules\GoogleIntegration\Services\CompletedOrderGoogleSync;
use Modules\GoogleIntegration\Services\GoogleSheetsService;
use Modules\Order\Entities\Order;

class BackfillGoogleSheetsCommand extends Command
{
    protected $signature = 'google-sheets:backfill
                            {--limit=0 : Maximum number of orders to sync (0 = no limit)}
                            {--order= : Sync a single order ID only}';

    protected $description = 'Append completed orders that have not been synced to Google Sheets yet';

    public function handle(CompletedOrderGoogleSync $sync): int
    {
        if (! GoogleSheetsService::isEnabled()) {
            $this->error('Google Sheets sync is disabled or not configured.');

            return self::FAILURE;
        }

        $orderId = $this->option('order');

        if ($orderId) {
            return $this->syncSingleOrder((int) $orderId, $sync);
        }

        $query = Order::query()
            ->where('status', Order::COMPLETED)
            ->whereNull('google_sheets_synced_at')
            ->orderBy('id');

        $limit = max(0, (int) $this->option('limit'));

        if ($limit > 0) {
            $query->limit($limit);
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->info('No completed orders waiting for Google Sheets sync.');

            return self::SUCCESS;
        }

        $synced = 0;
        $failed = 0;

        foreach ($orders as $order) {
            if ($this->syncOrder($order, $sync)) {
                $synced++;
                $this->line("Synced order #{$order->id}");
            } else {
                $failed++;
                $this->warn("Failed order #{$order->id}");
            }
        }

        $this->info("Done. Synced: {$synced}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }


    private function syncSingleOrder(int $orderId, CompletedOrderGoogleSync $sync): int
    {
        $order = Order::query()->find($orderId);

        if (! $order) {
            $this->error("Order #{$orderId} not found.");

            return self::FAILURE;
        }

        if ($order->status !== Order::COMPLETED) {
            $this->error("Order #{$orderId} is not completed.");

            return self::FAILURE;
        }

        if ($order->google_sheets_synced_at) {
            $this->warn("Order #{$orderId} was already synced at {$order->google_sheets_synced_at}.");

            return self::SUCCESS;
        }

        if ($this->syncOrder($order, $sync)) {
            $this->info("Synced order #{$orderId}.");

            return self::SUCCESS;
        }

        $this->error("Failed to sync order #{$orderId}.");

        return self::FAILURE;
    }


    private function syncOrder(Order $order, CompletedOrderGoogleSync $sync): bool
    {
        try {
            $sync->sync($order->fresh());
            $order->refresh();
        } catch (Exception $exception) {
            report($exception);
            $this->error($exception->getMessage());

            return false;
        }

        return (bool) $order->google_sheets_synced_at;
    }
}
