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
    ): JsonResponse|\Illuminate\Http\RedirectResponse {
        if (! $whatsapp->canSend($order)) {
            $message = trans(
                OneSenderWhatsAppService::isConfigured()
                    ? 'order::whatsapp.no_phone'
                    : 'order::whatsapp.not_configured'
            );

            return $this->respond($message, false);
        }

        try {
            $whatsapp->{$method}($order);
        } catch (InvalidArgumentException $exception) {
            return $this->respond($exception->getMessage(), false);
        } catch (Throwable $exception) {
            report($exception);

            return $this->respond($exception->getMessage() ?: trans('order::whatsapp.send_failed'), false);
        }

        return $this->respond(trans($successKey), true);
    }


    private function respond(string $message, bool $success): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['message' => $message], $success ? 200 : 422);
        }

        return redirect()
            ->back()
            ->with($success ? 'success' : 'error', $message);
    }
}
