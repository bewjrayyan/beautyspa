<?php

namespace Modules\WhatsappBirthdayReminder\Services;

use Illuminate\Support\Facades\Log;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;
use Modules\WhatsappBirthdayReminder\Entities\BirthdayReminderLog;
use Modules\WhatsappBirthdayReminder\Enums\DeliveryStatus;
use Modules\WhatsappBirthdayReminder\Enums\RewardType;

class BirthdayReminderService
{
    public function __construct(
        private BirthdayReminderConfig $config,
        private BirthdayCustomerFinder $finder,
        private BirthdayRewardService $rewards,
        private BirthdayMessageBuilder $messages,
        private BirthdayWhatsAppSender $sender,
    ) {}


    /**
     * @return array{sent: int, skipped: int, failed: int, total: int}
     */
    public function processToday(bool $force = false): array
    {
        $stats = ['sent' => 0, 'skipped' => 0, 'failed' => 0, 'total' => 0];

        if (! $this->config->enabled() && ! $force) {
            return $stats;
        }

        foreach ($this->finder->forToday() as $user) {
            $stats['total']++;
            $result = $this->processUser($user, $force);
            $stats[$result]++;
        }

        return $stats;
    }


    /**
     * @return 'sent'|'skipped'|'failed'
     */
    public function processUser(User $user, bool $force = false): string
    {
        $year = (int) now()->year;

        $existing = BirthdayReminderLog::query()
            ->where('user_id', $user->id)
            ->where('year', $year)
            ->first();

        if ($existing && $existing->delivery_status === DeliveryStatus::SENT && ! $force) {
            return 'skipped';
        }

        $log = $existing ?? BirthdayReminderLog::create([
            'user_id' => $user->id,
            'year' => $year,
            'phone' => PhoneNumber::normalize((string) $user->phone) ?: null,
            'reward_type' => $this->config->rewardType(),
            'delivery_status' => DeliveryStatus::PENDING,
        ]);

        try {
            $reward = $this->rewards->award($user);
            $message = $this->messages->build($user, $reward);

            $log->update([
                'phone' => PhoneNumber::normalize((string) $user->phone) ?: $log->phone,
                'reward_type' => $reward['type'] ?? RewardType::NONE,
                'reward_payload' => $reward['payload'] ?? [],
                'coupon_code' => $reward['coupon_code'] ?? null,
                'coupon_id' => $reward['coupon_id'] ?? null,
                'points_awarded' => $reward['points'] ?? null,
                'message_preview' => $message,
            ]);

            $delivery = $this->sender->send((string) $user->phone, $message);

            if ($delivery['sent']) {
                $log->markSent($delivery['image_url']);

                return 'sent';
            }

            if ($delivery['skipped']) {
                $log->markSkipped((string) $delivery['error']);

                return 'skipped';
            }

            $log->markFailed((string) ($delivery['error'] ?? 'Unknown error'));

            return 'failed';
        } catch (\Throwable $e) {
            Log::error('Birthday reminder processing failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            $log->markFailed($e->getMessage());

            return 'failed';
        }
    }
}
