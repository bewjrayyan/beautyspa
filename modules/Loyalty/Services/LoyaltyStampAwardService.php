<?php

namespace Modules\Loyalty\Services;

use Illuminate\Support\Facades\DB;
use Modules\Loyalty\Entities\LoyaltyStampEntry;
use Modules\Loyalty\Entities\LoyaltyStampProgram;
use Modules\Loyalty\Entities\LoyaltyStampWallet;
use Modules\Order\Entities\Order;
use Modules\User\Entities\User;

class LoyaltyStampAwardService
{
    public function __construct(
        private StampProgramEligibleProductService $eligibleProducts
    ) {}

    public function awardForOrder(Order $order): void
    {
        if (! $order->customer_id) {
            return;
        }

        // Align with points: only award after the order is completed / paid.
        if ($order->status !== Order::COMPLETED && ! $order->isPaymentPaid()) {
            return;
        }

        $order->loadMissing(['products.product']);

        $programs = LoyaltyStampProgram::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        if ($programs->isEmpty()) {
            return;
        }

        $user = User::find($order->customer_id);

        if (! $user) {
            return;
        }

        foreach ($programs as $program) {
            if (! $this->eligibleProducts->orderEarnsStampVisit($order, $program)) {
                continue;
            }

            $this->awardStamp($user, $program, $order);
        }
    }


    /**
     * Remove stamps previously awarded for an order (cancel / refund).
     * Skips wallets that are already redeemed or fulfilled at the counter.
     */
    public function clawbackForOrder(Order $order): void
    {
        $entries = LoyaltyStampEntry::query()
            ->where('order_id', $order->id)
            ->get();

        if ($entries->isEmpty()) {
            return;
        }

        foreach ($entries as $entry) {
            DB::transaction(function () use ($entry) {
                $wallet = LoyaltyStampWallet::query()
                    ->whereKey($entry->wallet_id)
                    ->lockForUpdate()
                    ->first();

                if (! $wallet || $wallet->redeemed_at || $wallet->fulfilled_at) {
                    return;
                }

                $stampsAdded = max(1, (int) $entry->stamps_added);
                $entry->delete();

                $wallet->decrement('stamps_count', $stampsAdded);
                $wallet->refresh();

                $wallet->loadMissing('program');
                $required = (int) ($wallet->program?->stamps_required ?? 0);

                if ($wallet->completed_at && ($required <= 0 || $wallet->stamps_count < $required)) {
                    $wallet->update(['completed_at' => null]);
                }
            });
        }
    }


    private function awardStamp(User $user, LoyaltyStampProgram $program, Order $order): void
    {
        DB::transaction(function () use ($user, $program, $order) {
            $wallet = $this->resolveActiveWallet($user, $program);

            // One stamp per order per program — even if the prior wallet expired.
            $existing = LoyaltyStampEntry::query()
                ->where('order_id', $order->id)
                ->whereHas('wallet', function ($query) use ($program) {
                    $query->where('program_id', $program->id);
                })
                ->first();

            if ($existing) {
                return;
            }

            LoyaltyStampEntry::create([
                'wallet_id' => $wallet->id,
                'order_id' => $order->id,
                'stamps_added' => 1,
            ]);

            $wallet->increment('stamps_count');

            $wallet->refresh();

            if ($wallet->stamps_count >= $program->stamps_required && ! $wallet->completed_at) {
                $wallet->update(['completed_at' => now()]);
            }
        });
    }


    private function resolveActiveWallet(User $user, LoyaltyStampProgram $program): LoyaltyStampWallet
    {
        $wallet = LoyaltyStampWallet::query()
            ->where('user_id', $user->id)
            ->where('program_id', $program->id)
            ->whereNull('completed_at')
            ->whereNull('redeemed_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('id')
            ->first();

        if ($wallet) {
            return $wallet;
        }

        $startedAt = now();

        return LoyaltyStampWallet::create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'stamps_count' => 0,
            'started_at' => $startedAt,
            'expires_at' => $program->validity_days > 0
                ? $startedAt->copy()->addDays($program->validity_days)
                : null,
        ]);
    }
}
