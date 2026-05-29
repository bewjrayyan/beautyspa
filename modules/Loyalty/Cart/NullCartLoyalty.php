<?php

namespace Modules\Loyalty\Cart;

use Modules\Support\Money;

class NullCartLoyalty
{
    public function points(): int
    {
        return 0;
    }


    public function holdId(): ?int
    {
        return null;
    }


    public function value(): Money
    {
        return Money::inDefaultCurrency(0);
    }


    public function toArray(): array
    {
        return [
            'points' => 0,
            'value' => Money::inDefaultCurrency(0),
        ];
    }


    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
