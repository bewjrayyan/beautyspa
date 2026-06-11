<?php

namespace Modules\Loyalty\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Loyalty\Entities\LoyaltyStampWallet;
use Modules\User\Entities\User;

class LoyaltyStampRedeemService
{
    public function redeem(LoyaltyStampWallet $wallet, User $user): string
    {
        if ((int) $wallet->user_id !== (int) $user->id) {
            throw new \InvalidArgumentException(trans('loyalty::account.stamp_redeem_not_allowed'));
        }

        if (! $wallet->completed_at) {
            throw new \InvalidArgumentException(trans('loyalty::account.stamp_redeem_not_ready'));
        }

        if ($wallet->redeemed_at) {
            throw new \InvalidArgumentException(trans('loyalty::account.stamp_redeem_already'));
        }

        return DB::transaction(function () use ($wallet) {
            $wallet->refresh();

            if ($wallet->redeemed_at) {
                throw new \InvalidArgumentException(trans('loyalty::account.stamp_redeem_already'));
            }

            $code = $this->generateRedemptionCode($wallet);

            $wallet->update([
                'redeemed_at' => now(),
                'redemption_code' => $code,
            ]);

            return $code;
        });
    }


    private function generateRedemptionCode(LoyaltyStampWallet $wallet): string
    {
        do {
            $code = 'STAMP-' . strtoupper(Str::random(4)) . '-' . $wallet->id;
        } while (LoyaltyStampWallet::where('redemption_code', $code)->exists());

        return $code;
    }
}
