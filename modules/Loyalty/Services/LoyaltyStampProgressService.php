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
            fn (array $card) => ! ($card['is_expired'] ?? false)
                && (($card['stamps_earned'] > 0) || ($card['can_redeem'] ?? false))
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
        $wallet->loadMissing(['program', 'entries.order.products.product']);

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
            ->with(['program', 'entries.order.products.product'])
            ->latest('id')
            ->get()
            ->groupBy('program_id');

        $cards = [];

        foreach ($programs as $program) {
            $wallet = $this->resolveDisplayWallet($wallets->get($program->id) ?? collect());

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
            ? $this->stampsEarnedFromWallet($wallet, $stampsRequired)
            : 0;

        $isExpired = $wallet && $this->isExpired($wallet);

        if ($isExpired && $stampsEarned === 0 && ! $wallet->completed_at) {
            $wallet = null;
            $isExpired = false;
            $stampsEarned = 0;
        }

        $isComplete = $wallet
            && ! $isExpired
            && ($stampsEarned >= $stampsRequired || $wallet->redeemed_at || $wallet->fulfilled_at);
        $canRedeem = $wallet && ! $isExpired && $stampsEarned >= $stampsRequired && ! $wallet->redeemed_at;

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


    /**
     * Prefer loyalty_stamp_entries over denormalized stamps_count so UI ticks
     * never appear without real award records.
     */
    private function stampsEarnedFromWallet(LoyaltyStampWallet $wallet, int $stampsRequired): int
    {
        $fromEntries = $wallet->earnedStampsCount();

        $updates = [];

        if ((int) $wallet->stamps_count !== $fromEntries) {
            $updates['stamps_count'] = $fromEntries;
        }

        if ($wallet->completed_at && $fromEntries < $stampsRequired) {
            $updates['completed_at'] = null;
        }

        if ($updates !== []) {
            $wallet->forceFill($updates)->saveQuietly();
        }

        return min(max(0, $fromEntries), $stampsRequired);
    }


    /**
     * Prefer the live card; fall back to ready-to-redeem, then the latest expired card.
     *
     * @param  \Illuminate\Support\Collection<int, LoyaltyStampWallet>  $programWallets
     */
    private function resolveDisplayWallet(Collection $programWallets): ?LoyaltyStampWallet
    {
        if ($programWallets->isEmpty()) {
            return null;
        }

        return $programWallets->first(fn (LoyaltyStampWallet $wallet) => $wallet->isActive())
            ?? $programWallets->first(fn (LoyaltyStampWallet $wallet) => $wallet->completed_at && ! $wallet->redeemed_at)
            ?? $programWallets->first(fn (LoyaltyStampWallet $wallet) => $this->isExpired($wallet));
    }


    private function isExpired(LoyaltyStampWallet $wallet): bool
    {
        return $wallet->expires_at
            && $wallet->expires_at->isPast()
            && ! $wallet->completed_at
            && ! $wallet->redeemed_at;
    }
}
