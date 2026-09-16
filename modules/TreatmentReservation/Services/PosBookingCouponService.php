<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Collection;
use Modules\Coupon\Entities\Coupon;
use Modules\Product\Entities\Product;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Entities\User;

class PosBookingCouponService
{
    public function __construct(private ManualBookingProductSelectionValidator $selections)
    {
    }

    public function quoteItems(string $code, User $customer, array $items): array
    {
        $lines = collect($items)->map(function (array $item) {
            $selection = $this->selections->validateAndResolve($item);

            return (object) [
                'product' => $selection['product'],
                'total' => $selection['total'],
            ];
        });

        return $this->quote($code, $customer, $lines);
    }

    public function quote(string $code, User $customer, Collection $lines, bool $lock = false): array
    {
        $coupon = Coupon::query()
            ->when($lock, fn ($query) => $query->lockForUpdate())
            ->whereRaw('UPPER(`code`) = ?', [mb_strtoupper(trim($code))])
            ->first();

        if (! $coupon) {
            throw new \InvalidArgumentException('Coupon code is invalid or inactive.');
        }

        if ($coupon->invalid()) {
            throw new \InvalidArgumentException('This coupon is not valid today.');
        }

        if ($coupon->usageLimitReached($customer->email)) {
            throw new \InvalidArgumentException('This coupon has reached its usage limit.');
        }

        if ($coupon->usage_limit_per_customer !== null) {
            $posUses = TreatmentBooking::query()
                ->where('coupon_id', $coupon->id)
                ->where('customer_id', $customer->id)
                ->count();

            if ($posUses >= (int) $coupon->usage_limit_per_customer) {
                throw new \InvalidArgumentException('This customer has already used this coupon.');
            }
        }

        $products = $lines->pluck('product')->filter()->values();
        $this->assertScope($coupon, $products);

        $subtotal = round((float) $lines->sum('total'), 2);
        $minimum = $coupon->minimum_spend?->amount();
        $maximum = $coupon->maximum_spend?->amount();

        if ($minimum !== null && $subtotal < (float) $minimum) {
            throw new \InvalidArgumentException('This coupon requires a higher service total.');
        }
        if ($maximum !== null && $subtotal > (float) $maximum) {
            throw new \InvalidArgumentException('This coupon cannot be used above its maximum spend.');
        }

        $value = (float) ($coupon->is_percent ? $coupon->getAttributes()['value'] : $coupon->value->amount());
        $discount = $coupon->is_percent ? round($subtotal * ($value / 100), 2) : $value;

        return [
            'coupon' => $coupon,
            'code' => $coupon->code,
            'subtotal' => $subtotal,
            'discount' => min($subtotal, max(0, round($discount, 2))),
        ];
    }

    private function assertScope(Coupon $coupon, Collection $products): void
    {
        $coupon->loadMissing(['products', 'excludeProducts', 'categories', 'excludeCategories']);
        $productIds = $products->pluck('id')->map(fn ($id) => (int) $id);
        $categoryIds = $products->flatMap(fn (Product $product) => $product->categories->pluck('id'))->map(fn ($id) => (int) $id);

        if ($coupon->products->isNotEmpty() && $coupon->products->pluck('id')->intersect($productIds)->isEmpty()) {
            throw new \InvalidArgumentException('This coupon is not applicable to the selected treatment.');
        }
        if ($coupon->categories->isNotEmpty() && $coupon->categories->pluck('id')->intersect($categoryIds)->isEmpty()) {
            throw new \InvalidArgumentException('This coupon is not applicable to the selected treatment category.');
        }
        if ($coupon->excludeProducts->pluck('id')->intersect($productIds)->isNotEmpty()
            || $coupon->excludeCategories->pluck('id')->intersect($categoryIds)->isNotEmpty()) {
            throw new \InvalidArgumentException('This coupon excludes one of the selected treatments.');
        }
    }
}
