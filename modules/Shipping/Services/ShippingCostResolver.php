<?php

namespace Modules\Shipping\Services;

use Illuminate\Support\Collection;
use Modules\Cart\Facades\Cart;
use Modules\Product\Entities\Product;
use Modules\Shipping\Entities\ShippingClass;
use Modules\Support\Money;

class ShippingCostResolver
{
    /**
     * flat_rate: sum of physical line class costs (qty × cost).
     * Missing class falls back to method cost so the store is never undercharged.
     *
     * @param  iterable<mixed>|null  $items  Cart items or test doubles; defaults to Cart::items().
     */
    public function resolve(string $methodName, float|int|string|null $methodCost, ?iterable $items = null): float
    {
        $fallback = (float) ($methodCost ?? 0);

        if ($methodName === 'free_shipping') {
            return 0.0;
        }

        if ($methodName !== 'flat_rate') {
            return $fallback;
        }

        $cartItems = Collection::make($items ?? Cart::items());
        $lines = $this->buildFlatRateLines($cartItems);
        $lines = $this->refreshMissingClassIdsFromDatabase($lines);

        $classIds = collect($lines)
            ->filter(fn (array $line) => ($line['class_cost'] ?? null) === null && ($line['class_id'] ?? null) !== null)
            ->pluck('class_id')
            ->unique()
            ->values()
            ->all();

        $costMap = $classIds === []
            ? []
            : ShippingClass::withoutGlobalScope('active')
                ->whereIn('id', $classIds)
                ->pluck('cost', 'id')
                ->map(fn ($cost) => $cost === null || $cost === '' ? null : (float) $cost)
                ->all();

        foreach ($lines as &$line) {
            if ($line['class_cost'] !== null) {
                continue;
            }

            $classId = $line['class_id'];
            $line['class_cost'] = ($classId !== null && array_key_exists($classId, $costMap))
                ? $costMap[$classId]
                : null;
        }
        unset($line);

        return $this->calculateFlatRateTotal($lines, $fallback);
    }


    public function resolveMoney(string $methodName, float|int|string|null $methodCost, ?iterable $items = null): Money
    {
        return Money::inDefaultCurrency($this->resolve($methodName, $methodCost, $items));
    }


    /**
     * @param  list<array{physical: bool, qty: int, class_id: int|null, class_cost: float|null}>  $lines
     */
    public function calculateFlatRateTotal(array $lines, float $fallback): float
    {
        $total = 0.0;
        $hasPhysical = false;

        foreach ($lines as $line) {
            if (! ($line['physical'] ?? false)) {
                continue;
            }

            $hasPhysical = true;
            $qty = max(1, (int) ($line['qty'] ?? 1));
            $classCost = $line['class_cost'] ?? null;

            if ($classCost === null || $classCost === '') {
                $total += $fallback * $qty;

                continue;
            }

            $total += (float) $classCost * $qty;
        }

        return $hasPhysical ? round($total, 4) : $fallback;
    }



    /**
     * Stale cart products (session) may lack shipping_class_id — refresh in one query.
     *
     * @param  list<array{physical: bool, qty: int, product_id?: int|null, class_id: int|null, class_cost: float|null}>  $lines
     * @return list<array{physical: bool, qty: int, product_id?: int|null, class_id: int|null, class_cost: float|null}>
     */
    private function refreshMissingClassIdsFromDatabase(array $lines): array
    {
        $productIds = [];

        foreach ($lines as $line) {
            if (! ($line['physical'] ?? false)) {
                continue;
            }

            if (($line['class_cost'] ?? null) !== null || ($line['class_id'] ?? null) !== null) {
                continue;
            }

            if (! empty($line['product_id'])) {
                $productIds[] = (int) $line['product_id'];
            }
        }

        if ($productIds === []) {
            return $lines;
        }

        $classIdsByProduct = Product::withoutGlobalScopes()
            ->whereIn('id', array_unique($productIds))
            ->pluck('shipping_class_id', 'id');

        foreach ($lines as &$line) {
            if (($line['class_id'] ?? null) !== null || ($line['class_cost'] ?? null) !== null) {
                continue;
            }

            $productId = $line['product_id'] ?? null;

            if ($productId && $classIdsByProduct->has($productId) && $classIdsByProduct[$productId]) {
                $line['class_id'] = (int) $classIdsByProduct[$productId];
            }
        }
        unset($line);

        return $lines;
    }


    /**
     * @param  Collection<int, mixed>  $cartItems
     * @return list<array{physical: bool, qty: int, class_id: int|null, class_cost: float|null}>
     */
    private function buildFlatRateLines(Collection $cartItems): array
    {
        $lines = [];

        foreach ($cartItems as $item) {
            $product = is_object($item) ? ($item->product ?? null) : null;

            if (! $product || ! is_object($product)) {
                continue;
            }

            $isVirtual = method_exists($product, 'isVirtualTreatment')
                ? (bool) $product->isVirtualTreatment()
                : (bool) ($product->is_virtual ?? false);

            $classCost = null;
            $classId = isset($product->shipping_class_id) ? (int) $product->shipping_class_id ?: null : null;

            if (method_exists($product, 'relationLoaded') && $product->relationLoaded('shippingClass')) {
                $shippingClass = $product->shippingClass;

                if ($shippingClass && isset($shippingClass->cost)) {
                    $classCost = (float) $shippingClass->cost;
                    $classId = isset($shippingClass->id) ? (int) $shippingClass->id : $classId;
                }
            }

            $lines[] = [
                'physical' => ! $isVirtual,
                'qty' => max(1, (int) ($item->qty ?? 1)),
                'product_id' => isset($product->id) ? (int) $product->id : null,
                'class_id' => $classId,
                'class_cost' => $classCost,
            ];
        }

        return $lines;
    }
}
