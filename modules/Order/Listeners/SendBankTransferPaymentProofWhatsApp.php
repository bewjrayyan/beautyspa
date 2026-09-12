<?php

namespace Modules\Order\Listeners;

use Exception;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Modules\Checkout\Events\OrderPlaced;
use Modules\Order\Services\BankTransferPaymentProofWhatsAppNotifier;

class SendBankTransferPaymentProofWhatsApp implements ShouldHandleEventsAfterCommit
{
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
