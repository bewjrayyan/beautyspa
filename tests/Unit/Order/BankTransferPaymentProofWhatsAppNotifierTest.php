<?php

namespace Tests\Unit\Order;

use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use Modules\Order\Services\BankTransferPaymentProofWhatsAppMessage;
use Modules\Order\Services\BankTransferPaymentProofWhatsAppNotifier;
use Modules\Order\Services\OrderPaymentProofPublicUrlService;
use Modules\Order\Services\OrderWhatsAppPdfService;
use Modules\User\Services\OneSenderWhatsAppService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BankTransferPaymentProofWhatsAppNotifierTest extends TestCase
{
    #[Test]
    public function it_sends_the_payment_proof_and_receipt_pdf_immediately_to_the_group(): void
    {
        $oneSender = new class extends OneSenderWhatsAppService {
            /** @var list<array<string, mixed>> */
            public array $messages = [];

            public function sendImageToGroup(
                string $groupId,
                string $imageUrl,
                ?string $caption = null,
                array $context = [],
            ): bool {
                $this->messages[] = compact('groupId', 'imageUrl', 'caption', 'context');

                return true;
            }

            public function sendDocumentToGroup(
                string $groupId,
                string $documentUrl,
                string $filename,
                ?string $caption = null,
                array $context = [],
            ): bool {
                $this->messages[] = compact('groupId', 'documentUrl', 'filename', 'caption', 'context');

                return true;
            }
        };
        $messages = new class extends BankTransferPaymentProofWhatsAppMessage {
            public function build(Order $order): string
            {
                return 'Payment proof';
            }
        };
        $proofUrls = new class extends OrderPaymentProofPublicUrlService {
            public function whatsAppMediaUrl(File $proof, Order $order): string
            {
                return 'https://example.test/payment-proof.webp';
            }
        };
        $pdf = new class extends OrderWhatsAppPdfService {
            public function receiptPublicUrl(Order $order): string
            {
                return 'https://example.test/receipt.pdf';
            }
        };
        $notifier = new class($oneSender, $messages, $proofUrls, $pdf) extends BankTransferPaymentProofWhatsAppNotifier {
            public function canSend(Order $order): bool
            {
                return true;
            }
        };

        $proof = new File();
        $proof->setRawAttributes([
            'id' => 9,
            'filename' => 'proof.webp',
            'extension' => 'webp',
            'mime' => 'image/webp',
        ], true);

        $order = new Order();
        $order->setRawAttributes([
            'id' => 77,
            'payment_proof_file_id' => 9,
        ], true);
        $order->setRelation('paymentProof', $proof);

        $notifier->send($order);

        $this->assertCount(2, $oneSender->messages);
        $this->assertSame('https://example.test/payment-proof.webp', $oneSender->messages[0]['imageUrl']);
        $this->assertSame('Payment proof', $oneSender->messages[0]['caption']);
        $this->assertTrue($oneSender->messages[0]['context']['immediate']);
        $this->assertTrue($oneSender->messages[0]['context']['fallback_to_queue']);
        $this->assertSame('https://example.test/receipt.pdf', $oneSender->messages[1]['documentUrl']);
        $this->assertSame('receipt-77.pdf', $oneSender->messages[1]['filename']);
        $this->assertTrue($oneSender->messages[1]['context']['immediate']);
        $this->assertTrue($oneSender->messages[1]['context']['fallback_to_queue']);
        $this->assertNotSame(
            $oneSender->messages[0]['context']['dedupe_key'],
            $oneSender->messages[1]['context']['dedupe_key'],
        );
    }
}
