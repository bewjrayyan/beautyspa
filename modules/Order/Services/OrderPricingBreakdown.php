<?php

namespace Modules\Order\Services;

use Modules\Order\Entities\Order;
use Modules\Support\Money;

class OrderPricingBreakdown
{
    /**
     * @return array<int, array{label: string, value: string, discount?: bool, meta?: bool}>
     */
    public function lines(Order $order, bool $alwaysShow = false, ?callable $formatter = null): array
    {
        $order->loadMissing(['taxes', 'coupon', 'products.product']);

        $format = $formatter ?? fn (Money $amount) => $amount
            ->convert($order->currency, $order->currency_rate)
            ->format($order->currency);

        $zero = Money::inDefaultCurrency(0);
        $amounts = $this->amounts($order);

        $lines = [
            [
                'label' => trans('order::print.subtotal'),
                'value' => $format($order->sub_total),
            ],
        ];

        $shippingAmount = $amounts['shipping'];
        if ($alwaysShow || $order->hasShippingMethod() || $shippingAmount > 0) {
            $lines[] = [
                'label' => $order->shipping_method
                    ? (string) $order->shipping_method
                    : trans('order::orders.shipping_method'),
                'value' => $format($order->shipping_cost),
            ];
        }

        if ($order->taxes->isNotEmpty()) {
            foreach ($order->taxes as $tax) {
                $lines[] = [
                    'label' => $tax->name,
                    'value' => $format($tax->order_tax->amount),
                ];
            }
        } elseif ($alwaysShow) {
            $lines[] = [
                'label' => trans('order::orders.tax'),
                'value' => $format($zero),
            ];
        }

        $couponDiscount = $amounts['coupon_discount'];
        $plainDiscount = $amounts['plain_discount'];
        $inferredOtherDiscount = $amounts['inferred_other_discount'];

        if ($order->hasCoupon()) {
            $lines[] = [
                'label' => trans('order::print.coupon').' ('.$order->coupon->code.')',
                'value' => $couponDiscount > 0 ? '-'.$format($order->discount) : $format($zero),
                'discount' => $couponDiscount > 0,
            ];
        } else {
            $plainDiscount += $inferredOtherDiscount;

            if ($alwaysShow || $plainDiscount > 0) {
                $plainDiscountMoney = Money::inDefaultCurrency($plainDiscount);

                $lines[] = [
                    'label' => trans('order::print.discount'),
                    'value' => $plainDiscount > 0 ? '-'.$format($plainDiscountMoney) : $format($zero),
                    'discount' => $plainDiscount > 0,
                ];
            }
        }

        if ($order->hasCoupon() && $inferredOtherDiscount > 0.009) {
            $lines[] = [
                'label' => trans('order::print.discount'),
                'value' => '-'.$format(Money::inDefaultCurrency($inferredOtherDiscount)),
                'discount' => true,
            ];
        }

        $loyaltyEnabled = $amounts['loyalty_enabled'];
        $loyaltyPointsRedeemed = $amounts['loyalty_points_redeemed'];
        $loyaltyPointsEarned = $amounts['loyalty_points_earned'];
        $loyaltyDiscount = $amounts['loyalty_discount'];
        $inferredLoyaltyDiscount = $amounts['inferred_loyalty_discount'];

        if ($loyaltyEnabled && ($alwaysShow || $loyaltyPointsRedeemed > 0 || $inferredLoyaltyDiscount > 0)) {
            $loyaltyDisplayAmount = $loyaltyDiscount > 0
                ? $order->loyaltyDiscountAmount()
                : Money::inDefaultCurrency($inferredLoyaltyDiscount);

            $label = trans('loyalty::orders.points_redeemed');
            if ($loyaltyPointsRedeemed > 0) {
                $label .= ' ('.number_format($loyaltyPointsRedeemed).' '.trans('order::orders.loyalty_pts').')';
            }

            $lines[] = [
                'label' => $label,
                'value' => $loyaltyDisplayAmount->amount() > 0
                    ? '-'.$format($loyaltyDisplayAmount)
                    : $format($zero),
                'discount' => $loyaltyDisplayAmount->amount() > 0,
            ];
        }

        $feeAmount = $amounts['processing_fee'];

        if ($alwaysShow || $feeAmount > 0.009) {
            $lines[] = [
                'label' => trans('order::print.payment_processing_fee'),
                'value' => $format(Money::inDefaultCurrency($feeAmount)),
            ];
        }

        if ($loyaltyEnabled && ($alwaysShow || $loyaltyPointsEarned > 0)) {
            $lines[] = [
                'label' => trans('loyalty::orders.points_earned'),
                'value' => number_format($loyaltyPointsEarned).' '.trans('order::orders.loyalty_pts'),
                'meta' => true,
            ];
        }

        return $lines;
    }


