<?php

namespace Tests\Unit\Checkout;

use Modules\Checkout\Services\CheckoutPaymentFinalizer;
use Modules\Order\Entities\Order;
use Modules\Payment\HasTransactionReference;
use Modules\Transaction\Entities\Transaction;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class CheckoutPaymentFinalizerTest extends TestCase
{
    #[Test]
    public function only_an_exact_paid_transaction_replay_is_idempotent(): void
    {
        $order = new Order();
        $order->setRawAttributes([
            'payment_status' => Order::PAYMENT_PAID,
            'payment_method' => 'chip',
        ], true);

        $transaction = new Transaction();
        $transaction->setRawAttributes(['transaction_id' => 'txn-123'], true);
        $order->setRelation('transaction', $transaction);

        $matching = new class implements HasTransactionReference {
            public function getTransactionReference(): string
            {
                return 'txn-123';
            }
        };
        $different = new class implements HasTransactionReference {
            public function getTransactionReference(): string
            {
                return 'txn-other';
            }
        };

        $method = new ReflectionMethod(CheckoutPaymentFinalizer::class, 'isMatchingPaidReplay');
        $service = new CheckoutPaymentFinalizer();

        $this->assertTrue($method->invoke($service, $order, 'chip', $matching));
        $this->assertFalse($method->invoke($service, $order, 'bank_transfer', $matching));
        $this->assertFalse($method->invoke($service, $order, 'chip', $different));

        $order->payment_status = Order::PAYMENT_PENDING;
        $this->assertFalse($method->invoke($service, $order, 'chip', $matching));
    }
}
