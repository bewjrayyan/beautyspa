<?php

namespace Modules\WhatsappBirthdayReminder\Services;

use Modules\Media\Entities\File;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\WhatsappBirthdayReminder\Enums\RewardType;
use Modules\WhatsappBirthdayReminder\Support\BirthdayReminderSettingsDefaults;

class BirthdayReminderConfig
{
    public function enabled(): bool
    {
        return filter_var(setting('wabr_enabled', false), FILTER_VALIDATE_BOOLEAN);
    }


    public function messageTemplate(): string
    {
        $template = trim((string) setting('wabr_message_template', ''));

        if ($template === '') {
            return trans('whatsappbirthday::settings.message_template_default');
        }

        return $template;
    }


    public function rewardType(): string
    {
        $type = (string) setting('wabr_reward_type', RewardType::POINTS);

        return in_array($type, RewardType::all(), true) ? $type : RewardType::POINTS;
    }


    public function rewardPoints(): int
    {
        return max(0, (int) setting('wabr_reward_points', 100));
    }


    public function discountValue(): float
    {
        return max(0, (float) setting('wabr_discount_value', 10));
    }


    public function discountIsPercent(): bool
    {
        return filter_var(setting('wabr_discount_is_percent', true), FILTER_VALIDATE_BOOLEAN);
    }


    public function voucherValue(): float
    {
        return max(0, (float) setting('wabr_voucher_value', 20));
    }


    public function couponValidityDays(): int
    {
        return max(1, (int) setting('wabr_coupon_validity_days', 30));
    }


    public function scheduleTime(): string
    {
        $time = trim((string) setting('wabr_schedule_time', '09:00'));

        return preg_match('/^\d{2}:\d{2}$/', $time) ? $time : '09:00';
    }


    public function imageFile(): ?File
    {
        $fileId = (int) setting('wabr_image_file_id');

        if ($fileId <= 0) {
            return null;
        }

        $file = File::find($fileId);

        return $file?->isImage() ? $file : null;
    }


    public function imageUrl(): ?string
    {
        $file = $this->imageFile();

        if (! $file) {
            return null;
        }

        return $this->absolutePublicUrl((string) $file->path);
    }


    public function isReady(): bool
    {
        return $this->enabled() && OneSenderWhatsAppService::isConfigured();
    }


    /**
     * True when this module owns the birthday WhatsApp greeting
     * (Loyalty should skip its plain-text birthday notification).
     */
    public function ownsBirthdayWhatsApp(): bool
    {
        return $this->isReady();
    }


    /**
     * @return array<string, mixed>
     */
    public function formSettings(): array
    {
        return BirthdayReminderSettingsDefaults::formSettings();
    }


    private function absolutePublicUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '' || preg_match('#^https?://#i', $url)) {
            return $url;
        }

        $parsed = parse_url((string) config('app.url'));

        if (! is_array($parsed) || empty($parsed['host'])) {
            return url($url);
        }

        $origin = ($parsed['scheme'] ?? 'https').'://'.$parsed['host'];

        if (! empty($parsed['port'])) {
            $origin .= ':'.$parsed['port'];
        }

        return $origin.(str_starts_with($url, '/') ? $url : '/'.$url);
    }
}
