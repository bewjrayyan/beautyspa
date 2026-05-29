<?php

namespace Modules\Order\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Throwable;
use Modules\Order\Entities\Order;
use Modules\Order\Services\OrderCustomerWhatsAppService;
use Modules\User\Services\OneSenderWhatsAppService;

class OrderWhatsAppController
{
    public function sendInvoice(Order $order, OrderCustomerWhatsAppService $whatsapp): JsonResponse
    {
        return $this->send($order, $whatsapp, 'sendInvoice', 'order::messages.whatsapp_invoice_sent');
    }


    public function sendReceipt(Order $order, OrderCustomerWhatsAppService $whatsapp): JsonResponse
    {
        return $this->send($order, $whatsapp, 'sendReceipt', 'order::messages.whatsapp_receipt_sent');
    }


    private function send(
        Order $order,
        OrderCustomerWhatsAppService $whatsapp,
        string $method,
        string $successKey,
    ): JsonResponse {
        if (! $whatsapp->canSend($order)) {
            return response()->json([
                'message' => trans(
                    OneSenderWhatsAppService::isConfigured()
                        ? 'order::whatsapp.no_phone'
                        : 'order::whatsapp.not_configured'
                ),
            ], 422);
        }

        try {
            $whatsapp->{$method}($order);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => $exception->getMessage() ?: trans('order::whatsapp.send_failed'),
            ], 422);
        }

        return response()->json([
            'message' => trans($successKey),
        ]);
    }
}
