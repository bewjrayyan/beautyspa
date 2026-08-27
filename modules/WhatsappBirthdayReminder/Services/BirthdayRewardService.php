<?php

namespace Modules\WhatsappBirthdayReminder\Services;

use Illuminate\Support\Str;
use Modules\Coupon\Entities\Coupon;
use Modules\Loyalty\Enums\TransactionType;
use Modules\Loyalty\Services\LoyaltyWalletService;
use Modules\User\Entities\User;
use Modules\WhatsappBirthdayReminder\Enums\RewardType;

class BirthdayRewardService
{
    public function __construct(
        private BirthdayReminderConfig $config,
        private ?LoyaltyWalletService $wallets = null,
    ) {
        if ($this->wallets === null && app('modules')->isEnabled('Loyalty')) {
            $this->wallets = app(LoyaltyWalletService::class);
        }
    }


    /**
     * @return array{
     *   type: string,
     *   points?: int,
     *   coupon_code?: string|null,
     *   coupon_id?: int|null,
     *   value?: float|null,
     *   is_percent?: bool|null,
     *   label: string,
     *   payload: array<string, mixed>
     * }
     */
    public function award(User $user): array
    {
        return match ($this->config->rewardType()) {
            RewardType::POINTS => $this->awardPoints($user),
            RewardType::DISCOUNT => $this->awardCoupon($user, RewardType::DISCOUNT),
            RewardType::VOUCHER => $this->awardCoupon($user, RewardType::VOUCHER),
            default => [
                'type' => RewardType::NONE,
                'label' => '',
                'payload' => [],
            ],
        };
    }


    /**
     * @return array{type: string, points: int, label: string, payload: array<string, mixed>}
     */
    private function awardPoints(User $user): array
    {
        $points = $this->config->rewardPoints();

        if ($points <= 0 || $this->wallets === null) {
            return [
                'type' => RewardType::POINTS,
                'points' => 0,
                'label' => '',
                'payload' => ['points' => 0, 'awarded' => false],
            ];
        }

        $wallet = $this->wallets->getOrCreateForUser($user);
        $referenceId = now()->year.':birthday';

        $existing = $this->wallets->findExistingTransaction(
            $wallet,
            TransactionType::BONUS,
            'birthday',
            $referenceId
        );

        if (! $existing) {
            $this->wallets->credit(
                $wallet,
                $points,
                TransactionType::BONUS,
                'birthday',
                $referenceId,
                trans('loyalty::messages.birthday_bonus'),
                ['year' => now()->year, 'source' => 'whatsapp_birthday_reminder']
            );
        } else {
            $points = (int) $existing->points;
        }

        return [
            'type' => RewardType::POINTS,
            'points' => $points,
            'label' => number_format($points).' '.trans('whatsappbirthday::messages.points_unit'),
            'payload' => [
                'points' => $points,
                'awarded' => $existing === null,
                'reference_id' => $referenceId,
            ],
        ];
    }


    /**
     * @return array{
     *   type: string,
     *   coupon_code: string,
     *   coupon_id: int,
     *   value: float,
     *   is_percent: bool,
     *   label: string,
     *   payload: array<string, mixed>
     * }
     */
    private function awardCoupon(User $user, string $type): array
    {
        $isPercent = $type === RewardType::DISCOUNT && $this->config->discountIsPercent();
        $value = $type === RewardType::VOUCHER
            ? $this->config->voucherValue()
            : $this->config->discountValue();

        $code = $this->uniqueCouponCode();
        $days = $this->config->couponValidityDays();
        $name = trans('whatsappbirthday::messages.coupon_name', [
            'name' => $user->first_name,
            'year' => now()->year,
        ]);

        $coupon = new Coupon([
            'code' => $code,
            'is_percent' => $isPercent,
            'value' => $value,
            'free_shipping' => false,
            'start_date' => now()->startOfDay(),
            'end_date' => now()->addDays($days)->endOfDay(),
            'is_active' => true,
            'usage_limit_per_coupon' => 1,
            'usage_limit_per_customer' => 1,
        ]);

        $coupon->fill([
            'en' => ['name' => $name],
            'ms' => ['name' => $name],
        ]);
        $coupon->save();

        $label = $isPercent
            ? rtrim(rtrim(number_format($value, 2), '0'), '.').'%'
            : number_format($value, 2);

        return [
            'type' => $type,
            'coupon_code' => $code,
            'coupon_id' => (int) $coupon->id,
            'value' => $value,
            'is_percent' => $isPercent,
            'label' => $label,
            'payload' => [
                'coupon_code' => $code,
                'coupon_id' => $coupon->id,
                'value' => $value,
                'is_percent' => $isPercent,
                'valid_days' => $days,
            ],
        ];
    }


    private function uniqueCouponCode(): string
    {
        do {
            $code = 'BDAY'.now()->format('y').Str::upper(Str::random(6));
        } while (Coupon::withoutGlobalScopes()->where('code', $code)->exists());

        return $code;
    }
}
