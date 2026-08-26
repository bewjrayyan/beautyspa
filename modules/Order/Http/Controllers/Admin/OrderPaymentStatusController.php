<?php

namespace Modules\Order\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\Order\Services\OrderPaymentReferenceService;

class OrderPaymentStatusController
{
    public function update(Order $order, OrderPaymentReferenceService $paymentReferences): JsonResponse|string
    {
        $paymentStatus = request('payment_status');

        if (! in_array($paymentStatus, Order::paymentStatuses(), true)) {
            abort(422, trans('order::messages.invalid_payment_status'));
        }

        $previousPayment = $order->payment_status;
        $previousOrder = $order->status;

        $order->loadMissing('transaction');

        try {
            $paymentReferences->syncForPaymentStatus(
                $order,
                $paymentStatus,
                request()->input('transaction_id'),
                request()->input('admin_note'),
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first()
                    ?: trans('order::messages.payment_reference_required'),
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($previousPayment === $paymentStatus) {
            $order = $order->fresh(['transaction']);

            if (request()->wantsJson()) {
                return response()->json([
                    'message' => trans('order::messages.payment_reference_saved'),
                    'transaction_id' => $order->transaction?->transaction_id,
                    'admin_note' => $order->transaction?->admin_note,
                ]);
            }

            return trans('order::messages.payment_reference_saved');
        }

        $updates = ['payment_status' => $paymentStatus];
        $changeType = 'payment';

        if ($paymentStatus === Order::PAYMENT_PAID && $order->status === Order::PENDING) {
            $updates['status'] = Order::COMPLETED;
            $changeType = 'order_and_payment';
        }

        if (
            in_array($paymentStatus, [Order::PAYMENT_CANCELED, Order::PAYMENT_REFUNDED], true)
            && $order->status !== Order::CANCELED
        ) {
            $updates['status'] = Order::CANCELED;
            $changeType = 'order_and_payment';
        }

        $order->update($updates);
        $order = $order->fresh(['transaction']);

        $newValue = $changeType === 'order_and_payment'
            ? ($order->status.'/'.$order->payment_status)
            : $order->payment_status;

        $previousValue = $changeType === 'order_and_payment'
            ? ($previousOrder.'/'.$previousPayment)
            : $previousPayment;

        event(new OrderStatusChanged($order, $changeType, $previousValue, $newValue));

        $message = trans('order::messages.payment_status_updated');

        if (request()->wantsJson()) {
            return response()->json([
                'message' => $message,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
                'transaction_id' => $order->transaction?->transaction_id,
                'admin_note' => $order->transaction?->admin_note,
            ]);
        }

        return $message;
    }
}
