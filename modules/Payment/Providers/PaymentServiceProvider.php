<?php

namespace Modules\Payment\Providers;

use Modules\Payment\Gateways\COD;
use Modules\Payment\Facades\Gateway;
use Illuminate\Support\ServiceProvider;
use Modules\Payment\Gateways\BankTransfer;
use Modules\Payment\Gateways\ChipGateway;
use Modules\Payment\Services\ChipCheckoutAvailability;
use Modules\Payment\Services\ChipPaymentMethodConfig;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!config('app.installed')) {
            return;
        }

        $this->registerChip();
        $this->registerCashOnDelivery();
        $this->registerBankTransfer();
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }


    private function enabled($paymentMethod)
    {
        if (app('inAdminPanel')) {
            return true;
        }

        return setting("{$paymentMethod}_enabled");
    }


    private function registerChip()
    {
        if (! $this->enabled('chip')) {
            return;
        }

        if (ChipCheckoutAvailability::showAllMethodsGateway()) {
            Gateway::register(ChipPaymentMethodConfig::METHOD_ALL, new ChipGateway(ChipPaymentMethodConfig::METHOD_ALL));
        }

        foreach (ChipPaymentMethodConfig::checkoutMethodKeys() as $methodKey) {
            $config = ChipPaymentMethodConfig::configFor($methodKey);

            if ($config !== null && setting($config['enabled_setting'])) {
                Gateway::register($methodKey, new ChipGateway($methodKey));
            }
        }
    }


    private function registerCashOnDelivery()
    {
        if ($this->enabled('cod')) {
            Gateway::register('cod', new COD());
        }
    }


    private function registerBankTransfer()
    {
        if ($this->enabled('bank_transfer')) {
            Gateway::register('bank_transfer', new BankTransfer());
        }
    }
}
