<?php

namespace AestheticCart\Install;

use AestheticCart\AestheticCart;
use Modules\Currency\Entities\CurrencyRate;
use Modules\Loyalty\Support\LoyaltySettingsDefaults;
use Modules\Payment\Support\ChipPaymentSettingsDefaults;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Support\WhatsAppNotificationDefaults;
use Modules\User\Entities\Role;

class ImmaSeriLarisAdminSettings
{
    /**
     * Restore Admin → Settings fields wiped or never seeded after db:restore-dev.
     *
     * @return array<int, string> Keys that were written
     */
    public function apply(bool $forceStoreContact = false): array
    {
        $applied = [];

        foreach ($this->coreSettings() as $key => $value) {
            if ($this->applyPlain($key, $value, $forceStoreContact)) {
                $applied[] = $key;
            }
        }

        foreach ($this->translatableSettings() as $key => $value) {
            if ($this->applyTranslatable($key, $value, $forceStoreContact)) {
                $applied[] = "translatable.{$key}";
            }
        }

        $applied = array_merge($applied, WhatsAppNotificationDefaults::applyMissingOnly());
        $applied = array_merge($applied, LoyaltySettingsDefaults::applyMissingOnly());
        $applied = array_merge($applied, ChipPaymentSettingsDefaults::applyMissingOnly());

        if (CurrencyRate::where('currency', 'MYR')->doesntExist()) {
            CurrencyRate::create(['currency' => 'MYR', 'rate' => 1]);
            $applied[] = 'currency_rate.MYR';
        }

        $this->syncStorefrontLogoFromAdmin($applied);

        return array_values(array_unique($applied));
    }


    /**
     * @return array<string, mixed>
     */
    private function coreSettings(): array
    {
        $customerRole = Role::whereTranslation('name', 'Customer')->value('id') ?? 2;

        return [
            'supported_countries' => ['MY'],
            'default_country' => 'MY',
            'supported_locales' => ['en', 'ms'],
            'default_locale' => 'en',
            'default_timezone' => 'Asia/Kuala_Lumpur',
            'customer_role' => $customerRole,
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
            'maintenance_mode' => false,
            'app_version' => AestheticCart::VERSION,
            'store_email' => 'booking@immaserilaris.com',
            'store_phone' => '+601133411016',
            'store_address_1' => 'IMMA Seri Laris Beauty & Wellness',
            'store_address_2' => '',
            'store_city' => 'Kajang',
            'store_country' => 'MY',
            'store_state' => 'SGR',
            'store_zip' => '43000',
            'store_phone_hide' => false,
            'store_email_hide' => false,
            'mail_from_address' => 'booking@immaserilaris.com',
            'mail_from_name' => 'Imma Seri Laris',
            'mail_host' => '',
            'mail_port' => 587,
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'welcome_email' => false,
            'admin_order_email' => false,
            'invoice_email' => false,
            'email_order_statuses' => [],
            'google_recaptcha_enabled' => false,
            'google_recaptcha_site_key' => '',
            'google_recaptcha_secret_key' => '',
            'facebook_login_enabled' => false,
            'google_login_enabled' => false,
            'whatsapp_otp_login_enabled' => false,
            'whatsapp_otp_expiry_minutes' => 10,
            'free_shipping_enabled' => false,
            'local_pickup_enabled' => true,
            'flat_rate_enabled' => false,
            'cod_enabled' => true,
            'bank_transfer_enabled' => true,
            'check_payment_enabled' => false,
            'paypal_enabled' => false,
            'stripe_enabled' => false,
            'storefront_copyright_text' => 'Copyright © <a href="{{ store_url }}">{{ store_name }}</a> {{ year }}. All rights reserved.',
        ];
    }


    /**
     * @return array<string, string>
     */
    private function translatableSettings(): array
    {
        return [
            'store_name' => 'Imma Seri Laris',
            'store_tagline' => 'Spa, Aesthetic & Cosmetik — Book Your Treatment Online',
            'store_description' => 'IMMA Seri Laris offers professional spa treatments, aesthetic procedures, and cosmetik products in Malaysia. Book online or visit our clinic.',
            'pwa_direction' => 'auto',
            'free_shipping_label' => 'Free Shipping',
            'local_pickup_label' => 'In-Clinic Pickup',
            'flat_rate_label' => 'Flat Rate',
            'paypal_label' => 'PayPal',
            'paypal_description' => 'Pay via your PayPal account.',
            'stripe_label' => 'Stripe',
            'stripe_description' => 'Pay via credit or debit card.',
            'cod_label' => 'Cash On Delivery',
            'cod_description' => 'Pay with cash upon delivery.',
            'bank_transfer_label' => 'Bank Transfer',
            'bank_transfer_description' => 'Make your payment directly into our bank account. Please use your Order ID as the payment reference.',
            'check_payment_label' => 'Check / Money Order',
            'check_payment_description' => 'Please send a check to our store.',
            'facebook_login_label' => 'Facebook',
            'google_login_label' => 'Google',
            'whatsapp_otp_login_label' => 'WhatsApp OTP',
        ];
    }


    private function applyPlain(string $key, mixed $value, bool $force): bool
    {
        if (! $force && ! $this->isMissing($key)) {
            return false;
        }

        Setting::set($key, $value);

        return true;
    }


    private function applyTranslatable(string $key, mixed $value, bool $force): bool
    {
        if (! $force && ! $this->isMissing($key)) {
            return false;
        }

        Setting::setMany(['translatable' => [$key => $value]]);

        return true;
    }


    private function isMissing(string $key): bool
    {
        if (! Setting::has($key)) {
            return true;
        }

        $current = Setting::get($key);

        if ($current === null) {
            return true;
        }

        if (is_string($current) && trim($current) === '') {
            return true;
        }

        if (is_array($current) && $current === []) {
            return true;
        }

        return false;
    }


    /**
     * @param array<int, string> $applied
     */
    private function syncStorefrontLogoFromAdmin(array &$applied): void
    {
        $adminLogo = setting('admin_logo');

        if (! $adminLogo) {
            return;
        }

        if ($this->isMissing('storefront_header_logo')) {
            Setting::setMany([
                'translatable' => [
                    'storefront_header_logo' => $adminLogo,
                ],
            ]);
            $applied[] = 'translatable.storefront_header_logo';
        }
    }
}
