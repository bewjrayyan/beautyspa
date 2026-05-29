<?php

namespace Modules\SpecialGift\Services;

use Modules\Media\Entities\File;

class SpecialGiftConfig
{
    public function enabled(): bool
    {
        return filter_var(setting('specialgift_enabled'), FILTER_VALIDATE_BOOLEAN);
    }


    public function messageTemplate(): string
    {
        $template = trim((string) setting('specialgift_message_template'));

        if ($template === '') {
            return 'Hi {recipient_name}! Anda telah menerima gift voucher. Order: {order_number}. Selamat menikmati hadiah anda!';
        }

        return $template;
    }


    public function voucherBackgroundFile(): ?File
    {
        $fileId = (int) setting('specialgift_voucher_background');

        if ($fileId <= 0) {
            return null;
        }

        $file = File::find($fileId);

        return $file?->isImage() ? $file : null;
    }


    public function isReady(): bool
    {
        return $this->enabled()
            && $this->voucherBackgroundFile() !== null
            && \Modules\User\Services\OneSenderWhatsAppService::isConfigured();
    }
}
