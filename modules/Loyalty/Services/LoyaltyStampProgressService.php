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
     * @return array<string, mixed>|null
     */
    public function cardFromWallet(LoyaltyStampWallet $wallet): ?array
    {
        $wallet->loadMissing('program');

        if (! $wallet->program) {
            return null;
        }

        return $this->formatCard($wallet->program, $wallet);
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
                fn (LoyaltyStampWallet $w) => $w->isActive() || $w->completed_at || $this->isExpired($w)
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
        $stampsRequired = (int) $program->stamps_required;
        $stampsEarned = $wallet
            ? min((int) $wallet->stamps_count, $stampsRequired)
            : 0;

        if ($wallet?->redeemed_at || $wallet?->fulfilled_at) {
            $stampsEarned = $stampsRequired;
        }

        $isExpired = $wallet && $this->isExpired($wallet);
        $isComplete = $wallet
            && ! $isExpired
            && ($stampsEarned >= $stampsRequired || $wallet->redeemed_at || $wallet->fulfilled_at);
        $canRedeem = $wallet && $wallet->completed_at && ! $wallet->redeemed_at;

        return [
            'wallet_id' => $wallet?->id,
            'program_id' => $program->id,
            'name' => $program->name,
            'reward_description' => $program->reward_description,
            'stamps_required' => $stampsRequired,
            'stamps_earned' => $stampsEarned,
            'days_until_expiry' => $isExpired ? null : $wallet?->daysUntilExpiry(),
            'is_complete' => (bool) $isComplete,
            'can_redeem' => (bool) $canRedeem,
            'not_started' => ! $wallet,
            'is_expired' => $isExpired,
        ];
    }


    private function isExpired(LoyaltyStampWallet $wallet): bool
    {
        return $wallet->expires_at
            && $wallet->expires_at->isPast()
            && ! $wallet->completed_at
            && ! $wallet->redeemed_at;
    }
}
