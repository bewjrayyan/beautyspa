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
    public function awardForOrder(Order $order): void
    {
        if (! $order->customer_id) {
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
            if (! $this->orderQualifies($order, $program)) {
                continue;
            }

            $this->awardStamp($user, $program, $order);
        }
    }


    private function orderQualifies(Order $order, LoyaltyStampProgram $program): bool
    {
        $productIds = array_filter((array) $program->product_ids);

        if ($productIds !== []) {
            return $order->products->contains(
                fn ($line) => in_array((int) $line->product_id, array_map('intval', $productIds), true)
            );
        }

        if ($program->virtual_treatments_only) {
            return $order->products->contains(fn ($line) => (bool) $line->product?->is_virtual);
        }

        return $order->products->isNotEmpty();
    }


    private function awardStamp(User $user, LoyaltyStampProgram $program, Order $order): void
    {
        DB::transaction(function () use ($user, $program, $order) {
            $wallet = $this->resolveActiveWallet($user, $program);

            $existing = LoyaltyStampEntry::query()
                ->where('wallet_id', $wallet->id)
                ->where('order_id', $order->id)
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
