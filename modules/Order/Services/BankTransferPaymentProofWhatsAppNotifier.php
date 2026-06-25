<?php

namespace Modules\Order\Services;

use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use Modules\Setting\Support\SettingValues;
use Modules\User\Services\OneSenderWhatsAppService;

class BankTransferPaymentProofWhatsAppNotifier
{
    public function __construct(
        private readonly OneSenderWhatsAppService $oneSender,
        private readonly BankTransferPaymentProofWhatsAppMessage $messages,
    ) {
    }

    public function canSend(Order $order): bool
    {
        return $order->getRawOriginal('payment_method') === 'bank_transfer'
            && $order->hasPaymentProof()
            && SettingValues::isTruthy('bank_transfer_payment_proof_whatsapp_enabled')
            && filled(trim((string) setting('bank_transfer_payment_proof_whatsapp_group_id', '')))
            && OneSenderWhatsAppService::isConfigured();
    }

    public function send(Order $order): void
    {
        if (! $this->canSend($order)) {
            return;
        }

        $order->loadMissing('paymentProof');

        $proof = $order->paymentProof;

        if (! $proof instanceof File) {
            return;
        }

        $groupId = trim((string) setting('bank_transfer_payment_proof_whatsapp_group_id', ''));
        $caption = $this->messages->build($order);
        $context = [
            'source' => 'order.bank_transfer.payment_proof',
            'dedupe_key' => 'order:' . $order->id . ':payment_proof_group',
            'immediate' => true,
        ];

        try {
            $fileUrl = app(OrderPaymentProofPublicUrlService::class)->whatsAppMediaUrl($proof, $order);
        } catch (\Throwable $exception) {
            report($exception);

            return;
        }

        $sent = $this->isImageProof($proof)
            ? $this->oneSender->sendImageToGroup($groupId, $fileUrl, $caption, $context)
            : $this->oneSender->sendDocumentToGroup(
                $groupId,
                $fileUrl,
                $proof->filename ?: ('payment-proof-' . $order->id . '.' . $proof->extension),
                $caption,
                $context
            );

        if (! $sent) {
            report(new \RuntimeException(trans('order::whatsapp.send_failed')));
        }
    }

    private function isImageProof(File $proof): bool
    {
        $mime = (string) $proof->mime;

        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        return in_array(strtolower((string) $proof->extension), ['jpg', 'jpeg', 'png', 'webp'], true);
    }
}
