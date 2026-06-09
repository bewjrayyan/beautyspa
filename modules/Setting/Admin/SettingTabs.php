<?php

namespace Modules\Setting\Admin;

use Modules\Admin\Ui\Tabs;
use Modules\Setting\Admin\SettingTab;
use Modules\Setting\Services\AppVersionService;
use Modules\Setting\Services\ArtisanCommandService;
use Modules\Setting\Services\CatalogSyncService;
use Modules\Setting\Services\GitHubVersionService;
use Modules\Support\Locale;
use Modules\Support\Country;
use Modules\Support\TimeZone;
use Modules\Currency\Currency;
use Modules\User\Entities\Role;
use Modules\Media\Entities\File;
use Illuminate\Support\Facades\Cache;
use Nwidart\Modules\Facades\Module;

class SettingTabs extends Tabs
{
    /**
     * Make new tabs with groups.
     *
     * @return void
     */
    public function make()
    {
        $this->group('general_settings', trans('setting::settings.tabs.group.general_settings'))
            ->active()
            ->add($this->general())
            ->add($this->logo())
            ->add($this->maintenance())
            ->add($this->system())
            ->add($this->store())
            ->add($this->PWA())
            ->add($this->currency())
            ->add($this->sms())
            ->add($this->googleSheets())
            ->add($this->googleCalendar())
            ->add($this->mail())
            ->add($this->newsletter())
            ->add($this->googleRecaptcha())
            ->add($this->customCssJs());

        $this->group('social_logins', trans('setting::settings.tabs.group.social_logins'))
            ->add($this->facebook())
            ->add($this->google())
            ->add($this->whatsappOtp());

        if (Module::isEnabled('Loyalty')) {
            $this->group('loyalty_settings', trans('loyalty::settings.group'))
                ->add($this->loyalty());
        }

        if (Module::isEnabled('SpecialGift')) {
            $this->group('specialgift_settings', trans('specialgift::settings.group'))
                ->add($this->specialgift());
        }

        $this->group('shipping_methods', trans('setting::settings.tabs.group.shipping_methods'))
            ->add($this->freeShipping())
            ->add($this->localPickup())
            ->add($this->flatRate());

        $this->group('payment_methods', trans('setting::settings.tabs.group.payment_methods'))
            ->add($this->paypal())
            ->add($this->stripe())
            ->add($this->authorizenet())
            ->add($this->flutterwave())
            ->add($this->chip())
            ->add($this->cod())
            ->add($this->bankTransfer())
            ->add($this->checkPayment());
    }


    private function general()
    {
        return tap(new SettingTab('general', trans('setting::settings.tabs.general')), function (SettingTab $tab) {
            $tab->active();
            $tab->weight(5);

            $tab->fields(['supported_countries.*', 'default_country', 'default_timezone', 'customer_role']);

            $tab->view('setting::admin.settings.tabs.general', [
                'countries' => Country::all(),
                'timeZones' => TimeZone::all(),
                'roles' => Role::list(),
            ]);
        });
    }

    private function logo()
    {
        return tap(new SettingTab('logo', trans('setting::settings.tabs.logo')), function (SettingTab $tab) {
            $tab->weight(10);

            $tab->fields(['translatable.admin_logo', 'translatable.admin_small_logo']);

            $tab->view('setting::admin.settings.tabs.logo', [
                'logo' => $this->getMedia(setting('admin_logo')),
                'shortLogo' => $this->getMedia(setting('admin_small_logo')),
            ]);
        });
    }


    private function maintenance()
    {
        return tap(new SettingTab('maintenance', trans('setting::settings.tabs.maintenance')), function (SettingTab $tab) {
            $tab->weight(7);

            $tab->fields(['maintenance_mode']);

            $tab->view('setting::admin.settings.tabs.maintenance');
        });
    }


