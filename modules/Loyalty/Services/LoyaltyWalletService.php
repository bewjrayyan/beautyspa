<?php

namespace Modules\Loyalty\Services;

use Illuminate\Support\Facades\DB;
use Modules\Loyalty\Entities\LoyaltyTier;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Loyalty\Entities\LoyaltyTransaction;
use Modules\Loyalty\Enums\TransactionType;
use Modules\User\Entities\User;

class LoyaltyWalletService
{
    public function __construct(private LoyaltyConfig $config) {}


    public function getOrCreateForUser(User $user): LoyaltyWallet
    {
        $wallet = LoyaltyWallet::where('user_id', $user->id)->first();

        if ($wallet) {
            return $wallet->load('tier');
        }

        $tier = LoyaltyTier::defaultTier();

        return LoyaltyWallet::create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'balance' => 0,
            'lifetime_spend' => 0,
            'tier_assigned_at' => now(),
        ])->load('tier');
    }


    public function findExistingTransaction(
        LoyaltyWallet $wallet,
        string $type,
        string $referenceType,
        string $referenceId
    ): ?LoyaltyTransaction {
        return LoyaltyTransaction::where('wallet_id', $wallet->id)
            ->where('type', $type)
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->first();
    }


    public function credit(
        LoyaltyWallet $wallet,
        int $points,
        string $type,
        string $referenceType,
        string $referenceId,
        ?string $description = null,
        ?array $meta = null,
        ?\DateTimeInterface $expiresAt = null
    ): LoyaltyTransaction {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Credit points must be positive.');
        }

        $existing = $this->findExistingTransaction($wallet, $type, $referenceType, $referenceId);

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use (
            $wallet,
            $points,
            $type,
            $referenceType,
            $referenceId,
            $description,
            $meta,
            $expiresAt
        ) {
            $locked = LoyaltyWallet::where('id', $wallet->id)->lockForUpdate()->first();
            $locked->balance += $points;
            $locked->save();

            return LoyaltyTransaction::create([
                'wallet_id' => $locked->id,
                'type' => $type,
                'points' => $points,
                'balance_after' => $locked->balance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'meta' => $meta,
                'expires_at' => $expiresAt,
                'created_at' => now(),
            ]);
        });
    }


    public function debit(
        LoyaltyWallet $wallet,
        int $points,
        string $type,
        string $referenceType,
        string $referenceId,
        ?string $description = null,
        ?array $meta = null
    ): LoyaltyTransaction {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Debit points must be positive.');
        }

        $existing = $this->findExistingTransaction($wallet, $type, $referenceType, $referenceId);

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use (
            $wallet,
            $points,
            $type,
            $referenceType,
            $referenceId,
            $description,
            $meta
        ) {
            $locked = LoyaltyWallet::where('id', $wallet->id)->lockForUpdate()->first();
            $debitPoints = min($points, $locked->balance);
            $locked->balance -= $debitPoints;
            $locked->save();

            return LoyaltyTransaction::create([
                'wallet_id' => $locked->id,
                'type' => $type,
                'points' => -$debitPoints,
                'balance_after' => $locked->balance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'meta' => $meta,
                'created_at' => now(),
            ]);
        });
    }


    public function adjust(
        LoyaltyWallet $wallet,
        int $signedPoints,
        string $referenceId,
        string $description,
        array $meta = []
    ): LoyaltyTransaction {
        $referenceType = 'admin';

        if ($signedPoints > 0) {
            return $this->credit(
                $wallet,
                $signedPoints,
                TransactionType::ADJUST,
                $referenceType,
                $referenceId,
                $description,
                $meta
            );
        }

        return $this->debit(
            $wallet,
            abs($signedPoints),
            TransactionType::ADJUST,
            $referenceType,
            $referenceId,
            $description,
            $meta
        );
    }


    public function addLifetimeSpend(LoyaltyWallet $wallet, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $wallet->increment('lifetime_spend', $amount);
    }


    public function subtractLifetimeSpend(LoyaltyWallet $wallet, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $wallet->update([
            'lifetime_spend' => max(0, $wallet->lifetime_spend - $amount),
        ]);
    }
}