    public function totalDiscountAmount(Order $order): float
    {
        $amounts = $this->amounts($order);

        return round(
            $amounts['coupon_discount']
                + $amounts['plain_discount']
                + $amounts['loyalty_discount']
                + $amounts['inferred_loyalty_discount']
                + $amounts['inferred_other_discount'],
            4
        );
    }


    /**
     * @return array<string, float|int|bool>
     */
    private function amounts(Order $order): array
    {
        $order->loadMissing(['taxes', 'coupon']);

        $shipping = (float) $order->shipping_cost->amount();
        $tax = (float) $order->taxes->sum(
            fn ($tax) => (float) $tax->order_tax->amount->amount()
        );
        $storedDiscount = (float) $order->discount->amount();
        $couponDiscount = $order->hasCoupon() ? $storedDiscount : 0.0;
        $plainDiscount = $order->hasCoupon() ? 0.0 : $storedDiscount;
        $loyaltyEnabled = app('modules')->isEnabled('Loyalty');
        $loyaltyPointsRedeemed = (int) ($order->loyalty_points_redeemed ?? 0);
        $loyaltyPointsEarned = (int) ($order->loyalty_points_earned ?? 0);
        $loyaltyDiscount = (float) $order->loyaltyDiscountAmount()->amount();

        $reconciled = $this->reconcile(
            (float) $order->sub_total->amount(),
            $shipping,
            $tax,
            $couponDiscount + $plainDiscount,
            $loyaltyDiscount,
            (float) $order->total->amount(),
            $loyaltyEnabled,
            $loyaltyPointsRedeemed
        );

        return [
            'shipping' => $shipping,
            'tax' => $tax,
            'coupon_discount' => $couponDiscount,
            'plain_discount' => $plainDiscount,
            'loyalty_enabled' => $loyaltyEnabled,
            'loyalty_points_redeemed' => $loyaltyPointsRedeemed,
            'loyalty_points_earned' => $loyaltyPointsEarned,
            'loyalty_discount' => $loyaltyDiscount,
            'inferred_loyalty_discount' => $reconciled['inferred_loyalty_discount'],
            'inferred_other_discount' => $reconciled['inferred_other_discount'],
            'processing_fee' => $reconciled['processing_fee'],
        ];
    }


    /**
     * @return array{inferred_loyalty_discount: float, inferred_other_discount: float, processing_fee: float}
     */
    public function reconcile(
        float $subtotal,
        float $shipping,
        float $tax,
        float $storedDiscount,
        float $loyaltyDiscount,
        float $total,
        bool $loyaltyEnabled,
        int $loyaltyPointsRedeemed
    ): array {
        $accounted = $subtotal + $shipping + $tax - $storedDiscount - $loyaltyDiscount;
        $gap = round($accounted - $total, 4);
        $inferredLoyaltyDiscount = 0.0;
        $inferredOtherDiscount = 0.0;
        $processingFee = 0.0;

        if ($gap > 0.009) {
            if ($loyaltyEnabled && $loyaltyPointsRedeemed > 0 && $loyaltyDiscount <= 0) {
                $inferredLoyaltyDiscount = $gap;
            } else {
                $inferredOtherDiscount = $gap;
            }
        } elseif ($gap < -0.009) {
            $processingFee = abs($gap);
        }

        return [
            'inferred_loyalty_discount' => $inferredLoyaltyDiscount,
            'inferred_other_discount' => $inferredOtherDiscount,
            'processing_fee' => $processingFee,
        ];
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
                $lines[] = strtoupper(strip_tags($line['label'])).' : '.strip_tags($line['value']);
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
