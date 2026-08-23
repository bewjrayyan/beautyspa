<?php

namespace Tests\Unit\Order;

use Modules\Order\Services\OrderPricingBreakdown;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OrderPricingBreakdownTest extends TestCase
{
    #[Test]
    public function it_recovers_an_unstored_discount_from_the_order_total(): void
    {
        $result = (new OrderPricingBreakdown())->reconcile(
            subtotal: 4298.00,
            shipping: 0,
            tax: 0,
            storedDiscount: 0,
            loyaltyDiscount: 0,
            total: 3855.40,
            loyaltyEnabled: true,
            loyaltyPointsRedeemed: 0,
        );

        $this->assertSame(442.60, $result['inferred_other_discount']);
        $this->assertSame(0.0, $result['inferred_loyalty_discount']);
        $this->assertSame(0.0, $result['processing_fee']);
    }

    #[Test]
    public function it_classifies_a_positive_balance_gap_as_a_payment_fee(): void
    {
        $result = (new OrderPricingBreakdown())->reconcile(
            subtotal: 100.00,
            shipping: 0,
            tax: 0,
            storedDiscount: 0,
            loyaltyDiscount: 0,
            total: 102.00,
            loyaltyEnabled: false,
            loyaltyPointsRedeemed: 0,
        );

        $this->assertSame(2.0, $result['processing_fee']);
        $this->assertSame(0.0, $result['inferred_other_discount']);
    }
}
