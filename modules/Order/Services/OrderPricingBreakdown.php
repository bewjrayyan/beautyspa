<?php

namespace Modules\Order\Services;

use Modules\Order\Entities\Order;
use Modules\Support\Money;

class OrderPricingBreakdown
{
    /**
     * @return array<int, array{label: string, value: string, discount?: bool, meta?: bool}>
     */
    public function lines(Order $order): array
    {
        $order->loadMissing(['taxes', 'coupon', 'products.product']);

        $format = fn (Money $amount) => $amount
            ->convert($order->currency, $order->currency_rate)
            ->format($order->currency);

        $lines = [
            [
                'label' => trans('order::print.subtotal'),
                'value' => $format($order->sub_total),
            ],
        ];

        if ($order->hasPhysicalProducts() && $order->hasShippingMethod()) {
            $lines[] = [
                'label' => (string) $order->shipping_method,
                'value' => $format($order->shipping_cost),
            ];
        }

        foreach ($order->taxes as $tax) {
            $lines[] = [
                'label' => $tax->name,
                'value' => $format($tax->order_tax->amount),
            ];
        }

        if ($order->hasCoupon()) {
            $lines[] = [
                'label' => trans('order::print.coupon').' ('.$order->coupon->code.')',
                'value' => '-'.$format($order->discount),
                'discount' => true,
            ];
        }

        if (app('modules')->isEnabled('Loyalty') && $order->hasLoyaltyRedemption()) {
            $lines[] = [
                'label' => trans('loyalty::orders.points_redeemed').' ('.number_format((int) $order->loyalty_points_redeemed).' '.trans('order::orders.loyalty_pts').')',
                'value' => '-'.$format($order->loyaltyDiscountAmount()),
                'discount' => true,
            ];
        }

        if ($order->hasPaymentProcessingFee()) {
            $lines[] = [
                'label' => trans('order::print.payment_processing_fee'),
                'value' => $format($order->paymentProcessingFee()),
            ];
        }

        if (app('modules')->isEnabled('Loyalty') && (int) ($order->loyalty_points_earned ?? 0) > 0) {
            $lines[] = [
                'label' => trans('loyalty::orders.points_earned'),
                'value' => number_format((int) $order->loyalty_points_earned).' '.trans('order::orders.loyalty_pts'),
                'meta' => true,
            ];
        }

        return $lines;
    }


    public function toWhatsAppBlock(Order $order, string $trackingUrl, string $separator): string
    {
        return $this->withOrderLocale($order, function () use ($order, $trackingUrl, $separator) {
            $format = fn (Money $amount) => $amount
                ->convert($order->currency, $order->currency_rate)
                ->format($order->currency);

            $lines = [
                $separator,
                'PAYMENT SUMMARY',
            ];

            foreach ($this->lines($order) as $line) {
                $lines[] = strtoupper($line['label']).' : '.$line['value'];
            }

            $lines[] = trans('order::print.total').' : '.$format($order->total);
            $lines[] = $separator;
            $lines[] = trans('order::print.payment_method').' : '.($order->payment_method ?: '—');
            $lines[] = trans('order::print.payment_status').' : '.$order->paymentStatusLabel();
            $lines[] = trans('order::print.order_status').' : '.$order->status();

            if ($order->transaction?->transaction_id) {
                $lines[] = trans('order::print.transaction_id').' : '.$order->transaction->transaction_id;
            }

            $lines[] = $separator;
            $lines[] = '';
            $lines[] = 'Payment notification v.13 '.setting('store_name');
            $lines[] = 'Track your Order here : '.$trackingUrl;

            return implode("\n", $lines);
        });
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
