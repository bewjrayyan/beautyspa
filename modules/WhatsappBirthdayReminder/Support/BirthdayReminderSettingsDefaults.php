<?php

namespace Modules\WhatsappBirthdayReminder\Support;

use Modules\Setting\Entities\Setting;
use Modules\WhatsappBirthdayReminder\Enums\RewardType;

class BirthdayReminderSettingsDefaults
{
    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'wabr_enabled' => false,
            'wabr_message_template' => '',
            'wabr_image_file_id' => null,
            'wabr_reward_type' => RewardType::POINTS,
            'wabr_reward_points' => 100,
            'wabr_discount_value' => 10,
            'wabr_discount_is_percent' => true,
            'wabr_voucher_value' => 20,
            'wabr_coupon_validity_days' => 30,
            'wabr_schedule_time' => '09:00',
        ];
    }


    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::defaults());
    }


    public static function applyMissingOnly(): void
    {
        $defaults = self::defaults();
        $toSet = [];

        foreach ($defaults as $key => $value) {
            $current = setting($key);

            if ($current === null || $current === '') {
                $toSet[$key] = $value;
            }
        }

        if ($toSet !== []) {
            Setting::setMany($toSet);
        }
    }


    /**
     * @return array<string, mixed>
     */
    public static function formSettings(): array
    {
        $settings = [];

        foreach (self::defaults() as $key => $default) {
            $settings[$key] = setting($key, $default);
        }

        return $settings;
    }
}
