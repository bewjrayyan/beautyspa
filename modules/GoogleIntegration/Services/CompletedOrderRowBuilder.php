<?php

namespace Modules\GoogleIntegration\Services;

use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;

class CompletedOrderRowBuilder
{
    public const HEADERS = [
        'Order ID',
        'Order Date',
        'Status',
        'Customer Name',
        'Customer Email',
        'Customer Phone',
        'Beautician',
        'Beautician Phone',
        'Appointment Date',
        'Appointment Time',
        'Treatments',
        'Subtotal',
        'Discount',
        'Shipping',
        'Tax',
        'Total',
        'Payment Method',
        'Coupon',
        'Order Note',
        'Synced At',
    ];


    public function headers(): array
    {
        return self::HEADERS;
    }


    public function row(Order $order): array
    {
        $order->loadMissing(['products', 'coupon', 'beautician']);

        $customerName = trim($order->customer_first_name . ' ' . $order->customer_last_name);
        $treatments = $order->products
            ->map(fn (OrderProduct $product) => $product->name . ' (x' . $product->qty . ')')
            ->implode('; ');

        return [
            (string) $order->id,
            $order->created_at->format('Y-m-d H:i:s'),
            $order->status(),
            $customerName,
            $order->customer_email,
            $order->customer_phone,
            $order->beautician?->name ?? $this->noteValue($order, 'beautician') ?? '',
            $order->beautician?->phone ?? '',
            $order->appointment_date?->format('Y-m-d') ?? ($this->noteValue($order, 'appointment_date') ?? ''),
            $order->appointment_time ?? ($this->noteValue($order, 'appointment_time') ?? ''),
            $treatments,
            $order->sub_total->convertToCurrentCurrency()->amount(),
            $order->discount->convertToCurrentCurrency()->amount(),
            $order->shipping_cost->convertToCurrentCurrency()->amount(),
            $order->tax->convertToCurrentCurrency()->amount(),
            $order->total->convertToCurrentCurrency()->amount(),
            $order->payment_method ?? '',
            $order->coupon?->code ?? '',
            $order->note ?? '',
            now()->format('Y-m-d H:i:s'),
        ];
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
