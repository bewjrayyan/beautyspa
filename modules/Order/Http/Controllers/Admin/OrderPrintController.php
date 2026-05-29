<?php

namespace Modules\Order\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;

class OrderPrintController
{
    /**
     * Show the specified resource.
     *
     * @param Order $order
     *
     * @return Response
     */
    public function show(Order $order)
    {
        return $this->renderPrintView($order, 'order::admin.orders.print.show');
    }


    public function receipt(Order $order)
    {
        return $this->renderPrintView($order, 'order::admin.orders.print.receipt');
    }


    private function renderPrintView(Order $order, string $view)
    {
        $order->load([
            'products.variations',
            'products.options.option',
            'products.options.values',
            'coupon',
            'taxes',
            'transaction',
            'beautician',
        ]);

        $logo = $this->resolveStoreLogo();

        return view($view, compact('order', 'logo'));
    }


    private function resolveStoreLogo(): ?string
    {
        $logoId = setting('storefront_header_logo');

        if (! $logoId) {
            return null;
        }

        return File::find($logoId)?->path;
    }
}
