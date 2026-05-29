<?php

namespace Modules\Order\Services;

use Exception;
use InvalidArgumentException;
use Modules\Order\Entities\Order;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\User\Support\PhoneNumber;

class OrderCustomerWhatsAppService
{
    public function __construct(
        private readonly OneSenderWhatsAppService $oneSender,
        private readonly OrderCustomerWhatsAppMessage $messages,
        private readonly OrderWhatsAppPdfService $pdf,
    ) {
    }


    public function canSend(Order $order): bool
    {
        return OneSenderWhatsAppService::isConfigured()
            && PhoneNumber::normalize((string) $order->customer_phone) !== '';
    }


    /**
     * @throws Exception
     */
    public function sendInvoice(Order $order): void
    {
        $phone = $this->resolveCustomerPhone($order);

        if (! $this->oneSender->sendDocument(
            $phone,
            $this->pdf->invoicePublicUrl($order),
            sprintf('invoice-%d.pdf', $order->id),
            $this->messages->invoice($order),
            [
                'source' => 'order.invoice',
                'dedupe_key' => 'order:' . $order->id . ':invoice',
            ]
        )) {
            throw new InvalidArgumentException(trans('order::whatsapp.not_configured'));
        }
    }


    /**
     * @throws Exception
     */
    public function sendReceipt(Order $order): void
    {
        $phone = $this->resolveCustomerPhone($order);

        if (! $this->oneSender->sendDocument(
            $phone,
            $this->pdf->receiptPublicUrl($order),
            sprintf('receipt-%d.pdf', $order->id),
            $this->messages->receipt($order),
            [
                'source' => 'order.receipt',
                'dedupe_key' => 'order:' . $order->id . ':receipt',
            ]
        )) {
            throw new InvalidArgumentException(trans('order::whatsapp.not_configured'));
        }
    }


    private function resolveCustomerPhone(Order $order): string
    {
        $phone = PhoneNumber::normalize((string) $order->customer_phone);

        if ($phone === '') {
            throw new InvalidArgumentException(trans('order::whatsapp.no_phone'));
        }

        return $phone;
    }
}
