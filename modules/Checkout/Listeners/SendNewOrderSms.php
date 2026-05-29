<?php

namespace Modules\Checkout\Listeners;

use Exception;
use Modules\Sms\Sms;
use Modules\Order\Entities\Order;
use Modules\Checkout\Events\OrderPlaced;
use Modules\Sms\Exceptions\SmsException;

class SendNewOrderSms
{
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
        return trans('sms::messages.new_order', ['order_id' => $order->id]);
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
        return trans('sms::messages.order_has_been_placed', [
            'first_name' => $order->customer_first_name,
            'order_id' => $order->id,
        ]);
    }
}
