<?php

namespace Modules\FlashSale\Services;

use Modules\FlashSale\Entities\FlashSaleProduct;
use Modules\Product\Entities\Product;

class FlashSaleProductSupport
{
    /**
     * Virtual/treatment products use flash-sale qty as promotion slots, not warehouse stock.
     * Qty 0 means unlimited availability for virtual treatments.
     */
    public static function hasUnlimitedPromotionQty(Product $product, ?FlashSaleProduct $pivot = null): bool
    {
        if (! $product->isVirtualTreatment()) {
            return false;
        }

        $qty = (int) ($pivot->qty ?? 0);

        return $qty <= 0;
    }


    public static function remainingQty(Product $product, FlashSaleProduct $pivot): int
    {
        if (self::hasUnlimitedPromotionQty($product, $pivot)) {
            return PHP_INT_MAX;
        }

        return max(0, (int) $pivot->qty - (int) $pivot->sold);
    }


    public static function defaultPromotionQty(Product $product): int
    {
        return $product->isVirtualTreatment() ? 0 : 1;
    }
}
