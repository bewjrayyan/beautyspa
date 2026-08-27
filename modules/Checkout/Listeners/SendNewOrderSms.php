<?php

namespace Modules\Checkout\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Checkout\Events\OrderPlaced;
use Modules\Order\Entities\Order;
use Modules\Order\Services\OrderWhatsAppMessageBuilder;
use Modules\Order\Services\OrderWhatsAppPdfService;
use Modules\User\Services\OneSenderWhatsAppService;

class SendNewOrderSms implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [15, 60, 180];

    public function __construct(
        private readonly OrderWhatsAppMessageBuilder $messageBuilder,
        private readonly OrderWhatsAppPdfService $pdf,
        private readonly OneSenderWhatsAppService $oneSender,
    ) {
    }

    public function handle(OrderPlaced $event): void
    {
        $this->sendAdminWhatsApp($event->order);
        $this->sendCustomerWhatsApp($event->order);
    }

    private function sendAdminWhatsApp(Order $order): void
    {
        if (! setting('new_order_admin_sms')) {
            return;
        }

        $this->oneSender->notifyAdmins($this->adminMessage($order), [
            'source' => 'checkout.order_placed.admin',
            'dedupe_key' => "order:{$order->id}:placed:admin",
        ]);
    }

    private function adminMessage(Order $order): string
    {
        return $this->messageBuilder->render($order, 'whatsapp_new_order_admin_message');
    }

    private function sendCustomerWhatsApp(Order $order): void
    {
        if (! setting('new_order_sms') || ! $order->customer_phone) {
            return;
        }

        $this->oneSender->sendDocument(
            $order->customer_phone,
            $this->pdf->receiptPublicUrl($order),
            sprintf('receipt-%d.pdf', $order->id),
            $this->customerMessage($order),
            [
                'source' => 'checkout.order_placed.customer',
                'dedupe_key' => "order:{$order->id}:placed:customer",
            ]
        );
    }

    private function customerMessage(Order $order): string
    {
        return $this->messageBuilder->render($order, 'whatsapp_new_order_customer_message');
    }
}
