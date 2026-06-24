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
            return trans('specialgift::settings.message_template_default');
        }

        return $template;
    }


    public function hasCustomVoucherBackground(): bool
    {
        return $this->voucherBackgroundFile() !== null;
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


    public function defaultVoucherBackgroundPath(): string
    {
        return module_path('SpecialGift', 'Resources/assets/images/default-voucher.jpg');
    }


    public function defaultVoucherBackgroundUrl(): string
    {
        return asset('modules/specialgift/default-voucher.jpg');
    }


    public function resolveVoucherBackgroundPath(): ?string
    {
        $customPath = $this->voucherBackgroundFile()?->realPath();

        if (is_string($customPath) && is_readable($customPath)) {
            return $customPath;
        }

        $defaultPath = $this->defaultVoucherBackgroundPath();

        if (is_readable($defaultPath)) {
            return $defaultPath;
        }

        $publicDefault = public_path('modules/specialgift/default-voucher.jpg');

        return is_readable($publicDefault) ? $publicDefault : null;
    }


    public function resolveVoucherBackgroundUrl(): string
    {
        $file = $this->voucherBackgroundFile();

        if ($file) {
            return $file->path;
        }

        return $this->defaultVoucherBackgroundUrl();
    }


    public function isReady(): bool
    {
        return $this->enabled()
            && $this->resolveVoucherBackgroundPath() !== null
            && \Modules\User\Services\OneSenderWhatsAppService::isConfigured();
    }
}
