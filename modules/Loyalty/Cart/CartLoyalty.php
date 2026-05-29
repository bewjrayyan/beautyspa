<?php

namespace Modules\Loyalty\Cart;

use JsonSerializable;
use Modules\Support\Money;

class CartLoyalty implements JsonSerializable
{
    public function __construct(
        private $cart,
        private $loyaltyCondition
    ) {}


    public function points(): int
    {
        return (int) $this->conditionAttribute('loyalty_points', 0);
    }


    public function holdId(): ?int
    {
        $holdId = $this->conditionAttribute('hold_id');

        return $holdId ? (int) $holdId : null;
    }


    private function conditionAttribute(string $key, mixed $default = null): mixed
    {
        if (method_exists($this->loyaltyCondition, 'getAttribute')) {
            return $this->loyaltyCondition->getAttribute($key, $default);
        }

        return $this->loyaltyCondition->getAttributes()[$key] ?? $default;
    }


    public function value(): Money
    {
        return Money::inDefaultCurrency(abs($this->calculate()));
    }


    public function toArray(): array
    {
        return [
            'points' => $this->points(),
            'value' => $this->value(),
        ];
    }


    public function jsonSerialize(): array
    {
        return $this->toArray();
    }


    private function calculate(): float
    {
        return (float) $this->loyaltyCondition->getCalculatedValue($this->cart->subTotal()->amount());
    }
}
