<?php

namespace Modules\Order\Services;

use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;
use Modules\Setting\Support\WhatsAppMessageTemplate;

class OrderWhatsAppMessageBuilder
{
    public const LINE = '..............................................................';


    public function __construct(
        private readonly OrderPricingBreakdown $pricingBreakdown,
    ) {
    }


    public function render(Order $order, string $templateKey, ?string $fallback = null): string
    {
        return WhatsAppMessageTemplate::render(
            $templateKey,
            $this->replacements($order),
            $fallback
        );
    }


    /**
     * @return array<string, string>
     */
    public function replacements(Order $order): array
    {
        $order->loadMissing([
            'products.variations',
            'products.options',
            'products.product',
            'coupon',
            'taxes',
            'transaction',
            'beautician',
        ]);

        $beauticianName = $order->beautician?->name
            ?? $this->noteValue($order, 'beautician')
            ?? (string) setting('whatsapp_group_staff_name', 'TEAM');
        $customerName = trim($order->customer_first_name.' '.$order->customer_last_name);
        $apptDate = $order->appointment_date
            ? $order->appointment_date->format('d/M/Y')
            : ($this->noteValue($order, 'appointment_date') ?: '—');
        $apptTime = $order->appointment_time ?: ($this->noteValue($order, 'appointment_time') ?: '—');
        $trackingUrl = $this->trackingUrl($order);

        return [
            'store' => (string) setting('store_name'),
            'staff' => strtoupper($beauticianName),
            'customer' => $customerName,
            'first_name' => (string) $order->customer_first_name,
            'order_id' => (string) $order->id,
            'email' => (string) $order->customer_email,
            'phone' => (string) ($order->customer_phone ?: '—'),
            'order_date' => $order->created_at->format('d/M/Y'),
            'beautician' => $beauticianName,
            'appointment_date' => $apptDate,
            'appointment_time' => $apptTime,
            'payment_method' => (string) ($order->payment_method ?: '—'),
            'order_status' => $order->status(),
            'payment_status' => $order->paymentStatusLabel(),
            'total' => $order->total->convert($order->currency, $order->currency_rate)->format($order->currency),
            'tracking_url' => $trackingUrl,
            'treatments' => $this->buildTreatmentsBlock($order),
            'payment_summary' => $this->pricingBreakdown->toWhatsAppBlock($order, $trackingUrl, self::LINE),
        ];
    }


    private function buildTreatmentsBlock(Order $order): string
    {
        $lines = [];

        foreach ($order->products as $product) {
            $lines[] = $this->productLine($product);
            $lines[] = $this->productPriceLine($product);
        }

        return implode("\n", $lines);
    }


    private function productLine(OrderProduct $product): string
    {
        $name = $product->name;

        if ($product->hasAnyVariation()) {
            $parts = $product->variations->map(fn ($variation) => $variation->value)->filter()->all();

            if ($parts !== []) {
                $name .= ' '.implode(' ', $parts);
            }
        }

        return $name;
    }


    private function productPriceLine(OrderProduct $product): string
    {
        $qty = $product->qty;
        $unit = $product->unit_price->convertToCurrentCurrency()->format();
        $line = $product->line_total->convertToCurrentCurrency()->format();

        return "{$qty} x {$unit} = {$line}";
    }


    private function trackingUrl(Order $order): string
    {
        $base = rtrim((string) setting('whatsapp_order_tracking_url', url('/')), '/');

        if ($base === '') {
            return url('/');
        }

        return "{$base}/{$order->id}";
    }


    private function noteValue(Order $order, string $field): ?string
    {
        $note = (string) $order->note;

        if ($note === '') {
            return null;
        }

        $patterns = [
            'beautician' => '/Beautician\s*:\s*(.+)/i',
            'appointment_date' => '/Appt\.?\s*Date\s*:\s*(.+)/i',
            'appointment_time' => '/Appt\.?\s*Time\s*:\s*(.+)/i',
        ];

        if (! isset($patterns[$field])) {
            return null;
        }

        if (preg_match($patterns[$field], $note, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
