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
        private LoyaltyCartService $cartService,
        private LoyaltyConfig $config
    ) {}


    /**
     * Debit wallet for points redeemed on the order.
     *
     * Prefer order columns (already snapshotted at persist). Cart is only a
     * fallback — OrderPlaced also clears the cart, so relying on Cart alone
     * raced ClearCart and left balances unchanged.
     */
    public function captureRedemptionFromCart(Order $order): void
    {
        if (! $order->customer_id) {
            return;
        }

        $points = (int) $order->loyalty_points_redeemed;
        $discount = (float) ($order->getAttributes()['loyalty_discount_amount'] ?? 0);

        if ($points <= 0 && Cart::hasLoyalty()) {
            $points = Cart::loyalty()->points();
            $discount = (float) Cart::loyalty()->value()->amount();
        }

        if ($points <= 0) {
            if (Cart::hasLoyalty()) {
                $this->cartService->remove();
            }

            return;
        }

        if ($discount <= 0) {
            $discount = $this->config->pointsToRm($points);
        }

        $user = User::find($order->customer_id);

        if (! $user) {
            return;
        }

        $wallet = $this->wallets->getOrCreateForUser($user);
        $redeemRef = $order->id . ':redeem';

        $this->wallets->debit(
            $wallet,
            $points,
            TransactionType::REDEEM,
            'order',
            $redeemRef,
            trans('loyalty::messages.redeem_for_order', ['id' => $order->id]),
            ['order_id' => $order->id]
        );

        $order->forceFill([
            'loyalty_points_redeemed' => $points,
            'loyalty_discount_amount' => $discount,
        ])->saveQuietly();

        if (Cart::hasLoyalty()) {
            $this->cartService->remove();
        }
    }


    public function refundRedemption(Order $order): void
    {
        if (! $order->customer_id || $order->loyalty_points_redeemed <= 0) {
            return;
        }

        $user = User::find($order->customer_id);

        if (! $user) {
            return;
        }

        $wallet = $this->wallets->getOrCreateForUser($user);
        $redeemRef = $order->id . ':redeem';
        $refundRef = $order->id . ':redeem_refund';

        if (! $this->wallets->findExistingTransaction($wallet, TransactionType::REDEEM, 'order', $redeemRef)) {
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
