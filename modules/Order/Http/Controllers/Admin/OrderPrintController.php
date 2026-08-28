<?php

namespace Modules\Order\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use Modules\Order\Services\OrderReceiptPageData;
use Modules\Order\Services\OrderWhatsAppPdfService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderPrintController
{
    public function show(Order $order)
    {
        return $this->renderPrintView($order, 'order::admin.orders.print.show');
    }


    public function receipt(Order $order)
    {
        $order = $this->loadOrder($order);

        return view('order::admin.orders.print.receipt', array_merge([
            'order' => $order,
            'logo' => $this->resolveStoreLogo(),
        ], OrderReceiptPageData::forWeb(
            $order,
            route('admin.orders.whatsapp.receipt', $order),
            route('admin.orders.receipt.download', $order),
        )));
    }


    public function downloadReceipt(Order $order, OrderWhatsAppPdfService $pdf): StreamedResponse|Response
    {
        $order = $this->loadOrder($order);
        $filename = sprintf('receipt-%d.pdf', $order->id);

        return response($pdf->receiptPdfBinary($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }


    private function renderPrintView(Order $order, string $view)
    {
        $order = $this->loadOrder($order);

        return view($view, [
            'order' => $order,
            'logo' => $this->resolveStoreLogo(),
        ]);
    }


    private function loadOrder(Order $order): Order
    {
        $order->load([
            'products.variations',
            'products.options.option',
            'products.options.values',
            'coupon',
            'taxes',
            'transaction',
            'beautician',
            'spaBranch',
            'treatmentBookings.product',
            'treatmentBookings.beautician',
            'treatmentBookings.orderProduct.options.values',
            'treatmentBookings.orderProduct.variations.values',
        ]);

        return $order;
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
