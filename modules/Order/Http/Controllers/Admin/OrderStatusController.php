<?php

namespace Modules\Order\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;
use Modules\Order\Events\OrderStatusChanged;

class OrderStatusController
{
    /**
     * Update the specified resource in storage.
     *
     * @return Response|string
     */
    public function update(Order $order)
    {
        $previous = $order->status;
        $status = (string) request('status');

        if (! in_array($status, Order::statuses(), true)) {
            abort(422, trans('order::messages.invalid_order_status'));
        }

        if ($previous === $status) {
            return trans('order::messages.status_updated');
        }

        $this->adjustStock($order);

        $order->update(['status' => $status]);

        event(new OrderStatusChanged($order->fresh(), 'order', $previous, $status));

        return trans('order::messages.status_updated');
    }


    private function adjustStock(Order $order)
    {
        if ($this->canceledOrRefunded(request('status'))) {
            $this->restoreStock($order);
        }

        if ($this->canceledOrRefunded($order->status)) {
            $this->reduceStock($order);
        }
    }


    private function canceledOrRefunded($status)
    {
        return $status === Order::CANCELED;
    }


    private function restoreStock(Order $order)
    {
        $order->products->each(function (OrderProduct $orderProduct) {
            if ($orderProduct->product_variant) {
                if ($orderProduct->product_variant->manage_stock) {
                    $orderProduct->product_variant->increment('qty', $orderProduct->qty);
                }

                if ($orderProduct->product_variant->qty === 1) {
                    $orderProduct->product_variant->markAsInStock();
                }
            } else {
                if ($orderProduct->product->manage_stock) {
                    $orderProduct->product->increment('qty', $orderProduct->qty);
                }

                if ($orderProduct->product->qty > 0) {
                    $orderProduct->product->markAsInStock();
                }
            }
        });
    }


    private function reduceStock(Order $order)
    {
        $order->products->each(function (OrderProduct $orderProduct) {
            if ($orderProduct->product_variant) {
                if (
                    $orderProduct->product_variant->manage_stock
                    && $orderProduct->product_variant->qty !== 0
                ) {
                    $orderProduct->product_variant->decrement('qty', $orderProduct->qty);
                }
            } else {
                if (
                    $orderProduct->product->manage_stock
                    && $orderProduct->product->qty !== 0
                ) {
                    $orderProduct->product->decrement('qty', $orderProduct->qty);
                }
            }
        });
    }
}
