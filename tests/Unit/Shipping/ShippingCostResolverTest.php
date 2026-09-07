<?php

namespace Tests\Unit\Shipping;

use Modules\Shipping\Services\ShippingCostResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippingCostResolverTest extends TestCase
{
    private ShippingCostResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ShippingCostResolver();
    }

    #[Test]
    public function free_shipping_is_always_zero(): void
    {
        $this->assertSame(0.0, $this->resolver->resolve('free_shipping', 8, []));
    }

    #[Test]
    public function non_flat_rate_uses_configured_method_cost(): void
    {
        $this->assertSame(5.0, $this->resolver->resolve('local_pickup', 5, []));
    }

    #[Test]
    public function flat_rate_sums_class_cost_times_quantity(): void
    {
        $total = $this->resolver->calculateFlatRateTotal([
            ['physical' => true, 'qty' => 2, 'class_cost' => 8.0],
            ['physical' => true, 'qty' => 1, 'class_cost' => 15.0],
            ['physical' => false, 'qty' => 3, 'class_cost' => 99.0],
        ], 8.0);

        $this->assertSame(31.0, $total);
    }

    #[Test]
    public function missing_class_falls_back_to_method_cost_per_qty(): void
    {
        $total = $this->resolver->calculateFlatRateTotal([
            ['physical' => true, 'qty' => 3, 'class_cost' => null],
        ], 8.0);

        $this->assertSame(24.0, $total);
    }

    #[Test]
    public function cart_with_only_virtual_items_uses_fallback(): void
    {
        $total = $this->resolver->calculateFlatRateTotal([
            ['physical' => false, 'qty' => 2, 'class_cost' => 8.0],
        ], 8.0);

        $this->assertSame(8.0, $total);
    }

    #[Test]
    public function resolve_flat_rate_from_item_doubles_with_loaded_class(): void
    {
        $shippingClass = (object) ['id' => 1, 'cost' => 5.5];
        $product = new class ($shippingClass) {
            public $shipping_class_id = 1;
            public $shippingClass;

            public function __construct($shippingClass)
            {
                $this->shippingClass = $shippingClass;
            }

            public function isVirtualTreatment(): bool
            {
                return false;
            }

            public function relationLoaded(string $relation): bool
            {
                return $relation === 'shippingClass';
            }
        };

        $item = (object) ['qty' => 2, 'product' => $product];

        $this->assertSame(11.0, $this->resolver->resolve('flat_rate', 8, [$item]));
    }
}
