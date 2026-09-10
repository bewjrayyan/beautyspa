<?php

namespace Tests\Feature;

use Modules\Lead\Services\CentralPaymentService;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeadCentralPaymentProofTest extends TestCase
{
    private function order(?string $mime): Order
    {
        $order = new Order();
        $order->setRawAttributes(['id' => 100, 'payment_proof_file_id' => $mime ? 7 : null, 'total' => 440, 'payment_method' => 'bank_transfer', 'payment_status' => 'processing', 'status' => 'pending'], true);
        foreach (['spaBranch', 'beautician', 'transaction'] as $relation) {
            $order->setRelation($relation, null);
        }
        $file = $mime ? new File(['id' => 7, 'filename' => 'receipt', 'mime' => $mime, 'extension' => '', 'disk' => 'private', 'path' => 'private/payment-proofs/secret']) : null;
        $order->setRelation('paymentProof', $file);

        return $order;
    }

    #[Test]
    public function receipt_metadata_is_not_exposed_without_order_permission(): void
    {
        $payload = app(CentralPaymentService::class)->toArray($this->order('image/png'));
        $this->assertTrue($payload['has_proof']);
        $this->assertNull($payload['proof']);
    }

    #[Test]
    public function permitted_receipts_use_expiring_signed_urls_and_correct_preview_types(): void
    {
        foreach (['image/png' => 'image', 'application/pdf' => 'pdf', 'text/html' => 'file'] as $mime => $kind) {
            $proof = app(CentralPaymentService::class)->toArray($this->order($mime), true)['proof'];
            $this->assertSame($kind, $proof['kind']);
            $this->assertStringContainsString('signature=', $proof['url']);
            $this->assertStringContainsString('expires=', $proof['url']);
            $this->assertStringNotContainsString('secret', $proof['url']);
        }
    }

    #[Test]
    public function missing_receipts_have_no_preview_url(): void
    {
        $payload = app(CentralPaymentService::class)->toArray($this->order(null), true);
        $this->assertFalse($payload['has_proof']);
        $this->assertNull($payload['proof']);
    }

    #[Test]
    public function checklist_marks_only_saved_evidence_as_completed(): void
    {
        $service = app(CentralPaymentService::class);
        $order = $this->order(null);
        $checks = $service->toArray($order)['checklist'];
        $this->assertSame(['identity' => 'pending', 'invoice' => 'completed', 'method' => 'completed', 'ref' => 'pending', 'proof' => 'pending', 'status' => 'pending'], $checks);

        $order = $this->order('image/png');
        $order->setRawAttributes($order->getAttributes() + ['customer_first_name' => 'Test', 'customer_phone' => '60123456789'], true);
        $order->setRelation('transaction', new \Modules\Transaction\Entities\Transaction(['transaction_id' => 'TEST-REF']));
        $payload = $service->toArray($order);
        $this->assertSame('completed', $payload['checklist']['identity']);
        $this->assertSame('completed', $payload['checklist']['ref']);
        $this->assertSame('completed', $payload['checklist']['proof']);
        $this->assertSame('pending', $payload['checklist']['status']);
        $this->assertSame(trans('lead::central.payments.stage_reviewing'), $payload['accountant_stage']);

        foreach (['paid', 'canceled', 'refunded', 'pending'] as $status) {
            $order->payment_status = $status;
            $payload = $service->toArray($order);
            $this->assertSame($status === 'paid' ? 'completed' : 'pending', $payload['checklist']['status']);
            $this->assertNotSame(trans('lead::central.payments.stage_bank_checked'), $payload['accountant_stage']);
        }
    }

    #[Test]
    public function optional_evidence_is_not_applicable_and_missing_data_is_not_complete(): void
    {
        $order = $this->order(null);
        $order->setRawAttributes(array_replace($order->getAttributes(), ['payment_method' => 'cash', 'total' => null]), true);
        $checks = app(CentralPaymentService::class)->toArray($order)['checklist'];
        $this->assertSame('not_applicable', $checks['ref']);
        $this->assertSame('not_applicable', $checks['proof']);
        $this->assertSame('pending', $checks['invoice']);
        $order->setRawAttributes(array_replace($order->getAttributes(), ['payment_method' => '']), true);
        $checks = app(CentralPaymentService::class)->toArray($order)['checklist'];
        $this->assertSame('pending', $checks['method']);
        $this->assertSame('pending', $checks['ref']);
        $this->assertSame('pending', $checks['proof']);
    }
}
