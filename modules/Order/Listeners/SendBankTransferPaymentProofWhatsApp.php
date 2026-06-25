<?php

namespace Modules\Order\Listeners;

use Exception;
use Modules\Checkout\Events\OrderPlaced;
use Modules\Order\Services\BankTransferPaymentProofWhatsAppNotifier;

class SendBankTransferPaymentProofWhatsApp
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
            report($exception);
        }
    }
}
