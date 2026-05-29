<?php

namespace Modules\Order\Services;

use Illuminate\Support\Facades\Route;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;

class OrderCustomerWhatsAppMessage
{
    private const LINE = '────────────────';


    public function invoice(Order $order): string
    {
        return $this->build($order, 'invoice');
    }


    public function receipt(Order $order): string
    {
        return $this->build($order, 'receipt');
    }


    public function invoiceCaption(Order $order): string
    {
        return $this->caption($order, 'invoice');
    }


    public function receiptCaption(Order $order): string
    {
        return $this->caption($order, 'receipt');
    }


    private function build(Order $order, string $type): string
    {
        $order->loadMissing([
            'products.variations',
            'products.options.option',
            'products.options.values',
            'coupon',
            'taxes',
            'transaction',
            'beautician',
        ]);

        return $this->withOrderLocale($order, function () use ($order, $type) {
            $titleKey = $type === 'receipt' ? 'order::whatsapp.receipt_title' : 'order::whatsapp.invoice_title';
            $viewDocKey = $type === 'receipt' ? 'order::whatsapp.view_receipt' : 'order::whatsapp.view_invoice';
            $docRoute = $type === 'receipt' ? 'account.orders.receipt' : 'account.orders.invoice';

            $lines = [
                trans('order::whatsapp.greeting', ['name' => $order->customer_full_name]),
                '',
                trans('order::whatsapp.document_heading', [
                    'title' => trans($titleKey),
                    'store' => setting('store_name'),
                ]),
                '',
                trans('order::whatsapp.order_no', ['id' => $order->id]),
                trans('order::whatsapp.date', [
                    'date' => $order->created_at->format('d M Y, h:i A'),
                ]),
                self::LINE,
                trans('order::whatsapp.items'),
            ];

            foreach ($order->products as $product) {
                $lines[] = '• '.$this->productDescription($product);
                $lines[] = '  '.$this->productPriceLine($order, $product);
            }

            $lines[] = self::LINE;
            $lines[] = trans('order::whatsapp.subtotal', [
                'amount' => $this->formatMoney($order, $order->sub_total),
            ]);

            if ($order->hasShippingMethod()) {
                $lines[] = trans('order::whatsapp.shipping', [
                    'method' => $order->shipping_method,
                    'amount' => $this->formatMoney($order, $order->shipping_cost),
                ]);
            }

            if ($order->hasCoupon()) {
                $lines[] = trans('order::whatsapp.discount', [
                    'code' => $order->coupon->code,
                    'amount' => $this->formatMoney($order, $order->discount),
                ]);
            }

            foreach ($order->taxes as $tax) {
                $lines[] = trans('order::whatsapp.tax_line', [
                    'name' => $tax->name,
                    'amount' => $this->formatMoney($order, $tax->order_tax->amount),
                ]);
            }

            if (app('modules')->isEnabled('Loyalty') && $order->loyalty_points_redeemed > 0) {
                $lines[] = trans('loyalty::orders.points_redeemed').': -'.$this->formatMoney(
                    $order,
                    \Modules\Support\Money::inDefaultCurrency($order->loyalty_discount_amount)
                );
            }

            $lines[] = trans('order::whatsapp.total', [
                'amount' => $this->formatMoney($order, $order->total),
            ]);
            $lines[] = self::LINE;
            $lines[] = trans('order::whatsapp.payment_method', ['method' => $order->payment_method]);
            $lines[] = trans('order::whatsapp.payment_status', [
                'status' => $order->paymentStatusLabel(),
            ]);
            $lines[] = trans('order::whatsapp.order_status', ['status' => $order->status()]);

            if ($order->transaction?->transaction_id) {
                $lines[] = trans('order::whatsapp.transaction_id', [
                    'id' => $order->transaction->transaction_id,
                ]);
            }

            $lines[] = self::LINE;

            if ($order->beautician?->name) {
                $lines[] = trans('order::whatsapp.beautician', ['name' => $order->beautician->name]);
            }

            if ($order->appointment_date) {
                $lines[] = trans('order::whatsapp.appointment_date', [
                    'date' => $order->appointment_date->format('d M Y'),
                ]);
            }

            if ($order->appointment_time) {
                $lines[] = trans('order::whatsapp.appointment_time', [
                    'time' => $order->appointment_time,
                ]);
            }

            $lines[] = '';
            $lines[] = trans($viewDocKey, ['url' => $this->accountUrl($order, $docRoute)]);
            $lines[] = trans('order::whatsapp.view_order', [
                'url' => $this->accountUrl($order, 'account.orders.show'),
            ]);
            $lines[] = '';
            $lines[] = trans('order::whatsapp.thanks', ['store' => setting('store_name')]);

            return implode("\n", $lines);
        });
    }


    private function caption(Order $order, string $type): string
    {
        return $this->withOrderLocale($order, function () use ($order, $type) {
            $key = $type === 'receipt' ? 'order::whatsapp.receipt_caption' : 'order::whatsapp.invoice_caption';

            return trans($key, [
                'name' => $order->customer_full_name,
                'store' => setting('store_name'),
                'id' => $order->id,
            ]);
        });
    }


    private function productDescription(OrderProduct $product): string
    {
        $name = $product->name;
        $parts = [];

        if ($product->hasAnyVariation()) {
            foreach ($product->variations as $variation) {
                if (filled($variation->value)) {
                    $parts[] = $variation->value;
                }
            }
        }

        if ($product->hasAnyOption()) {
            foreach ($product->options as $option) {
                if ($option->option?->isFieldType()) {
                    if (filled($option->value)) {
                        $parts[] = $option->value;
                    }
                } else {
                    $labels = $option->values->pluck('label')->filter()->implode(', ');

                    if ($labels !== '') {
                        $parts[] = $labels;
                    }
                }
            }
        }

        if ($parts !== []) {
            $name .= ' ('.implode(', ', $parts).')';
        }

        return $name;
    }


    private function productPriceLine(Order $order, OrderProduct $product): string
    {
        $qty = $product->qty;
        $unit = $this->formatMoney($order, $product->unit_price);
        $line = $this->formatMoney($order, $product->line_total);

        return "{$qty} × {$unit} = {$line}";
    }


    private function formatMoney(Order $order, $amount): string
    {
        return $amount->convert($order->currency, $order->currency_rate)->format($order->currency);
    }


    private function accountUrl(Order $order, string $routeName): string
    {
        if (! Route::has($routeName)) {
            return '';
        }

        $locale = $order->locale ?? app()->getLocale();

        return localized_url($locale, route($routeName, $order->id));
    }


    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    private function withOrderLocale(Order $order, callable $callback)
    {
        $locale = $order->locale ?? app()->getLocale();
        $previous = app()->getLocale();
        app()->setLocale($locale);

        try {
            return $callback();
        } finally {
            app()->setLocale($previous);
        }
    }
}
