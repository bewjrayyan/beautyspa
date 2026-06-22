<?php

namespace Modules\Payment\Services;

use Modules\Order\Entities\Order;
use Modules\Support\Money;

class ChipSurchargeCalculator
{
    public function forGateway(string $gatewayKey, ?Order $order = null, ?int $amountSubunit = null): int
    {
        $config = ChipPaymentMethodConfig::configFor($gatewayKey);

        if ($config !== null) {
            return $this->forMethod($gatewayKey, $order, $amountSubunit);
        }

        return $this->maxEnabledMethodSurcharge($order, $amountSubunit);
    }


    private function forMethod(string $gatewayKey, ?Order $order, ?int $amountSubunit): int
    {
        $config = ChipPaymentMethodConfig::configFor($gatewayKey);

        if ($config === null) {
            return 0;
        }

        if (($config['surcharge_type'] ?? 'flat') === 'percent') {
            $basis = $amountSubunit ?? $this->basisSubunit($order);

            return $this->percentSubunit($gatewayKey, $basis);
        }

        return $this->flatSubunit($gatewayKey);
    }


    private function flatSubunit(string $gatewayKey): int
    {
        $config = ChipPaymentMethodConfig::configFor($gatewayKey);

        if ($config === null) {
            return 0;
        }

        $configured = setting($config['surcharge_setting']);

        if ($configured === null || $configured === '') {
            return max(0, (int) $config['default_surcharge']);
        }

        return max(0, (int) round((float) $configured));
    }


    private function percentSubunit(string $gatewayKey, ?int $amountSubunit): int
    {
        if (! $amountSubunit || $amountSubunit <= 0) {
            return 0;
        }

        $config = ChipPaymentMethodConfig::configFor($gatewayKey);

        if ($config === null) {
            return 0;
        }

        $percentSetting = $config['percent_setting'] ?? null;
        $configured = $percentSetting ? setting($percentSetting) : null;

        if ($configured === null || $configured === '') {
            $percent = (float) ($config['default_surcharge_percent'] ?? 0);
        } else {
            $percent = (float) $configured;
        }

        if ($percent <= 0) {
            return 0;
        }

        return (int) max(0, round($amountSubunit * $percent / 100));
    }


    private function basisSubunit(?Order $order): int
    {
        if (! $order) {
            return 0;
        }

        $order->loadMissing(['taxes']);

        $amount = $order->sub_total->amount();

        if ($order->hasShippingMethod()) {
            $amount += $order->shipping_cost->amount();
        }

        foreach ($order->taxes as $tax) {
            $amount += $tax->order_tax->amount->amount();
        }

        if ($order->hasCoupon()) {
            $amount -= $order->discount->amount();
        }

        if ($order->hasLoyaltyRedemption()) {
            $amount -= $order->loyaltyDiscountAmount()->amount();
        }

        return Money::inDefaultCurrency(max(0, $amount))
            ->convert($order->currency, $order->currency_rate)
            ->subunit();
    }


    private function maxEnabledMethodSurcharge(?Order $order = null, ?int $amountSubunit = null): int
    {
        $max = 0;

        foreach (ChipPaymentMethodConfig::checkoutMethodKeys() as $methodKey) {
            if (! setting($methodKey . '_enabled')) {
                continue;
            }

            $max = max($max, $this->forMethod($methodKey, $order, $amountSubunit));
        }

        return $max;
    }
}
