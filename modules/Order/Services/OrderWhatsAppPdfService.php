<?php

namespace Modules\Order\Services;

use Dompdf\Dompdf;
use Exception;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Entities\File;
use Modules\Order\Entities\Order;
use Modules\Support\Services\DompdfConfigurator;

class OrderWhatsAppPdfService
{
    public function invoicePublicUrl(Order $order): string
    {
        return $this->storePdf($order, 'invoice', 'order::admin.orders.print.pdf-invoice');
    }


    public function receiptPublicUrl(Order $order): string
    {
        return $this->storePdf($order, 'receipt', 'order::admin.orders.print.pdf-receipt');
    }


    private function storePdf(Order $order, string $type, string $view): string
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

        $relativePath = sprintf(
            'order-whatsapp/%d/%s-%s.pdf',
            $order->id,
            $type,
            md5((string) ($order->updated_at?->timestamp ?? $order->id))
        );

        $disk = Storage::disk('public');
        $pdf = $this->renderPdf($order, $view);

        $disk->put($relativePath, $pdf);

        return asset('storage/' . $relativePath);
    }


    private function renderPdf(Order $order, string $view): string
    {
        $html = view($view, [
            'order' => $order,
            'logo' => $this->resolveStoreLogo(),
        ])->render();

        try {
            $dompdf = new Dompdf(DompdfConfigurator::createOptions(true));
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return (string) $dompdf->output();
        } catch (\Throwable $exception) {
            throw new Exception(
                'Failed to generate order PDF: '.$exception->getMessage(),
                previous: $exception
            );
        }
    }


    private function resolveStoreLogo(): ?string
    {
        $logoId = setting('storefront_header_logo');

        if (! $logoId) {
            return null;
        }

        $path = File::find($logoId)?->path;

        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url($path);
    }
}
