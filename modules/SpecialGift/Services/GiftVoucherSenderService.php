<?php

namespace Modules\SpecialGift\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Order\Entities\Order;
use Modules\SpecialGift\Entities\GiftVoucherSubmission;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\User\Support\PhoneNumber;

class GiftVoucherSenderService
{
    public function __construct(
        private SpecialGiftConfig $config,
        private GiftVoucherImageService $imageService,
        private OneSenderWhatsAppService $whatsApp,
    ) {}


    /**
     * @param  array{recipient_name: string, order_number: string, whatsapp_number: string, sender_name?: string|null}  $data
     */
    public function send(array $data): GiftVoucherSubmission
    {
        $orderNumber = $this->normalizeOrderNumber($data['order_number']);
        $whatsapp = PhoneNumber::normalize($data['whatsapp_number']);
        $order = $this->resolveOrder($orderNumber);

        $submission = GiftVoucherSubmission::create([
            'recipient_name' => $data['recipient_name'],
            'order_number' => $orderNumber,
            'order_id' => $order?->id,
            'whatsapp_number' => $whatsapp,
            'sender_name' => $data['sender_name'] ?? null,
            'delivery_status' => GiftVoucherSubmission::STATUS_PROCESSING,
        ]);

        if (! $this->config->isReady()) {
            return $this->fail($submission, trans('specialgift::messages.not_configured'));
        }

        if ($order === null) {
            return $this->fail($submission, trans('specialgift::messages.order_not_found'));
        }

        if ($whatsapp === '') {
            return $this->fail($submission, trans('specialgift::messages.invalid_phone'));
        }

        $backgroundPath = $this->config->resolveVoucherBackgroundPath();

        if ($backgroundPath === null) {
            return $this->fail($submission, trans('specialgift::messages.image_failed'));
        }

        $imageUrl = $this->imageService->generateFromPath($backgroundPath, $data['recipient_name'], $orderNumber);

        if (! $imageUrl) {
            return $this->fail($submission, trans('specialgift::messages.image_failed'));
        }

        $submission->update(['generated_image_url' => $imageUrl]);

        $message = $this->buildMessage(
            $data['recipient_name'],
            $orderNumber,
            $data['sender_name'] ?? ''
        );

        try {
            $this->whatsApp->sendImage($whatsapp, $imageUrl, $message);
        } catch (Exception $e) {
            Log::error('SpecialGift WhatsApp failed', [
                'submission_id' => $submission->id,
                'error' => $e->getMessage(),
            ]);

            return $this->fail($submission, $e->getMessage());
        }

        $submission->update([
            'delivery_status' => GiftVoucherSubmission::STATUS_SENT,
            'sent_at' => now(),
            'error_message' => null,
        ]);

        return $submission->fresh();
    }


    private function buildMessage(string $recipientName, string $orderNumber, string $senderName): string
    {
        $placeholders = [
            '{recipient_name}' => $recipientName,
            '{order_number}' => $orderNumber,
            '{sender_name}' => $senderName,
            '{voucher_value}' => '',
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $this->config->messageTemplate());
    }


    private function normalizeOrderNumber(string $orderNumber): string
    {
        $orderNumber = trim($orderNumber);

        return ltrim($orderNumber, '#');
    }


    private function resolveOrder(string $orderNumber): ?Order
    {
        if ($orderNumber === '' || ! ctype_digit($orderNumber)) {
            return null;
        }

        return Order::find((int) $orderNumber);
    }


    private function fail(GiftVoucherSubmission $submission, string $message): GiftVoucherSubmission
    {
        $submission->update([
            'delivery_status' => GiftVoucherSubmission::STATUS_FAILED,
            'error_message' => $message,
        ]);

        return $submission->fresh();
    }
}
