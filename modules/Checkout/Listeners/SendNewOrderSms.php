<?php

namespace Modules\Checkout\Listeners;

use Modules\Order\Entities\Order;
use Modules\Order\Services\OrderWhatsAppMessageBuilder;
use Modules\Checkout\Events\OrderPlaced;
use Modules\Sms\Exceptions\SmsException;
use Modules\Sms\Sms;

class SendNewOrderSms
{
    public function __construct(
        private readonly OrderWhatsAppMessageBuilder $messageBuilder,
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

        try {
            Sms::sendToAdmins($this->adminMessage($order));
        } catch (SmsException $e) {
            //
        }
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

        try {
            Sms::send(
                $order->customer_phone,
                $this->customerMessage($order)
            );
        } catch (SmsException $e) {
            //
        }
    }


    private function customerMessage(Order $order)
    {
        return $this->messageBuilder->render($order, 'whatsapp_new_order_customer_message');
    }
}
