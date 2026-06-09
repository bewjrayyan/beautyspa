<?php

namespace AestheticCart\Install;

use Modules\User\Entities\Role;
use Modules\Setting\Entities\Setting;
use Modules\Currency\Entities\CurrencyRate;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

class App
{
    public function setup($request): void
    {
        $this->setEnvVariables($request);
        $this->createCustomerRole();
        $this->setAppSettings($request);
        $this->createDefaultCurrencyRate();
    }


    private function setEnvVariables($request): void
    {
        $env = DotenvEditor::load();

        $env->setKey('APP_ENV', 'production');
        $env->setKey('APP_DEBUG', 'false');
        $env->setKey('APP_CACHE', 'true');
        $env->setKey('APP_URL', rtrim((string) $request['app_url'], '/'));
        $env->setKey('APP_TIMEZONE', 'Asia/Kuala_Lumpur');

        $env->save();
    }


    private function createCustomerRole(): void
    {
        Role::create(['name' => 'Customer']);
    }


    private function setAppSettings($request): void
    {
        Setting::setMany([
            'supported_countries' => ['MY'],
            'default_country' => 'MY',
            'supported_locales' => ['en', 'ms'],
            'default_locale' => 'en',
            'default_timezone' => 'Asia/Kuala_Lumpur',
            'customer_role' => 2,
            'reviews_enabled' => true,
            'auto_approve_reviews' => false,
            'cookie_bar_enabled' => true,
            'supported_currencies' => ['MYR'],
            'default_currency' => 'MYR',
            'send_order_invoice_email' => false,
            'newsletter_enabled' => false,
            'search_engine' => 'mysql',
            'local_pickup_cost' => 0,
            'flat_rate_cost' => 0,
            'app_version' => \AestheticCart\AestheticCart::VERSION,
            'translatable' => [
                'store_name' => $request['store_name'],
                'pwa_direction' => 'auto',
                'free_shipping_label' => 'Free Shipping',
                'local_pickup_label' => 'Local Pickup',
                'flat_rate_label' => 'Flat Rate',
                'paypal_label' => 'PayPal',
                'paypal_description' => 'Pay via your PayPal account.',
                'stripe_label' => 'Stripe',
                'stripe_description' => 'Pay via credit or debit card.',
                'cod_label' => 'Cash On Delivery',
                'cod_description' => 'Pay with cash upon delivery.',
                'bank_transfer_label' => 'Bank Transfer',
                'bank_transfer_description' => 'Make your payment directly into our bank account.',
                'check_payment_label' => 'Check / Money Order',
                'check_payment_description' => 'Please send a check to our store.',
            ],
            'storefront_copyright_text' => 'Copyright © <a href="{{ store_url }}">{{ store_name }}</a> {{ year }}. All rights reserved.',
        ]);
    }


    private function createDefaultCurrencyRate(): void
    {
        CurrencyRate::create(['currency' => 'MYR', 'rate' => 1]);
    }
}
