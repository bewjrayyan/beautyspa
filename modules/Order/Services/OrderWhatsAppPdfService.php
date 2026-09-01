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
    private const RECEIPT_PAPER_WIDTH_MM = 80;

    private const RECEIPT_PAPER_HEIGHT_MM = 297;

    public function invoicePublicUrl(Order $order): string
    {
        return $this->storePdf($order, 'invoice', 'order::admin.orders.print.pdf-invoice');
    }


    public function receiptPublicUrl(Order $order): string
    {
        return $this->storePdf($order, 'receipt', 'order::admin.orders.print.receipt', $this->receiptPdfViewData());
    }


    public function invoicePdfBinary(Order $order): string
    {
        return $this->renderPdf($this->prepareOrder($order), 'order::admin.orders.print.pdf-invoice');
    }


    public function receiptPdfBinary(Order $order): string
    {
        return $this->renderReceiptPdf($this->prepareOrder($order));
    }


    private function storePdf(Order $order, string $type, string $view, array $viewData = []): string
    {
        $order = $this->prepareOrder($order);

        $fingerprint = md5((string) ($order->updated_at?->timestamp ?? $order->id));
        $relativePath = "orders/{$order->id}/{$type}-{$fingerprint}.pdf";

        $disk = Storage::disk('private');

        if (! $disk->exists($relativePath)) {
            $pdf = $type === 'receipt'
                ? $this->renderReceiptPdf($order)
                : $this->renderPdf($order, $view, $viewData);

            $disk->put($relativePath, $pdf);
        }

        // Relative signatures must exclude the install base (see aestheticcart_subdirectory_safe_temporary_signed_route).
        $relative = aestheticcart_subdirectory_safe_temporary_signed_route(
            'order.documents.temporary',
            now()->addMinutes(90),
            ['order' => $order->id, 'type' => $type, 'fingerprint' => $fingerprint]
        );

        return aestheticcart_absolute_from_relative_path($relative);
    }


    private function prepareOrder(Order $order): Order
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


    private function renderReceiptPdf(Order $order): string
    {
        return $this->renderPdf(
            $order,
            'order::admin.orders.print.receipt',
            $this->receiptPdfViewData(),
            $this->receiptPaperSizePoints(),
        );
    }


    private function receiptPaperSizePoints(): array
    {
        return [
            0,
            0,
            $this->mmToPoints(self::RECEIPT_PAPER_WIDTH_MM),
            $this->mmToPoints(self::RECEIPT_PAPER_HEIGHT_MM),
        ];
    }


    private function mmToPoints(float $mm): float
    {
        return $mm * 72 / 25.4;
    }


    private function receiptPdfViewData(): array
    {
        return [
            'forPdf' => true,
            'autoPrint' => false,
            'receiptActions' => false,
            'inlineReceiptCss' => app(OrderReceiptPdfCss::class)->inline(),
        ];
    }


    private function renderPdf(Order $order, string $view, array $viewData = [], ?array $paper = null): string
    {
        $html = view($view, array_merge([
            'order' => $order,
            'logo' => $this->resolveStoreLogoForPdf(),
        ], $viewData))->render();

        try {
            $dompdf = new Dompdf(DompdfConfigurator::createOptions(true));
            $dompdf->loadHtml($html);

            if ($paper !== null) {
                $dompdf->setPaper($paper);
            } else {
                $dompdf->setPaper('A4', 'portrait');
            }

            $dompdf->render();

            return (string) $dompdf->output();
        } catch (\Throwable $exception) {
            throw new Exception(
                'Failed to generate order PDF: '.$exception->getMessage(),
                previous: $exception
            );
        }
    }


    private function resolveStoreLogoForPdf(): ?string
    {
        $logoId = setting('storefront_header_logo');

        if ($logoId) {
            $file = File::find($logoId);

            if ($file) {
                $rawPath = $file->getRawOriginal('path');

                if (is_string($rawPath) && $rawPath !== '') {
                    try {
                        $disk = Storage::disk($file->disk);

                        if ($disk->exists($rawPath)) {
                            $fullPath = $disk->path($rawPath);

                            if (is_readable($fullPath)) {
                                return $fullPath;
                            }
                        }
                    } catch (\Throwable) {
                        // Fall back to public URL below.
                    }
                }
            }
        }

        return $this->resolveStoreLogo();
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
