<?php

namespace Modules\Loyalty\Services;

use Illuminate\Support\Collection;
use Modules\Loyalty\Entities\LoyaltyStampProgram;
use Modules\Loyalty\Entities\LoyaltyStampWallet;
use Modules\User\Entities\User;

class LoyaltyStampProgressService
{
    /**
     * Stamp cards for the customer account (all active programs).
     *
     * @return array<int, array<string, mixed>>
     */
    public function forAccount(User $user): array
    {
        return $this->buildCards($user, includeNotStarted: true);
    }


    /**
     * Stamp cards for order-complete (only cards with progress or pending redemption).
     *
     * @return array<int, array<string, mixed>>
     */
    public function forOrderComplete(User $user): array
    {
        return array_values(array_filter(
            $this->buildCards($user, includeNotStarted: false),
            fn (array $card) => $card['stamps_earned'] > 0 || $card['can_redeem']
        ));
    }


    public function recentRedemptions(User $user, int $limit = 5): Collection
    {
        return LoyaltyStampWallet::query()
            ->where('user_id', $user->id)
            ->whereNotNull('redeemed_at')
            ->with('program')
            ->latest('redeemed_at')
            ->limit($limit)
            ->get();
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildCards(User $user, bool $includeNotStarted): array
    {
        $programs = LoyaltyStampProgram::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        if ($programs->isEmpty()) {
            return [];
        }

        $wallets = LoyaltyStampWallet::query()
            ->where('user_id', $user->id)
            ->whereIn('program_id', $programs->pluck('id'))
            ->whereNull('redeemed_at')
            ->with('program')
            ->latest('id')
            ->get()
            ->groupBy('program_id');

        $cards = [];

        foreach ($programs as $program) {
            $wallet = $wallets->get($program->id)?->first(
                fn (LoyaltyStampWallet $w) => $w->isActive() || $w->completed_at
            );

            if (! $wallet && ! $includeNotStarted) {
                continue;
            }

            $cards[] = $this->formatCard($program, $wallet);
        }

        return $cards;
    }


    private function formatCard(LoyaltyStampProgram $program, ?LoyaltyStampWallet $wallet): array
    {
        $stampsEarned = $wallet
            ? min((int) $wallet->stamps_count, (int) $program->stamps_required)
            : 0;

        $isComplete = $wallet && $wallet->completed_at && ! $wallet->redeemed_at;

        return [
            'wallet_id' => $wallet?->id,
            'program_id' => $program->id,
            'name' => $program->name,
            'reward_description' => $program->reward_description,
            'stamps_required' => (int) $program->stamps_required,
            'stamps_earned' => $stampsEarned,
            'days_until_expiry' => $wallet?->daysUntilExpiry(),
            'is_complete' => (bool) $isComplete,
            'can_redeem' => (bool) $isComplete,
            'not_started' => ! $wallet,
        ];
    }
}
