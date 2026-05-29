<?php

namespace Modules\Order\Services;

use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;

class CompletedOrderGroupWhatsAppMessage
{
    private const LINE = '..............................................................';


    public function build(Order $order): string
    {
        $order->loadMissing(['products.variations', 'products.options', 'coupon', 'transaction', 'beautician']);

        $beauticianName = $order->beautician?->name
            ?? $this->noteValue($order, 'beautician')
            ?? (string) setting('whatsapp_group_staff_name', 'TEAM');
        $staffName = strtoupper($beauticianName);
        $customerName = trim($order->customer_first_name . ' ' . $order->customer_last_name);
        $beautician = $beauticianName;
        $apptDate = $order->appointment_date
            ? $order->appointment_date->format('d/M/Y')
            : ($this->noteValue($order, 'appointment_date') ?: '—');
        $apptTime = $order->appointment_time ?: ($this->noteValue($order, 'appointment_time') ?: '—');
        $couponCode = $order->coupon?->code ?? '- / -';
        $transactionId = $order->transaction?->transaction_id ?? '—';
        $trackingUrl = rtrim((string) setting('whatsapp_order_tracking_url', url('/')), '/');

        $lines = [
            'New Booking Treatment! 🟢',
            '',
            "HI {$staffName} NEW Booking Treatment from {$customerName},",
            '',
            "Customer : {$customerName}",
            "Order No : {$order->id}",
            "Email : {$order->customer_email}",
            'Order Date : ' . $order->created_at->format('d/M/Y'),
            self::LINE,
            "Beautician : {$beautician}",
            "Appt.Date: {$apptDate}",
            "Appt.Time: {$apptTime}",
            self::LINE,
            'Treatment :',
        ];

        foreach ($order->products as $product) {
            $lines[] = $this->productLine($product);
            $lines[] = $this->productPriceLine($product);
        }

        $subtotal = $order->sub_total->convertToCurrentCurrency()->format();
        $discount = $order->discount->convertToCurrentCurrency()->format();
        $total = $order->total->convertToCurrentCurrency()->format();
        $totalBeforeDiscount = $order->sub_total
            ->convertToCurrentCurrency()
            ->add($order->shipping_cost->convertToCurrentCurrency())
            ->add($order->tax->convertToCurrentCurrency())
            ->format();

        $lines = array_merge($lines, [
            self::LINE,
            'PAYMENT SUMMARY.',
            "SUBTOTAL : {$subtotal}",
            "TOTAL TREATMENT : {$subtotal}",
            "TOTAL PAID : {$totalBeforeDiscount}",
            "COUPON CODE : {$couponCode}",
            "Coupon DISCOUNT : {$discount}",
            'TOTAL PAID after Coupon DISCOUNT',
            ": {$total}",
            self::LINE,
            'Payment Method : ' . ($order->payment_method ?: '—'),
            'Payment Status : ' . $order->status(),
            "Transaction ID : {$transactionId}",
            self::LINE,
            '',
            'Payment notification v.13 ' . setting('store_name'),
            "Track your Order here : {$trackingUrl}",
        ]);

        return implode("\n", $lines);
    }


    private function productLine(OrderProduct $product): string
    {
        $name = $product->name;

        if ($product->hasAnyVariation()) {
            $parts = $product->variations->map(fn ($variation) => $variation->value)->filter()->all();

            if ($parts !== []) {
                $name .= implode(' ', $parts);
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
