<?php

namespace Modules\Checkout\Listeners;

use Modules\Order\Entities\Order;
use Modules\Order\Services\OrderWhatsAppMessageBuilder;
use Modules\Checkout\Events\OrderPlaced;
use Modules\User\Services\OneSenderWhatsAppService;

class SendNewOrderSms
{
    public function __construct(
        private readonly OrderWhatsAppMessageBuilder $messageBuilder,
        private readonly OneSenderWhatsAppService $oneSender,
    ) {
    }


    /**
     * Handle the event.
     *
     * @param OrderPlaced $event
     *
     * @return void
     */
    public function handle(OrderPlaced $event)
    {
        $this->sendAdminWhatsApp($event->order);
        $this->sendCustomerWhatsApp($event->order);
    }


    private function sendAdminWhatsApp(Order $order)
    {
        if (! setting('new_order_admin_sms')) {
            return;
        }

        $this->oneSender->notifyAdmins($this->adminMessage($order), [
            'source' => 'checkout.order_placed.admin',
            'dedupe_key' => "order:{$order->id}:placed:admin",
        ]);
    }


    private function adminMessage(Order $order)
    {
        return $this->messageBuilder->render($order, 'whatsapp_new_order_admin_message');
    }


    private function sendCustomerWhatsApp(Order $order)
    {
        if (! setting('new_order_sms') || ! $order->customer_phone) {
            return;
        }

        $this->oneSender->sendNotification(
            $order->customer_phone,
            $this->customerMessage($order),
            [
                'source' => 'checkout.order_placed.customer',
                'dedupe_key' => "order:{$order->id}:placed:customer",
            ]
        );
    }


    private function customerMessage(Order $order)
    {
        return $this->messageBuilder->render($order, 'whatsapp_new_order_customer_message');
    }
}
