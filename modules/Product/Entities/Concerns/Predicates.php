<?php

namespace Modules\Product\Entities\Concerns;

trait Predicates
{
    /**
     * Is this Product purchased by the current user?
     *
     * @return bool
     */
    public function purchasedByUser(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return \Modules\Order\Entities\Order::query()
            ->where('customer_id', auth()->id())
            ->where('status', \Modules\Order\Entities\Order::COMPLETED)
            ->whereHas('products', function ($query) {
                $query->where('product_id', $this->getKey());
            })
            ->exists();
    }


    public function hasAnyVariation()
    {
        return $this->getAttribute('variations')->isNotEmpty();
    }


    public function hasAnyVariants(): bool
    {
        return $this->getAttribute('variants')->isNotEmpty();
    }


    public function hasAnyOption(): bool
    {
        return $this->getAttribute('options')->isNotEmpty();
    }


    public function hasAnyAttribute(): bool
    {
        return $this->getAttribute('attributes')->isNotEmpty();
    }
}