    private function system()
    {
        return tap(new SettingTab('system', trans('setting::settings.tabs.system')), function (SettingTab $tab) {
            $tab->weight(8);

            $appVersion = app(AppVersionService::class);
            $artisanCommands = app(ArtisanCommandService::class);
            $githubVersion = app(GitHubVersionService::class);

            try {
                // Never fetch from origin on page load — shared hosting can timeout and return 500.
                $git = $appVersion->gitInfo(false);
                $artisanButtons = $artisanCommands->buttons();
            } catch (\Throwable) {
                $git = ['available' => false];
                $artisanButtons = [];
            }

            $catalogSync = class_exists(CatalogSyncService::class)
                ? app(CatalogSyncService::class)
                : null;

            $tab->view('setting::admin.settings.tabs.system', [
                'appVersionMeta' => [
                    'local_version' => $appVersion->codeVersion(),
                    'git' => $git,
                    'github' => $githubVersion->cachedCheck(),
                ],
                'artisanCommands' => $artisanButtons,
                'catalogSync' => [
                    'export_url' => $catalogSync?->exportUrl(),
                    'token_configured' => trim((string) config('setting.catalog_sync.token')) !== '',
                    'source_url' => setting('catalog_sync_source_url') ?: config('setting.catalog_sync.default_source_url'),
                    'bundle_exists' => $catalogSync?->bundleExists() ?? false,
                ],
            ]);
        });
    }


    private function store()
    {
        return tap(new SettingTab('store', trans('setting::settings.tabs.store')), function (SettingTab $tab) {
            $tab->weight(10);

            $tab->fields(['translatable.store_name', 'translatable.store_tagline', 'store_phone', 'store_email', 'store_address_1', 'store_address_2', 'store_city', 'store_country', 'store_state', 'store_zip']);

            $tab->view('setting::admin.settings.tabs.store', [
                'countries' => Country::all(),
            ]);
        });
    }


    private function PWA()
    {
        return tap(new SettingTab('pwa', trans('setting::settings.tabs.pwa')), function (SettingTab $tab) {
            $tab->weight(15);

            $tab->fields([
                'pwa_icon',
                'pwa_theme_color',
                'pwa_background_color',
                'pwa_status_bar',
                'pwa_display',
                'pwa_orientation',
                'translatable.pwa_direction',
            ]);

            $directions = [
                'ltr' => trans('setting::settings.form.pwa_directions.ltr'),
                'rtl' => trans('setting::settings.form.pwa_directions.rtl'),
                'auto' => trans('setting::settings.form.pwa_directions.auto'),
            ];

            $orientations = [
                'any' => trans('setting::settings.form.pwa_orientations.any'),
                'natural' => trans('setting::settings.form.pwa_orientations.natural'),
                'landscape' => trans('setting::settings.form.pwa_orientations.landscape'),
                'portrait' => trans('setting::settings.form.pwa_orientations.portrait'),
                'portrait-primary' => trans('setting::settings.form.pwa_orientations.portrait-primary'),
                'portrait-secondary' => trans('setting::settings.form.pwa_orientations.portrait-secondary'),
                'landscape-primary' => trans('setting::settings.form.pwa_orientations.landscape-primary'),
                'landscape-secondary' => trans('setting::settings.form.pwa_orientations.landscape-secondary'),
            ];

            $displays = [
                'fullscreen' => trans('setting::settings.form.pwa_displays.fullscreen'),
                'standalone' => trans('setting::settings.form.pwa_displays.standalone'),
                'minimal-ui' => trans('setting::settings.form.pwa_displays.minimal-ui'),
                'browser' => trans('setting::settings.form.pwa_displays.browser'),
            ];

            $tab->view('setting::admin.settings.tabs.pwa', [
                'icon' => $this->getMedia(setting('pwa_icon')),
                'directions' => $directions,
                'orientations' => $orientations,
                'displays' => $displays,
            ]);
        });
    }


