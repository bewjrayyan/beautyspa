<?php

namespace Modules\Loyalty\Services;

use Modules\Cart\CartCondition;
use Modules\Cart\Facades\Cart;
use Modules\User\Entities\User;
use Modules\Loyalty\Entities\LoyaltyRedemptionHold;
use Darryldecode\Cart\Exceptions\InvalidConditionException;
use Modules\Loyalty\Exceptions\InvalidLoyaltyRedemptionException;

class LoyaltyCartService
{
    public function __construct(
        private LoyaltyConfig $config,
        private LoyaltyWalletService $wallets,
        private LoyaltyRedemptionService $redemption
    ) {}


    public function cartId(): string
    {
        return session()->getId();
    }


    public function eligibleCartTotalRm(): float
    {
        $cart = Cart::instance();

        $total = $cart->subTotal()->amount()
            + $cart->shippingCost()->amount()
            - $cart->coupon()->value()->amount();

        return max(0, $total);
    }


    public function quoteForUser(User $user, ?int $requestedPoints = null): array
    {
        $wallet = $this->wallets->getOrCreateForUser($user);

        return $this->redemption->quote(
            $wallet,
            $this->eligibleCartTotalRm(),
            $requestedPoints
        );
    }


    /**
     * @throws InvalidLoyaltyRedemptionException
     * @throws InvalidConditionException
     */
    public function apply(User $user, ?int $requestedPoints = null): void
    {
        if (Cart::isEmpty()) {
            throw new InvalidLoyaltyRedemptionException(trans('loyalty::checkout.cart_empty'));
        }

        $this->assertCanCombineWithCoupon();

        $wallet = $this->wallets->getOrCreateForUser($user);
        $quote = $this->redemption->quote(
            $wallet,
            $this->eligibleCartTotalRm(),
            $requestedPoints
        );

        if ($quote['points'] <= 0 || $quote['discount_rm'] <= 0) {
            throw new InvalidLoyaltyRedemptionException(trans('loyalty::checkout.insufficient_points'));
        }

        $this->remove();

        $hold = $this->redemption->createHold(
            $wallet,
            $this->cartId(),
            $quote['points'],
            $quote['discount_rm']
        );

        Cart::instance()->condition(
            new CartCondition([
                'name' => 'loyalty_points',
                'type' => 'loyalty',
                'target' => 'total',
                'value' => '-' . $quote['discount_rm'],
                'order' => 2,
                'attributes' => [
                    'loyalty_points' => $quote['points'],
                    'hold_id' => $hold->id,
                ],
            ])
        );
    }


    public function remove(): void
    {
        if (!Cart::hasLoyalty()) {
            return;
        }

        Cart::removeLoyalty();

        if (auth()->check()) {
            $this->redemption->releaseHold(auth()->id(), $this->cartId());
        }
    }


    public function assertCanCombineWithCoupon(): void
    {
        if ($this->config->allowWithCoupon()) {
            return;
        }

        if (Cart::hasCoupon()) {
            throw new InvalidLoyaltyRedemptionException(trans('loyalty::checkout.cannot_combine_coupon'));
        }
    }


    public function refreshHoldIfExpired(): void
    {
        if (!auth()->check() || !Cart::hasLoyalty()) {
            return;
        }

        $holdId = Cart::loyalty()->holdId();

        if (!$holdId) {
            return;
        }

        $hold = LoyaltyRedemptionHold::find($holdId);

        if (!$hold || $hold->expires_at->isPast()) {
            $this->remove();
        }
    }
}
