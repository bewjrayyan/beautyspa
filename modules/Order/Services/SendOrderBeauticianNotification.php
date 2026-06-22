<?php

namespace Modules\Order\Services;

use Exception;
use Modules\Order\Entities\Order;
use Modules\User\Services\OneSenderWhatsAppService;

class SendOrderBeauticianNotification
{
    public function __construct(
        private readonly CompletedOrderGroupWhatsAppMessage $messageBuilder,
        private readonly OneSenderWhatsAppService $oneSender,
    ) {
    }


    /**
     * @throws Exception
     */
    public function send(Order $order): void
    {
        $order->loadMissing('beautician');

        if (! $order->beautician_id || ! $order->beautician) {
            throw new Exception(trans('storefront::order_complete.beautician_notify_no_beautician'));
        }

        $phone = trim((string) $order->beautician->phone);

        if ($phone === '') {
            throw new Exception(trans('storefront::order_complete.beautician_notify_no_phone'));
        }

        $delivered = $this->oneSender->sendNotification(
            $phone,
            $this->messageBuilder->build($order),
            [
                'source' => 'order.completed.beautician',
                'dedupe_key' => 'order:' . $order->id . ':beautician',
                'immediate' => true,
            ]
        );

        if (! $delivered) {
            throw new Exception(trans('storefront::order_complete.beautician_notify_not_configured'));
        }
    }
}
