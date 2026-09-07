<?php

namespace Modules\Shipping;

use JsonSerializable;
use Modules\Support\Money;
use Modules\Cart\Facades\Cart;
use Modules\Shipping\Services\ShippingCostResolver;

class Method implements JsonSerializable
{
    public $name;
    public $label;

    /**
     * Configured method cost from settings (fallback / pickup fee).
     *
     * @var float|int|string
     */
    private $configuredCost;


    public function __construct($name, $label, $cost)
    {
        $this->name = $name;
        $this->label = $label;
        $this->configuredCost = $cost;
    }


    public function __get($key)
    {
        if ($key === 'cost') {
            return $this->cost();
        }

        return null;
    }


    public function __isset($key)
    {
        return $key === 'cost';
    }


    public function cost(): Money
    {
        return app(ShippingCostResolver::class)->resolveMoney(
            $this->name,
            $this->configuredCost
        );
    }


    public function available()
    {
        if ($this->name !== 'free_shipping') {
            return true;
        }

        return $this->freeShippingMethodIsAvailable();
    }


    private function freeShippingMethodIsAvailable()
    {
        $minimumAmount = Money::inDefaultCurrency(setting('free_shipping_min_amount'));

        return Cart::subTotal()->greaterThanOrEqual($minimumAmount);
    }


    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'cost' => $this->cost(),
        ];
    }
}
