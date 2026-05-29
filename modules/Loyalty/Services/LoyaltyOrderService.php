<?php

namespace Modules\Loyalty\Services;

use Modules\Cart\Facades\Cart;
use Modules\Order\Entities\Order;
use Modules\Loyalty\Enums\TransactionType;
use Modules\User\Entities\User;

class LoyaltyOrderService
{
    public function __construct(
        private LoyaltyWalletService $wallets,
        private LoyaltyCartService $cartService
    ) {}


    public function captureRedemptionFromCart(Order $order): void
    {
        if (!Cart::hasLoyalty() || !$order->customer_id) {
            return;
        }

        $points = Cart::loyalty()->points();
        $discount = Cart::loyalty()->value()->amount();

        if ($points <= 0) {
            return;
        }

        $user = User::find($order->customer_id);

        if (!$user) {
            return;
        }

        $wallet = $this->wallets->getOrCreateForUser($user);

        $this->wallets->debit(
            $wallet,
            $points,
            TransactionType::REDEEM,
            'order',
            $order->id . ':redeem',
            trans('loyalty::messages.redeem_for_order', ['id' => $order->id]),
            ['order_id' => $order->id]
        );

        if ($order->loyalty_points_redeemed <= 0) {
            $order->update([
                'loyalty_points_redeemed' => $points,
                'loyalty_discount_amount' => $discount,
            ]);
        }

        $this->cartService->remove();
    }


    public function refundRedemption(Order $order): void
    {
        if (!$order->customer_id || $order->loyalty_points_redeemed <= 0) {
            return;
        }

        $user = User::find($order->customer_id);

        if (!$user) {
            return;
        }

        $wallet = $this->wallets->getOrCreateForUser($user);
        $redeemRef = $order->id . ':redeem';
        $refundRef = $order->id . ':redeem_refund';

        if (!$this->wallets->findExistingTransaction($wallet, TransactionType::REDEEM, 'order', $redeemRef)) {
            return;
        }

        if ($this->wallets->findExistingTransaction($wallet, TransactionType::ADJUST, 'order', $refundRef)) {
            return;
        }

        $points = (int) $order->loyalty_points_redeemed;

        $this->wallets->credit(
            $wallet,
            $points,
            TransactionType::ADJUST,
            'order',
            $refundRef,
            trans('loyalty::messages.refund_redeem_for_order', ['id' => $order->id]),
            ['order_id' => $order->id]
        );

        $order->update([
            'loyalty_points_redeemed' => 0,
            'loyalty_discount_amount' => 0,
        ]);
    }
}
