<?php

namespace Modules\Order\Services;

use Modules\Order\Entities\Order;
use Modules\Support\Money;

class BankTransferPaymentProofWhatsAppMessage
{
    public function build(Order $order): string
    {
        $template = trim((string) setting('bank_transfer_payment_proof_whatsapp_message', ''));

        if ($template === '') {
            $template = trans('order::whatsapp.payment_proof_group_default');
        }

        return str_replace(
            [':store', ':order_id', ':customer', ':email', ':phone', ':total', ':admin_url'],
            [
                (string) setting('store_name'),
                (string) $order->id,
                $order->customer_full_name,
                (string) $order->customer_email,
                (string) $order->customer_phone,
                $this->formatOrderTotal($order),
                route('admin.orders.show', $order->id),
            ],
            $template
        );
    }


    private function formatOrderTotal(Order $order): string
    {
        $total = $order->total;

        // Order::$total accessor already returns Money — never wrap again.
        if (! $total instanceof Money) {
            $total = Money::inDefaultCurrency($total);
        }

        return $total
            ->convert($order->currency, $order->currency_rate)
            ->format($order->currency);
    }
}
