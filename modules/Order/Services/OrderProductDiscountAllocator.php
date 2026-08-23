<?php

namespace Modules\Order\Services;

use Modules\Order\Entities\Order;
use Modules\Support\Money;

class OrderProductDiscountAllocator
{
    private const PRECISION = 4;


    public function __construct(private OrderPricingBreakdown $pricingBreakdown)
    {
    }


    /**
     * Allocate the order-level coupon and loyalty discounts across product lines.
     *
     * @return array<int, array<string, Money>>
     */
    public function forOrder(Order $order): array
    {
        $discountAmount = $this->pricingBreakdown->totalDiscountAmount($order);

        if ($discountAmount <= 0 || $order->products->isEmpty()) {
            return [];
        }

        $lineAmounts = $order->products
            ->mapWithKeys(fn ($product) => [
                $product->getKey() => max(0, (float) $product->getRawOriginal('line_total')),
            ])
            ->all();

        $allocatedDiscounts = $this->allocate($lineAmounts, $discountAmount);
        $pricing = [];

        foreach ($order->products as $product) {
            $lineDiscount = $allocatedDiscounts[$product->getKey()] ?? 0.0;

            if ($lineDiscount <= 0) {
                continue;
            }

            $quantity = max(1, (int) $product->qty);
            $originalUnitPrice = max(0, (float) $product->getRawOriginal('unit_price'));
            $originalLineTotal = max(0, (float) $product->getRawOriginal('line_total'));

            $pricing[$product->getKey()] = [
                'original_unit_price' => Money::inDefaultCurrency($originalUnitPrice),
                'discounted_unit_price' => Money::inDefaultCurrency(
                    max(0, $originalUnitPrice - ($lineDiscount / $quantity))
                ),
                'original_line_total' => Money::inDefaultCurrency($originalLineTotal),
                'discounted_line_total' => Money::inDefaultCurrency(
                    max(0, $originalLineTotal - $lineDiscount)
                ),
                'savings' => Money::inDefaultCurrency($lineDiscount),
            ];
        }

        return $pricing;
    }


    /**
     * @param array<int|string, float|int> $lineAmounts
     * @return array<int|string, float>
     */
    public function allocate(array $lineAmounts, float $discountAmount): array
    {
        $factor = 10 ** self::PRECISION;
        $lineUnits = [];

        foreach ($lineAmounts as $key => $amount) {
            $units = max(0, (int) round((float) $amount * $factor));

            if ($units > 0) {
                $lineUnits[$key] = $units;
            }
        }

        $grossUnits = array_sum($lineUnits);
        $discountUnits = min(
            $grossUnits,
            max(0, (int) round($discountAmount * $factor))
        );

        if ($grossUnits === 0 || $discountUnits === 0) {
            return [];
        }

        $lastKey = array_key_last($lineUnits);
        $remainingUnits = $discountUnits;
        $allocations = [];

        foreach ($lineUnits as $key => $lineGrossUnits) {
            $shareUnits = $key === $lastKey
                ? $remainingUnits
                : (int) round($discountUnits * ($lineGrossUnits / $grossUnits));
            $shareUnits = min($shareUnits, $remainingUnits, $lineGrossUnits);

            if ($shareUnits > 0) {
                $allocations[$key] = $shareUnits / $factor;
                $remainingUnits -= $shareUnits;
            }
        }

        return $allocations;
    }
}
