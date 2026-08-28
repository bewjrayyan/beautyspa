<?php

namespace Modules\Order\Services;

use Modules\Order\Entities\Order;

class OrderReceiptPageData
{
    public static function forWeb(Order $order, string $whatsappUrl, string $downloadUrl): array
    {
        return [
            'autoPrint' => false,
            'receiptActions' => true,
            'canSendReceiptWhatsApp' => app(OrderCustomerWhatsAppService::class)->canSend($order),
            'receiptWhatsAppUrl' => $whatsappUrl,
            'receiptDownloadUrl' => $downloadUrl,
        ];
    }
}
