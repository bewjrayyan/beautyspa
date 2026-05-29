<?php

namespace Modules\Payment\Services;

use Illuminate\Support\Str;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;
use Modules\Support\Money;

class ChipPurchaseProductsBuilder
{
    /**
     * Build CHIP purchase line items from order (matches storefront order detail table).
     *
     * @return array<int, array{name: string, price: int, quantity: int, discount?: int}>
     */
    public function build(Order $order, int $surchargeSubunit = 0): array
    {
        $order->loadMissing([
            'products.variations',
            'products.options.values',
            'taxes',
        ]);

        $lines = [];

        foreach ($order->products as $orderProduct) {
            $lines[] = $this->mapOrderProduct($order, $orderProduct);
        }

        if ($order->hasShippingMethod() && ! $order->shipping_cost->isZero()) {
            $lines[] = [
                'name' => Str::limit((string) $order->shipping_method, 256),
                'price' => $this->subunit($order->shipping_cost, $order),
                'quantity' => 1,
            ];
        }

        foreach ($order->taxes as $tax) {
            $taxAmount = $tax->order_tax->amount;

            if ($taxAmount->isZero()) {
                continue;
            }

            $lines[] = [
                'name' => Str::limit($tax->name, 256),
                'price' => $this->subunit($taxAmount, $order),
                'quantity' => 1,
            ];
        }

        if ($surchargeSubunit > 0) {
            $lines[] = [
                'name' => Str::limit(trans('payment::messages.chip_payment_fee'), 256),
                'price' => $surchargeSubunit,
                'quantity' => 1,
            ];
        }

        return $this->applyCartDiscount($order, $lines, $surchargeSubunit);
    }


    /**
     * @return array{name: string, price: int, quantity: int}
     */
    private function mapOrderProduct(Order $order, OrderProduct $orderProduct): array
    {
        return [
            'name' => $this->productDisplayName($orderProduct),
            'price' => $this->subunit($orderProduct->unit_price, $order),
            'quantity' => (int) $orderProduct->qty,
        ];
    }


    private function productDisplayName(OrderProduct $orderProduct): string
    {
        $segments = [$orderProduct->name];

        foreach ($orderProduct->variations as $variation) {
            $value = trim((string) ($variation->value ?? ''));

            if ($value !== '') {
                $segments[] = $variation->name . ': ' . $value;
            }
        }

        foreach ($orderProduct->options as $option) {
            if ($option->isFieldType()) {
                $value = trim((string) $option->value);

                if ($value !== '') {
                    $segments[] = $option->name . ': ' . $value;
                }

                continue;
            }

            $labels = $option->values->pluck('label')->filter()->implode(', ');

            if ($labels !== '') {
                $segments[] = $option->name . ': ' . $labels;
            }
        }

        return Str::limit(implode(' — ', $segments), 256);
    }


    /**
     * @param  array<int, array{name: string, price: int, quantity: int, discount?: int}>  $lines
     * @return array<int, array{name: string, price: int, quantity: int, discount?: int}>
     */
    private function applyCartDiscount(Order $order, array $lines, int $extraSubunit = 0): array
    {
        if ($lines === []) {
            return $lines;
        }

        $gross = 0;

        foreach ($lines as $line) {
            $gross += $line['price'] * $line['quantity'];
        }

        $target = $this->subunit($order->total, $order) + $extraSubunit;
        $discount = max(0, $gross - $target);

        if ($discount === 0) {
            return $lines;
        }

        $remaining = $discount;

        foreach ($lines as $index => $line) {
            $lineGross = $line['price'] * $line['quantity'];

            if ($lineGross <= 0 || $remaining <= 0) {
                continue;
            }

            $share = (int) round($discount * ($lineGross / $gross));

            if ($index === array_key_last($lines)) {
                $share = $remaining;
            } else {
                $share = min($share, $remaining, $lineGross);
            }

            if ($share > 0) {
                $lines[$index]['discount'] = $share;
                $remaining -= $share;
            }
        }

        return $lines;
    }


    private function subunit(Money $money, Order $order): int
    {
        return $money->convert($order->currency, $order->currency_rate)->subunit();
    }
}
