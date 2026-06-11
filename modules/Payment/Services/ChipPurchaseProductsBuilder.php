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
            'products.product',
            'products.variations',
            'products.options.values',
            'taxes',
        ]);

        $lines = [];

        foreach ($order->products as $orderProduct) {
            $line = $this->mapOrderProduct($order, $orderProduct);

            if ($line !== null) {
                $lines[] = $line;
            }
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

        $lines = $this->applyCartDiscount($order, $lines, $surchargeSubunit);

        return $this->finalizeLines($order, $lines, $surchargeSubunit);
    }


    /**
     * @return array{name: string, price: int, quantity: int}|null
     */
    private function mapOrderProduct(Order $order, OrderProduct $orderProduct): ?array
    {
        $lineTotalSubunit = $this->lineTotalSubunit($order, $orderProduct);

        if ($lineTotalSubunit <= 0) {
            return null;
        }

        $qty = max(1, (int) $orderProduct->qty);

        if ($qty === 1) {
            return [
                'name' => $this->productDisplayName($orderProduct),
                'price' => $lineTotalSubunit,
                'quantity' => 1,
            ];
        }

        $unitSubunit = (int) max(1, (int) round($lineTotalSubunit / $qty));

        return [
            'name' => $this->productDisplayName($orderProduct),
            'price' => $unitSubunit,
            'quantity' => $qty,
        ];
    }


    private function lineTotalSubunit(Order $order, OrderProduct $orderProduct): int
    {
        $rawLineTotal = (float) $orderProduct->getRawOriginal('line_total');

        if ($rawLineTotal > 0) {
            return $this->amountToSubunit($rawLineTotal, $order);
        }

        $rawUnitPrice = (float) $orderProduct->getRawOriginal('unit_price');
        $qty = max(1, (int) $orderProduct->qty);

        if ($rawUnitPrice > 0) {
            return $this->amountToSubunit($rawUnitPrice * $qty, $order);
        }

        return $this->subunit($orderProduct->line_total, $order);
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

        $gross = $this->linesGrossSubunit($lines);
        $target = $this->subunit($order->total, $order) + $extraSubunit;
        $discount = max(0, $gross - $target);

        if ($discount === 0) {
            return $lines;
        }

        $remaining = $discount;

        foreach ($lines as $index => $line) {
            if ($this->isPaymentFeeLine($line)) {
                continue;
            }

            $lineGross = (int) $line['price'] * (int) $line['quantity'];

            if ($lineGross <= 0 || $remaining <= 0) {
                continue;
            }

            $share = (int) round($discount * ($lineGross / max(1, $gross - $extraSubunit)));

            if ($index === $this->lastDiscountableLineIndex($lines)) {
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


    /**
     * @param  array<int, array{name: string, price: int, quantity: int, discount?: int}>  $lines
     * @return array<int, array{name: string, price: int, quantity: int, discount?: int}>
     */
    private function finalizeLines(Order $order, array $lines, int $surchargeSubunit): array
    {
        $lines = array_values(array_filter(
            $lines,
            fn (array $line): bool => (int) ($line['quantity'] ?? 0) > 0
        ));

        if ($lines !== [] && $this->linesNetSubunit($lines) > 0) {
            return $lines;
        }

        $lines = $this->rebuildFromOrderProducts($order);

        if ($surchargeSubunit > 0) {
            $lines[] = [
                'name' => Str::limit(trans('payment::messages.chip_payment_fee'), 256),
                'price' => $surchargeSubunit,
                'quantity' => 1,
            ];
        }

        if ($lines !== [] && $this->linesNetSubunit($lines) > 0) {
            return $this->applyCartDiscount($order, $lines, $surchargeSubunit);
        }

        $targetSubunit = $this->subunit($order->total, $order) + $surchargeSubunit;

        if ($targetSubunit <= 0) {
            return [];
        }

        $itemNames = $order->products
            ->map(fn (OrderProduct $product) => $this->productDisplayName($product))
            ->filter()
            ->values();

        if ($itemNames->isNotEmpty()) {
            return [[
                'name' => Str::limit($itemNames->implode(', '), 256),
                'price' => max(1, $targetSubunit - $surchargeSubunit),
                'quantity' => 1,
            ], ...($surchargeSubunit > 0 ? [[
                'name' => Str::limit(trans('payment::messages.chip_payment_fee'), 256),
                'price' => $surchargeSubunit,
                'quantity' => 1,
            ]] : [])];
        }

        return [[
            'name' => Str::limit(
                trans('payment::messages.chip_order_line', ['id' => $order->id]),
                256
            ),
            'price' => $targetSubunit,
            'quantity' => 1,
        ]];
    }


    /**
     * @return array<int, array{name: string, price: int, quantity: int}>
     */
    private function rebuildFromOrderProducts(Order $order): array
    {
        $lines = [];

        foreach ($order->products as $orderProduct) {
            $line = $this->mapOrderProduct($order, $orderProduct);

            if ($line !== null) {
                $lines[] = $line;
            }
        }

        return $lines;
    }


    private function subunit(Money $money, Order $order): int
    {
        return $money->convert($order->currency, $order->currency_rate)->subunit();
    }


    private function amountToSubunit(float $amount, Order $order): int
    {
        return Money::inDefaultCurrency($amount)
            ->convert($order->currency, $order->currency_rate)
            ->subunit();
    }


    /**
     * @param  array<int, array{name: string, price: int, quantity: int, discount?: int}>  $lines
     */
    private function linesGrossSubunit(array $lines): int
    {
        $total = 0;

        foreach ($lines as $line) {
            $total += (int) $line['price'] * (int) $line['quantity'];
        }

        return $total;
    }


    /**
     * @param  array<int, array{name: string, price: int, quantity: int, discount?: int}>  $lines
     */
    private function linesNetSubunit(array $lines): int
    {
        $total = 0;

        foreach ($lines as $line) {
            $lineTotal = (int) $line['price'] * (int) $line['quantity'];
            $lineTotal -= (int) ($line['discount'] ?? 0);
            $total += max(0, $lineTotal);
        }

        return $total;
    }


    /**
     * @param  array<int, array{name: string, price: int, quantity: int, discount?: int}>  $lines
     */
    private function lastDiscountableLineIndex(array $lines): int
    {
        for ($index = count($lines) - 1; $index >= 0; $index--) {
            if (! $this->isPaymentFeeLine($lines[$index])) {
                return $index;
            }
        }

        return array_key_last($lines) ?? 0;
    }


    /**
     * @param  array{name: string, price: int, quantity: int, discount?: int}  $line
     */
    private function isPaymentFeeLine(array $line): bool
    {
        return $line['name'] === Str::limit(trans('payment::messages.chip_payment_fee'), 256);
    }
}
