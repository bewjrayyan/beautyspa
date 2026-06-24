<?php

namespace Modules\GoogleIntegration\Console;

use Exception;
use Illuminate\Console\Command;
use Modules\GoogleIntegration\Services\GoogleSheetsService;
use Modules\GoogleIntegration\Services\OrderGoogleSyncService;
use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;
use Modules\Order\Entities\Order;

class RetryFailedGoogleSheetsSyncCommand extends Command
{
    protected $signature = 'google-sheets:retry-failed
                            {--limit=50 : Maximum number of orders to retry}
                            {--order= : Retry a single order ID only}';

    protected $description = 'Retry Google Sheets sync for orders that failed or never synced';

    public function handle(OrderGoogleSyncService $sync): int
    {
        if (! GoogleSheetsService::isEnabled()) {
            $this->error('Google Sheets sync is disabled or not configured.');

            return self::FAILURE;
        }

        $orderId = $this->option('order');

        if ($orderId) {
            return $this->retrySingleOrder((int) $orderId, $sync);
        }

        $statuses = GoogleSheetsStatusConfig::enabledStatuses();

        if ($statuses === []) {
            $this->error('No order statuses are enabled for Google Sheets sync.');

            return self::FAILURE;
        }

        $limit = max(1, (int) $this->option('limit'));

        $orders = Order::query()
            ->whereIn('status', $statuses)
            ->where(function ($query) {
                $query->whereNotNull('google_sheets_sync_error')
                    ->orWhereNull('google_sheets_synced_at');
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No failed or pending Google Sheets syncs to retry.');

            return self::SUCCESS;
        }

        $synced = 0;
        $failed = 0;

        foreach ($orders as $order) {
            if ($this->retryOrder($order, $sync)) {
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


    private function retrySingleOrder(int $orderId, OrderGoogleSyncService $sync): int
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

        if ($this->retryOrder($order, $sync)) {
            $this->info("Synced order #{$orderId} → {$order->google_sheets_tab}.");

            return self::SUCCESS;
        }

        $this->error("Failed to sync order #{$orderId}.");

        return self::FAILURE;
    }


    private function retryOrder(Order $order, OrderGoogleSyncService $sync): bool
    {
        try {
            $sync->sync($order->fresh(), forceSheets: (bool) $order->google_sheets_sync_error, trigger: 'retry');
            $order->refresh();
        } catch (Exception $exception) {
            report($exception);
            $this->error($order->google_sheets_sync_error ?: $exception->getMessage());

            return false;
        }

        return (bool) $order->google_sheets_synced_at && ! $order->google_sheets_sync_error;
    }
}
