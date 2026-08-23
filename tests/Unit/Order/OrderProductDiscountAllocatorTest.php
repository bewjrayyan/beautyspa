<?php

namespace Tests\Unit\Order;

use Modules\Order\Services\OrderProductDiscountAllocator;
use Modules\Order\Services\OrderPricingBreakdown;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OrderProductDiscountAllocatorTest extends TestCase
{
    #[Test]
    public function it_allocates_the_exact_discount_proportionally_across_products(): void
    {
        $allocations = $this->allocator()->allocate([
            11 => 1099.00,
            22 => 3199.00,
        ], 442.60);

        $this->assertSame(442.60, round(array_sum($allocations), 2));
        $this->assertGreaterThan(0, $allocations[11]);
        $this->assertGreaterThan($allocations[11], $allocations[22]);
    }

    #[Test]
    public function it_never_allocates_more_than_the_product_total(): void
    {
        $allocations = $this->allocator()->allocate([
            11 => 10.00,
            22 => 20.00,
        ], 100.00);

        $this->assertEquals(30.00, array_sum($allocations));
        $this->assertEquals(10.00, $allocations[11]);
        $this->assertEquals(20.00, $allocations[22]);
    }

    #[Test]
    public function it_returns_no_allocations_without_a_real_discount(): void
    {
        $allocator = $this->allocator();

        $this->assertSame([], $allocator->allocate([11 => 10.00], 0));
        $this->assertSame([], $allocator->allocate([], 10.00));
    }


    private function allocator(): OrderProductDiscountAllocator
    {
        return new OrderProductDiscountAllocator(new OrderPricingBreakdown());
    }
}
