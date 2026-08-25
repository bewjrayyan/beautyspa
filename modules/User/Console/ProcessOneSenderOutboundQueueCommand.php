<?php

namespace Modules\User\Console;

use Illuminate\Console\Command;
use Modules\User\Services\OneSenderOutboundQueueService;
use Throwable;

class ProcessOneSenderOutboundQueueCommand extends Command
{
    protected $signature = 'onesender:process-outbound-queue {--limit=50 : Maximum messages to process}';

    protected $description = 'Process due OneSender outbound WhatsApp queue messages';

    public function handle(OneSenderOutboundQueueService $queue): int
    {
        try {
            if (! $queue->isEnabled()) {
                $this->warn('Outbound queue is disabled in settings.');

                return self::SUCCESS;
            }

            $processed = $queue->processDueBatch((int) $this->option('limit'));

            $this->info("Processed {$processed} queued message(s).");
        } catch (Throwable $e) {
            // Transient MySQL outages should not fail the whole schedule run.
            $this->warn('onesender:process-outbound-queue skipped: '.$e->getMessage());
        }

        return self::SUCCESS;
    }
}