    private function getMedia($fileId)
    {
        try {
            return Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
                return File::findOrNew($fileId);
            });
        } catch (\Throwable) {
            return File::findOrNew($fileId);
        }
    }


    private function currency()
    {
        return tap(new SettingTab('currency', trans('setting::settings.tabs.currency')), function (SettingTab $tab) {
            $tab->weight(20);

            $tab->fields(['supported_currencies.*', 'default_currency', 'currency_rate_exchange_service', 'fixer_access_key', 'forge_api_key', 'currency_data_feed_api_key', 'auto_refresh_currency_rates', 'auto_refresh_currency_rate_frequency']);

            $tab->view('setting::admin.settings.tabs.currency', [
                'currencies' => Currency::names(),
                'currencyRateExchangeServices' => $this->getCurrencyRateExchangeServices(),
            ]);
        });
    }


    private function getCurrencyRateExchangeServices()
    {
        $currencyRateExchangeServices = ['' => trans('setting::settings.form.select_service')];

        $currencyRateExchangeServices += trans('currency::services');

        return $currencyRateExchangeServices;
    }


    private function sms()
    {
        return tap(new SettingTab('sms', trans('setting::settings.tabs.whatsapp')), function (SettingTab $tab) {
            $tab->weight(25);

            $tab->fields([
                'onesender_enabled',
                'onesender_api_url',
                'onesender_api_key',
                'onesender_admin_phones',
                'onesender_whatsapp_group_id',
                'onesender_sending_paused',
                'onesender_dedupe_enabled',
                'onesender_dedupe_minutes',
                'onesender_outbound_queue_enabled',
                'onesender_outbound_delay_seconds',
                'whatsapp_group_staff_name',
                'whatsapp_order_tracking_url',
                'whatsapp_completed_group_enabled',
                'whatsapp_completed_beautician_enabled',
                'whatsapp_beautician_new_booking_enabled',
                'whatsapp_beautician_reminder_enabled',
                'whatsapp_beautician_reminder_minutes',
                'welcome_sms',
                'whatsapp_customer_reminder_enabled',
                'whatsapp_customer_reminder_minutes',
                'whatsapp_customer_completed_enabled',
                'whatsapp_customer_completed_message',
                'whatsapp_customer_followup_enabled',
                'whatsapp_customer_followup_days',
                'whatsapp_customer_followup_message',
                'new_order_admin_sms',
                'new_order_sms',
                'sms_order_statuses',
            ]);

            $tab->view('setting::admin.settings.tabs.sms', [
                'orderStatuses' => trans('order::statuses'),
            ]);
        });
    }


    private function googleSheets()
    {
        return tap(new SettingTab('google_sheets', trans('setting::settings.tabs.google_sheets')), function (SettingTab $tab) {
            $tab->weight(26);

            $tab->fields([
                'google_service_account_json',
                'google_sheets_enabled',
                'google_spreadsheet_id',
                'google_sheet_gid',
                'google_sheet_name',
            ]);

            $tab->view('setting::admin.settings.tabs.google_sheets');
        });
    }


    private function googleCalendar()
    {
        return tap(new SettingTab('google_calendar', trans('setting::settings.tabs.google_calendar')), function (SettingTab $tab) {
            $tab->weight(27);

            $tab->fields([
                'google_calendar_enabled',
                'google_calendar_id',
            ]);

            $tab->view('setting::admin.settings.tabs.google_calendar');
        });
    }


    private function mail()
    {
        return tap(new SettingTab('mail', trans('setting::settings.tabs.mail')), function (SettingTab $tab) {
            $tab->weight(30);

            $tab->fields([
                'mail_from_address',
                'mail_from_name',
                'mail_host',
                'mail_port',
                'mail_username',
                'mail_password',
                'mail_encryption',
                'welcome_email',
                'admin_order_email',
                'invoice_email',
                'email_order_statuses',
            ]);

            $tab->view('setting::admin.settings.tabs.mail', [
                'encryptionProtocols' => $this->getMailEncryptionProtocols(),
                'orderStatuses' => trans('order::statuses'),
            ]);
        });
    }


    private function getMailEncryptionProtocols()
    {
        return ['' => trans('admin::admin.form.please_select')] + trans('setting::settings.form.mail_encryption_protocols');
    }


    private function newsletter()
    {
        return tap(new SettingTab('newsletter', trans('setting::settings.tabs.newsletter')), function (SettingTab $tab) {
            $tab->weight(32);

            $tab->fields(['newsletter_enabled', 'mailchimp_api_key', 'mailchimp_list_id']);

            $tab->view('setting::admin.settings.tabs.newsletter');
        });
    }


    private function googleRecaptcha()
    {
        return tap(new SettingTab('google_recaptcha', trans('setting::settings.tabs.google_recaptcha')), function (SettingTab $tab) {
            $tab->weight(35);

            $tab->fields(['google_recaptcha_enabled', 'google_recaptcha_site_key', 'google_recaptcha_secret_key']);

            $tab->view('setting::admin.settings.tabs.google_recaptcha');
        });
    }


    private function customCssJs()
    {
        return tap(new SettingTab('custom_css_js', trans('setting::settings.tabs.custom_css_js')), function (SettingTab $tab) {
            $tab->weight(40);

            $tab->fields(['custom_header_assets', 'custom_footer_assets']);

            $tab->view('setting::admin.settings.tabs.custom_css_js');
        });
    }


    private function facebook()
    {
        return tap(new SettingTab('facebook', trans('setting::settings.tabs.facebook')), function (SettingTab $tab) {
            $tab->weight(41);

            $tab->fields(['facebook_login_enabled', 'translatable.facebook_login_label', 'facebook_login_app_id', 'facebook_login_app_secret']);

            $tab->view('setting::admin.settings.tabs.facebook');
        });
    }


    private function google()
    {
        return tap(new SettingTab('google', trans('setting::settings.tabs.google')), function (SettingTab $tab) {
            $tab->weight(42);

            $tab->fields(['google_login_enabled', 'translatable.google_login_label', 'google_login_client_id', 'google_login_client_secret']);

            $tab->view('setting::admin.settings.tabs.google');
        });
    }


    private function whatsappOtp()
    {
        return tap(new SettingTab('whatsapp_otp', trans('setting::settings.tabs.whatsapp_otp')), function (SettingTab $tab) {
            $tab->weight(43);

            $tab->fields([
                'whatsapp_otp_login_enabled',
                'translatable.whatsapp_otp_login_label',
                'whatsapp_otp_expiry_minutes',
            ]);

            $tab->view('setting::admin.settings.tabs.whatsapp_otp');
        });
    }


    private function specialgift()
    {
        return tap(new SettingTab('specialgift', trans('specialgift::settings.tab')), function (SettingTab $tab) {
            $tab->weight(45);
            $tab->customLayout();

            $tab->fields([
                'specialgift_enabled',
                'specialgift_voucher_background',
                'specialgift_message_template',
            ]);

            $tab->view('specialgift::admin.settings.specialgift', [
                'voucherBackground' => File::findOrNew(setting('specialgift_voucher_background')),
            ]);
        });
    }


    private function loyalty()
    {
        return tap(new SettingTab('loyalty', trans('loyalty::settings.tab')), function (SettingTab $tab) {
            $tab->weight(44);
            $tab->customLayout();

            $tab->fields([
                'loyalty_earn_rate_per_rm',
                'loyalty_point_value_rm',
                'loyalty_max_redeem_percent',
                'loyalty_points_expire_months',
                'loyalty_hold_minutes',
                'loyalty_allow_with_coupon',
                'loyalty_birthday_bonus_enabled',
                'loyalty_birthday_bonus_points',
                'loyalty_referral_enabled',
                'loyalty_referral_bonus_referrer',
                'loyalty_referral_bonus_referee',
                'loyalty_expiring_notify_days',
                'loyalty_whatsapp_tier_upgrade',
                'loyalty_whatsapp_points_earned',
                'loyalty_whatsapp_points_expiring',
                'loyalty_whatsapp_birthday_bonus',
                'loyalty_whatsapp_referral_bonus',
            ]);

            $tab->view('loyalty::admin.settings.loyalty');
        });
    }


    private function freeShipping()
    {
        return tap(new SettingTab('free_shipping', trans('setting::settings.tabs.free_shipping')), function (SettingTab $tab) {
            $tab->weight(50);

            $tab->fields(['free_shipping_enabled', 'translatable.free_shipping_label']);

            $tab->view('setting::admin.settings.tabs.free_shipping');
        });
    }


    private function localPickup()
    {
        return tap(new SettingTab('local_pickup', trans('setting::settings.tabs.local_pickup')), function (SettingTab $tab) {
            $tab->weight(55);

            $tab->fields(['local_pickup_enabled', 'translatable.local_pickup_label', 'local_pickup_cost']);

            $tab->view('setting::admin.settings.tabs.local_pickup');
        });
    }


    private function flatRate()
    {
        return tap(new SettingTab('flat_rate', trans('setting::settings.tabs.flat_rate')), function (SettingTab $tab) {
            $tab->weight(60);

            $tab->fields(['flat_rate_enabled', 'translatable.flat_rate_label', 'flat_rate_cost']);

            $tab->view('setting::admin.settings.tabs.flat_rate');
        });
    }


    private function paypal()
    {
        return tap(new SettingTab('paypal', trans('setting::settings.tabs.paypal')), function (SettingTab $tab) {
            $tab->weight(61);

            $tab->fields(['paypal_enabled', 'translatable.paypal_label', 'translatable.paypal_description', 'paypal_env', 'paypal_client_id', 'paypal_secret']);

            $tab->view('setting::admin.settings.tabs.paypal');
        });
    }


    private function stripe()
    {
        return tap(new SettingTab('stripe', trans('setting::settings.tabs.stripe')), function (SettingTab $tab) {
            $tab->weight(62);

            $tab->fields(['stripe_enabled', 'translatable.stripe_label', 'translatable.stripe_description', 'stripe_publishable_key', 'stripe_secret_key', 'stripe_integration_type']);

            $tab->view('setting::admin.settings.tabs.stripe', [
                'stripe_integration_types' => [
                    'embedded_form' => 'Embedded Form',
                    'hosted_page' => 'Hosted Page'
                ]
            ]);
        });
    }


    private function authorizenet()
    {
        return tap(new SettingTab('authorizenet', trans('setting::settings.tabs.authorizenet')), function (SettingTab $tab) {
            $tab->weight(67);

            $tab->fields(['authorizenet_enabled', 'translatable.authorizenet_label', 'translatable.authorizenet_description', 'authorizenet_test_mode', 'authorizenet_merchant_login_id', 'authorizenet_merchant_transaction_key']);

            $tab->view('setting::admin.settings.tabs.authorizenet');
        });
    }


    private function flutterwave()
    {
        return tap(new SettingTab('flutterwave', trans('setting::settings.tabs.flutterwave')), function (SettingTab $tab) {
            $tab->weight(69);

            $tab->fields(['flutterwave_enabled', 'translatable.flutterwave_label', 'translatable.flutterwave_description', 'flutterwave_test_mode', 'flutterwave_public_key', 'flutterwave_secret_key', 'flutterwave_encryption_key']);

            $tab->view('setting::admin.settings.tabs.flutterwave');
        });
    }


    private function chip()
    {
        return tap(new SettingTab('chip', trans('setting::settings.tabs.chip')), function (SettingTab $tab) {
            $tab->weight(70.5);

            $tab->fields([
                'chip_enabled',
                'translatable.chip_label',
                'translatable.chip_description',
                'chip_test_mode',
                'chip_brand_id',
                'chip_api_key',
                'chip_webhook_url',
                'chip_webhook_secret',
                'chip_all_methods_enabled',
                'chip_fpx_enabled',
                'translatable.chip_fpx_label',
                'translatable.chip_fpx_description',
                'chip_fpx_surcharge',
                'chip_fpx_whitelist',
                'chip_card_enabled',
                'translatable.chip_card_label',
                'translatable.chip_card_description',
                'chip_card_surcharge',
                'chip_card_whitelist',
                'chip_atome_enabled',
                'translatable.chip_atome_label',
                'translatable.chip_atome_description',
                'chip_atome_surcharge',
                'chip_atome_whitelist',
            ]);

            $tab->view('setting::admin.settings.tabs.chip');
        });
    }


    private function cod()
    {
        return tap(new SettingTab('cod', trans('setting::settings.tabs.cod')), function (SettingTab $tab) {
            $tab->weight(72);

            $tab->fields(['cod_enabled', 'translatable.cod_label', 'translatable.cod_description']);

            $tab->view('setting::admin.settings.tabs.cod');
        });
    }


    private function bankTransfer()
    {
        return tap(new SettingTab('bank_transfer', trans('setting::settings.tabs.bank_transfer')), function (SettingTab $tab) {
            $tab->weight(73);

            $tab->fields(['bank_transfer_enabled', 'translatable.bank_transfer_label', 'translatable.bank_transfer_description', 'translatable.bank_transfer_instructions']);

            $tab->view('setting::admin.settings.tabs.bank_transfer');
        });
    }


    private function checkPayment()
    {
        return tap(new SettingTab('check_payment', trans('setting::settings.tabs.check_payment')), function (SettingTab $tab) {
            $tab->weight(74);

            $tab->fields(['check_payment_enabled', 'translatable.check_payment_label', 'translatable.check_payment_description', 'translatable.check_payment_instructions']);

            $tab->view('setting::admin.settings.tabs.check_payment');
        });
    }


    /**
     * @param array $data
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render($data = [])
    {
        $this->buttonOffset = false;

        return $this->renderSettings($data);
    }
}
