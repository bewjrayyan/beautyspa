<?php

namespace Modules\Shipping\Services;

use Modules\Cart\Facades\Cart;
use Modules\Support\Money;
use Modules\Shipping\Entities\ShippingClass;
use Illuminate\Support\Facades\DB;

class ShippingCostResolver
{
    /**
     * flat_rate: sum of physical line class costs (qty x cost).
     * Missing class falls back to method cost so the store is never undercharged.
     */
    public function resolve(string $methodName, float|int|string|null $methodCost): float
    {
        $fallback = (float) ($methodCost ?? 0);

        if ($methodName === 'free_shipping') {
            return 0.0;
        }

        if ($methodName !== 'flat_rate') {
            return $fallback;
        }

        $total = 0.0;
        $hasPhysical = false;

        foreach (Cart::items() as $item) {
            $product = $item->product ?? null;

            if (! $product || $product->isVirtualTreatment()) {
                continue;
            }

            $hasPhysical = true;
            $qty = max(1, (int) $item->qty);
            $classId = $product->shipping_class_id
                ?? DB::table('products')->where('id', $product->id)->value('shipping_class_id');
            $classCost = null;

            if ($classId) {
                $classCost = ShippingClass::withoutGlobalScope('active')
                    ->whereKey($classId)
                    ->value('cost');
            }

            if ($classCost === null || $classCost === '') {
                $total += $fallback * $qty;

                continue;
            }

            $total += (float) $classCost * $qty;
        }

        return $hasPhysical ? round($total, 4) : $fallback;
    }


    public function resolveMoney(string $methodName, float|int|string|null $methodCost): Money
    {
        return Money::inDefaultCurrency($this->resolve($methodName, $methodCost));
    }
}
