<?php

namespace Modules\WhatsappBirthdayReminder\Services;

use Modules\User\Entities\User;
use Modules\WhatsappBirthdayReminder\Enums\RewardType;

class BirthdayMessageBuilder
{
    public function __construct(private BirthdayReminderConfig $config) {}


    /**
     * @param  array{
     *   type?: string,
     *   points?: int,
     *   coupon_code?: string|null,
     *   label?: string,
     *   value?: float|null,
     *   is_percent?: bool|null
     * }  $reward
     */
    public function build(User $user, array $reward = []): string
    {
        $type = $reward['type'] ?? RewardType::NONE;
        $rewardLine = match ($type) {
            RewardType::POINTS => trans('whatsappbirthday::messages.reward_points_line', [
                'points' => number_format((int) ($reward['points'] ?? 0)),
            ]),
            RewardType::DISCOUNT, RewardType::VOUCHER => trans('whatsappbirthday::messages.reward_coupon_line', [
                'code' => $reward['coupon_code'] ?? '',
                'value' => $reward['label'] ?? '',
            ]),
            default => '',
        };

        $placeholders = [
            '{first_name}' => (string) $user->first_name,
            '{last_name}' => (string) $user->last_name,
            '{full_name}' => trim($user->first_name.' '.$user->last_name),
            '{points}' => isset($reward['points']) ? number_format((int) $reward['points']) : '',
            '{coupon_code}' => (string) ($reward['coupon_code'] ?? ''),
            '{reward_label}' => (string) ($reward['label'] ?? ''),
            '{reward_line}' => $rewardLine,
            '{store_name}' => (string) setting('store_name', config('app.name')),
        ];

        return trim(str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $this->config->messageTemplate()
        ));
    }
}
