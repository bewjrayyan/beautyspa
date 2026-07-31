<?php

namespace Modules\Payment\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Payment\Services\ChipWebhookProcessor;
use Throwable;

class ProcessChipWebhookPurchase implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 60;

    public int $uniqueFor = 600;

    /** @var array<int, int> */
    public array $backoff = [15, 60, 180, 600];

    public function __construct(public readonly string $purchaseId)
    {
    }

    public function uniqueId(): string
    {
        return hash('sha256', $this->purchaseId);
    }

    public function handle(ChipWebhookProcessor $processor): void
    {
        $processor->process($this->purchaseId);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('CHIP webhook job exhausted all retries.', [
            'purchase_id_hash' => hash('sha256', $this->purchaseId),
            'exception' => $exception::class,
        ]);
    }
}
