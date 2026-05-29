<?php

namespace Modules\Loyalty\Services;

use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;
use Modules\Loyalty\Enums\TransactionType;
use Modules\Product\Entities\Product;
use Modules\User\Entities\User;

class LoyaltyEarnService
{
    public function __construct(
        private LoyaltyConfig $config,
        private LoyaltyWalletService $wallets,
        private LoyaltyTierService $tiers,
        private LoyaltyNotificationService $notifications,
        private LoyaltyLifetimeSpendService $lifetimeSpend
    ) {}


    public function eligibleAmount(Order $order): float
    {
        return $this->lifetimeSpend->eligibleAmount($order);
    }


    public function calculatePointsForOrder(Order $order, float $multiplier = 1.0): int
    {
        $order->loadMissing('products.product');

        if ($order->products->isEmpty()) {
            return $this->calculatePointsFromOrderTotal($order, $multiplier);
        }

        return $this->calculatePointsFromLineItems($order, $multiplier);
    }


    public function earnFromCompletedOrder(Order $order): void
    {
        if (!$order->customer_id) {
            return;
        }

        if ($order->loyalty_points_earned > 0) {
            return;
        }

        $user = User::find($order->customer_id);

        if (!$user) {
            return;
        }

        $wallet = $this->wallets->getOrCreateForUser($user);
        $wallet->load('tier');

        $tierMultiplier = (float) $wallet->tier->earn_multiplier;
        $points = $this->calculatePointsForOrder($order, $tierMultiplier);

        $eligible = $this->eligibleAmount($order);

        if ($points <= 0 && $eligible <= 0) {
            return;
        }

        if ($points > 0) {
            $expiresAt = now()->addMonths($this->config->pointsExpireMonths());

            $this->wallets->credit(
                $wallet,
                $points,
                TransactionType::EARN,
                'order',
                (string) $order->id . ':earn',
                trans('loyalty::messages.earn_from_order', ['id' => $order->id]),
                [
                    'order_id' => $order->id,
                    'eligible_rm' => $eligible,
                    'per_product' => true,
                ],
                $expiresAt
            );

            $order->update([
                'loyalty_points_earned' => $points,
            ]);

            $this->notifications->notifyPointsEarned($user, $points, $wallet);
        }

        $this->lifetimeSpend->recalculateWallet($wallet);

        $tierChange = $this->tiers->evaluate($wallet->fresh(), 'order_completed');
        $wallet = $wallet->fresh(['tier']);

        if ($tierChange) {
            $this->notifications->notifyTierUpgraded(
                $user,
                $tierChange['from'],
                $tierChange['to']
            );
        }
    }


    public function clawbackFromOrder(Order $order): void
    {
        if (!$order->customer_id || $order->loyalty_points_earned <= 0) {
            return;
        }

        $user = User::find($order->customer_id);

        if (!$user) {
            return;
        }

        $wallet = $this->wallets->getOrCreateForUser($user);

        $earnRef = (string) $order->id . ':earn';
        $clawbackRef = (string) $order->id . ':clawback';

        if ($this->wallets->findExistingTransaction($wallet, TransactionType::CLAWBACK, 'order', $clawbackRef)) {
            return;
        }

        if (!$this->wallets->findExistingTransaction($wallet, TransactionType::EARN, 'order', $earnRef)) {
            return;
        }

        $points = (int) $order->loyalty_points_earned;
        $eligible = $this->eligibleAmount($order);

        $this->wallets->debit(
            $wallet,
            $points,
            TransactionType::CLAWBACK,
            'order',
            $clawbackRef,
            trans('loyalty::messages.clawback_from_order', ['id' => $order->id]),
            ['order_id' => $order->id]
        );

        $this->lifetimeSpend->recalculateWallet($wallet);
        $this->tiers->evaluate($wallet->fresh(), 'order_clawback');

        $order->update([
            'loyalty_points_earned' => 0,
        ]);
    }


    private function calculatePointsFromOrderTotal(Order $order, float $tierMultiplier): int
    {
        $eligible = $this->eligibleAmount($order);

        return (int) floor($eligible * $this->config->earnRatePerRm() * $tierMultiplier);
    }


    private function calculatePointsFromLineItems(Order $order, float $tierMultiplier): int
    {
        $eligibleTotal = $this->eligibleAmount($order);

        if ($eligibleTotal <= 0) {
            return 0;
        }

        $subTotal = (float) $order->sub_total->amount();

        if ($subTotal <= 0) {
            return $this->calculatePointsFromOrderTotal($order, $tierMultiplier);
        }

        $earnRate = $this->config->earnRatePerRm();
        $totalPoints = 0;

        foreach ($order->products as $orderProduct) {
            $totalPoints += $this->calculatePointsForLineItem(
                $orderProduct,
                $eligibleTotal,
                $subTotal,
                $earnRate,
                $tierMultiplier
            );
        }

        return (int) $totalPoints;
    }


    private function calculatePointsForLineItem(
        OrderProduct $orderProduct,
        float $eligibleTotal,
        float $subTotal,
        float $earnRate,
        float $tierMultiplier
    ): int {
        $lineTotal = (float) $orderProduct->line_total->amount();
        $lineEligible = ($lineTotal / $subTotal) * $eligibleTotal;

        $product = $orderProduct->product;

        if (!$product) {
            return (int) floor($lineEligible * $earnRate * $tierMultiplier);
        }

        $productMultiplier = $this->productEarnMultiplier($product);
        $spendPoints = (int) floor(
            $lineEligible * $earnRate * $tierMultiplier * $productMultiplier
        );
        $bonusPoints = (int) ($product->loyalty_bonus_points ?? 0) * (int) $orderProduct->qty;

        return $spendPoints + $bonusPoints;
    }


    private function productEarnMultiplier(Product $product): float
    {
        $multiplier = (float) ($product->loyalty_earn_multiplier ?? 1);

        return $multiplier > 0 ? $multiplier : 1.0;
    }
}
