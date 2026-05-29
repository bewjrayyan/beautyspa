<?php

namespace Modules\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\User\Services\OneSenderOutboundQueueService;

class ProcessOneSenderOutboundMessage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    public int $tries = 1;


    public function __construct(
        public int $messageId,
    ) {
    }


    public function handle(OneSenderOutboundQueueService $queue): void
    {
        $queue->process($this->messageId);
    }
}
