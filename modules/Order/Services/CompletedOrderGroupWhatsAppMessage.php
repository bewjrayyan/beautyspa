<?php

namespace Modules\Order\Services;

use Modules\Order\Entities\Order;

class CompletedOrderGroupWhatsAppMessage
{
    public function __construct(
        private readonly OrderWhatsAppMessageBuilder $messageBuilder,
    ) {
    }


    public function build(Order $order, string $templateKey = 'whatsapp_completed_group_message'): string
    {
        return $this->messageBuilder->render($order, $templateKey);
    }
}
