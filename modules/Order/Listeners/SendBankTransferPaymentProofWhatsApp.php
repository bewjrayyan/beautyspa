<?php

namespace Modules\Order\Listeners;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Checkout\Events\OrderPlaced;
use Modules\Order\Services\BankTransferPaymentProofWhatsAppNotifier;

class SendBankTransferPaymentProofWhatsApp implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [15, 60, 180];

    public function __construct(
        private readonly BankTransferPaymentProofWhatsAppNotifier $notifier,
    ) {
    }

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order->fresh(['paymentProof']);

        if (! $this->notifier->canSend($order)) {
            return;
        }

        try {
            $this->notifier->send($order);
        } catch (Exception $exception) {
            // Never rethrow: a WhatsApp failure must not abort other OrderPlaced
            // listeners (stamp awards, emails, session, etc.).
            report($exception);
        }
    }
}
