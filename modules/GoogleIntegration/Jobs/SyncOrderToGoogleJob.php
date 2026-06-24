<?php

namespace Modules\GoogleIntegration\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\GoogleIntegration\Services\OrderGoogleSyncService;
use Modules\Order\Entities\Order;

class SyncOrderToGoogleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    public int $tries = 3;


    public int $backoff = 30;


    public function __construct(
        public int $orderId,
        public bool $forceSheets = false,
        public string $trigger = 'auto',
    ) {
    }


    public function handle(OrderGoogleSyncService $sync): void
    {
        $order = Order::query()->find($this->orderId);

        if (! $order) {
            return;
        }

        try {
            $sync->sync($order, $this->forceSheets, $this->trigger);
        } catch (Exception $exception) {
            report($exception);

            throw $exception;
        }
    }
}
