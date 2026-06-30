<?php

namespace Modules\SpecialGift\Services;

use Modules\Media\Entities\File;
use Modules\SpecialGift\Support\SpecialGiftPageSettings;

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


    public function pageCopy(string $key, string $fallbackTranslationKey): string
    {
        $value = trim((string) setting($key, ''));

        if ($value !== '') {
            return $value;
        }

        return trans($fallbackTranslationKey);
    }


    public function pageTitle(): string
    {
        return $this->pageCopy('specialgift_page_title', 'specialgift::messages.page_title');
    }


    public function pageTagline(): string
    {
        return $this->pageCopy('specialgift_page_tagline', 'specialgift::messages.page_tagline');
    }


    public function pageLead(): string
    {
        return $this->pageCopy('specialgift_page_lead', 'specialgift::messages.page_lead');
    }


    public function stepOrder(): string
    {
        return $this->pageCopy('specialgift_step_order', 'specialgift::messages.step_order');
    }


    public function stepDetails(): string
    {
        return $this->pageCopy('specialgift_step_details', 'specialgift::messages.step_details');
    }


    public function stepSend(): string
    {
        return $this->pageCopy('specialgift_step_send', 'specialgift::messages.step_send');
    }


    public function formTitle(): string
    {
        return $this->pageCopy('specialgift_form_title', 'specialgift::messages.form_title');
    }


    public function submitLabel(): string
    {
        return $this->pageCopy('specialgift_submit_label', 'specialgift::messages.submit');
    }


    public function trustNote(): string
    {
        return $this->pageCopy('specialgift_trust_note', 'specialgift::messages.trust_note');
    }


    public function previewLabel(): string
    {
        return $this->pageCopy('specialgift_preview_label', 'specialgift::messages.preview_label');
    }


    /**
     * @return array{
     *   preset: string,
     *   gradient: bool,
     *   bokeh: bool,
     *   sparkles: bool,
     *   accent_color: string
     * }
     */
    public function pageDesign(): array
    {
        return SpecialGiftPageSettings::resolved();
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
