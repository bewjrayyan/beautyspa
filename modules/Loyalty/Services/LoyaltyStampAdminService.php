<?php

namespace Modules\Loyalty\Services;

use Illuminate\Support\Collection;
use Modules\Loyalty\Entities\LoyaltyStampEntry;
use Modules\Loyalty\Entities\LoyaltyStampProgram;
use Modules\Loyalty\Entities\LoyaltyStampWallet;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Order\Entities\Order;
use Modules\User\Entities\User;

class LoyaltyStampAdminService
{
    public function __construct(
        private LoyaltyStampProgressService $stampProgress
    ) {}

    public function lookupByCode(string $code): array
    {
        $code = strtoupper(trim($code));

        if ($code === '') {
            return ['status' => 'not_found'];
        }

        $wallet = LoyaltyStampWallet::query()
            ->where('redemption_code', $code)
            ->with(['program', 'user'])
            ->first();

        if (! $wallet) {
            return ['status' => 'not_found', 'code' => $code];
        }

        $loyaltyWallet = LoyaltyWallet::query()
            ->where('user_id', $wallet->user_id)
            ->first();

        return [
            'status' => $this->resolveLookupStatus($wallet),
            'code' => $code,
            'wallet' => $wallet,
            'loyalty_wallet' => $loyaltyWallet,
        ];
    }


    public function orderStampData(Order $order): ?array
    {
        if (! $order->customer_id) {
            return null;
        }

        if (! LoyaltyStampProgram::query()->active()->exists()) {
            return null;
        }

        $entries = LoyaltyStampEntry::query()
            ->where('order_id', $order->id)
            ->with(['wallet.program'])
            ->get();

        return [
            'stampCards' => $this->orderStampCards($entries),
            'loyalty_wallet' => LoyaltyWallet::query()
                ->where('user_id', $order->customer_id)
                ->first(),
        ];
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    public function orderStampCards(Collection $entries): array
    {
        return $entries
            ->map(function (LoyaltyStampEntry $entry) {
                $wallet = $entry->wallet;

                if (! $wallet) {
                    return null;
                }

                $card = $this->stampProgress->cardFromWallet($wallet);

                if (! $card) {
                    return null;
                }

                return array_merge($card, [
                    'stamps_added_this_order' => (int) $entry->stamps_added,
                    'admin_status' => $this->walletStatus($wallet),
                    'redemption_code' => $wallet->redemption_code,
                ]);
            })
            ->filter()
            ->values()
            ->all();
    }


    public function walletStatus(LoyaltyStampWallet $wallet): string
    {
        if ($wallet->expires_at
            && $wallet->expires_at->isPast()
            && ! $wallet->completed_at
            && ! $wallet->redeemed_at) {
            return 'expired';
        }

        return $this->resolveLookupStatus($wallet);
    }


    public function memberStampData(User $user): array
    {
        $wallets = LoyaltyStampWallet::query()
            ->where('user_id', $user->id)
            ->with('program')
            ->latest('id')
            ->get();

        return [
            'active_cards' => $wallets->filter(
                fn (LoyaltyStampWallet $w) => $w->isActive() && ! $w->completed_at
            )->values(),
            'ready_to_redeem' => $wallets->filter(
                fn (LoyaltyStampWallet $w) => $w->completed_at && ! $w->redeemed_at
            )->values(),
            'redemptions' => $wallets->filter(
                fn (LoyaltyStampWallet $w) => $w->redeemed_at
            )->values(),
        ];
    }


    public function pendingRedemptions(int $limit = 10): Collection
    {
        return LoyaltyStampWallet::query()
            ->whereNotNull('redeemed_at')
            ->whereNull('fulfilled_at')
            ->with(['program', 'user'])
            ->latest('redeemed_at')
            ->limit($limit)
            ->get();
    }


    public function markFulfilled(LoyaltyStampWallet $wallet, int $adminUserId): LoyaltyStampWallet
    {
        if (! $wallet->redeemed_at) {
            throw new \InvalidArgumentException(trans('loyalty::members.stamps.fulfill_not_redeemed'));
        }

        if ($wallet->fulfilled_at) {
            throw new \InvalidArgumentException(trans('loyalty::members.stamps.fulfill_already'));
        }

        $wallet->update([
            'fulfilled_at' => now(),
            'fulfilled_by' => $adminUserId,
        ]);

        return $wallet->fresh(['program', 'user']);
    }


    private function resolveLookupStatus(LoyaltyStampWallet $wallet): string
    {
        if ($wallet->fulfilled_at) {
            return 'fulfilled';
        }

        if ($wallet->redeemed_at) {
            return 'valid';
        }

        if ($wallet->completed_at) {
            return 'pending_customer_redeem';
        }

        return 'in_progress';
    }
}
