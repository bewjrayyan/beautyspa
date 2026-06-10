<?php

namespace Modules\Setting\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Support\Locale;
use Modules\Support\Country;
use Modules\Support\TimeZone;
use Modules\Currency\Currency;
use Modules\Setting\Services\ArtisanCommandService;
use Modules\Setting\Support\SettingTabScope;
use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;

class UpdateSettingRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'setting::attributes';

    /**
     * Array of attributes that should be merged with null
     * if attribute is not found in the current request.
     *
     * @var array
     */
    private $shouldCheck = ['sms_order_statuses', 'email_order_statuses'];


    protected function prepareForValidation(): void
    {
        if ($this->has('google_spreadsheet_id')) {
            $parsed = $this->parseGoogleSpreadsheetInput((string) $this->input('google_spreadsheet_id'));

            $this->merge([
                'google_spreadsheet_id' => $parsed['spreadsheet_id'],
                'google_sheet_gid' => $parsed['sheet_gid'],
            ]);
        }

        if (! $this->filled('onesender_api_key')
            && setting('onesender_api_key')
            && SettingTabScope::activeTab($this) === 'sms') {
            $this->merge(['onesender_api_key' => setting('onesender_api_key')]);
        }
    }


    /**
     * @return array{spreadsheet_id: string, sheet_gid: ?string}
     */
    private function parseGoogleSpreadsheetInput(string $input): array
    {
        $input = trim($input);

        if ($input === '') {
            return ['spreadsheet_id' => '', 'sheet_gid' => null];
        }

        $marker = '/spreadsheets/d/';
        $spreadsheetId = null;
        $sheetGid = null;

        if (str_contains($input, $marker)) {
            $start = strpos($input, $marker) + strlen($marker);
            $length = strcspn($input, '/?#', $start);
            $spreadsheetId = substr($input, $start, $length) ?: null;

            foreach (['?gid=', '&gid=', '#gid='] as $needle) {
                $pos = strpos($input, $needle);

                if ($pos === false) {
                    continue;
                }

                $gidStart = $pos + strlen($needle);
                $gid = '';

                for ($i = $gidStart, $len = strlen($input); $i < $len; $i++) {
                    if (! ctype_digit($input[$i])) {
                        break;
                    }

                    $gid .= $input[$i];
                }

                if ($gid !== '') {
                    $sheetGid = $gid;
                    break;
                }
            }
        }

        if ($spreadsheetId !== null) {
            return [
                'spreadsheet_id' => $spreadsheetId,
                'sheet_gid' => $sheetGid,
            ];
        }

        return [
            'spreadsheet_id' => $input,
            'sheet_gid' => null,
        ];
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->filled('artisan_action')) {
            return [
                'artisan_action' => ['required', 'in:'.implode(',', array_keys(app(ArtisanCommandService::class)->definitions()))],
            ];
        }

        if ($this->filled('app_version_action')) {
            return [
                'app_version_action' => ['required', 'in:pull_latest,check_github,sync_version,github_update'],
            ];
        }

        $tab = SettingTabScope::activeTab($this);

        if ($tab === null) {
            return $this->allRules();
        }

        return SettingTabScope::filterRules($this->allRules(), SettingTabScope::fieldsForTab($tab));
    }


    private function allRules(): array
    {
        return [
            'supported_countries.*' => ['required', Rule::in(Country::codes())],
            'default_country' => 'required|in_array:supported_countries.*',
            'default_timezone' => ['required', Rule::in(TimeZone::all())],
            'customer_role' => ['required', Rule::exists('roles', 'id')],
            'supported_currencies.*' => ['required', Rule::in(Currency::codes())],
            'default_currency' => 'required|in_array:supported_currencies.*',

            'translatable.store_name' => 'required',
            'store_phone' => ['required', new ValidPhone()],
            'store_email' => 'required|email',
            'store_country' => ['required', Rule::in(Country::codes())],

            'pwa_enabled' => 'required',
            'pwa_icon' => 'required_if:pwa_enabled,1',
            'pwa_theme_color' => ['regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'pwa_background_color' => ['regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'pwa_status_bar' => ['regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],

            'fixer_access_key' => 'required_if:currency_rate_exchange_service,fixer',
            'forge_api_key' => 'required_if:currency_rate_exchange_service,forge',
            'currency_data_feed_api_key' => 'required_if:currency_rate_exchange_service,currency_data_feed',
            'auto_refresh_currency_rates' => 'required|boolean',
            'auto_refresh_currency_rate_frequency' => [
                'required_if:auto_refresh_currency_rates,1',
                Rule::in($this->refreshFrequencies()),
            ],

            'onesender_enabled' => 'required|boolean',
            'onesender_api_url' => 'required_if:onesender_enabled,1|nullable|url',
            'onesender_api_key' => 'nullable|required_if:onesender_enabled,1',
            'onesender_admin_phones' => 'nullable',
            'onesender_whatsapp_group_id' => 'nullable|string',
            'onesender_sending_paused' => 'required|boolean',
            'onesender_dedupe_enabled' => 'required|boolean',
            'onesender_dedupe_minutes' => 'nullable|integer|min:1|max:10080',
            'onesender_outbound_queue_enabled' => 'required|boolean',
            'onesender_outbound_delay_seconds' => 'nullable|integer|min:0|max:3600',
            'whatsapp_group_staff_name' => 'nullable|string|max:100',
            'whatsapp_order_tracking_url' => 'nullable|url',
            'whatsapp_completed_group_enabled' => 'required|boolean',
            'whatsapp_completed_beautician_enabled' => 'required|boolean',
            'whatsapp_beautician_new_booking_enabled' => 'required|boolean',
            'whatsapp_beautician_reminder_enabled' => 'required|boolean',
            'whatsapp_beautician_reminder_minutes' => 'nullable|integer|min:15|max:1440',

            'google_service_account_json' => 'nullable|string',
            'google_sheets_enabled' => 'required|boolean',
            'google_spreadsheet_id' => 'required_if:google_sheets_enabled,1|nullable|string|max:500',
            'google_sheet_gid' => 'nullable|string|max:20',
            'google_sheet_name' => 'nullable|string|max:100',
            'google_calendar_enabled' => 'required|boolean',
            'google_calendar_id' => 'required_if:google_calendar_enabled,1|nullable|string|max:255',

            'welcome_sms' => 'required|boolean',
            'whatsapp_customer_reminder_enabled' => 'required|boolean',
            'whatsapp_customer_reminder_minutes' => 'nullable|integer|min:15|max:1440',
            'whatsapp_customer_completed_enabled' => 'required|boolean',
            'whatsapp_customer_completed_message' => 'nullable|string|max:2000',
            'whatsapp_customer_followup_enabled' => 'required|boolean',
            'whatsapp_customer_followup_days' => 'nullable|integer|min:1|max:90',
            'whatsapp_customer_followup_message' => 'nullable|string|max:2000',
            'new_order_admin_sms' => 'required|boolean',
            'new_order_sms' => 'required|boolean',
            'sms_order_statuses.*' => ['nullable', Rule::in($this->orderStatuses())],

            'mail_from_address' => 'nullable|email',
            'mail_encryption' => ['nullable', Rule::in($this->mailEncryptionProtocols())],

            'newsletter_enabled' => ['required', 'boolean'],
            'mailchimp_api_key' => ['required_if:newsletter_enabled,1'],
            'mailchimp_list_id' => ['required_if:newsletter_enabled,1'],

            'google_recaptcha_enabled' => ['required', 'boolean'],
            'google_recaptcha_site_key' => ['required_if:google_recatcha_enabled,1'],
            'google_recaptcha_secret_key' => ['required_if:google_recaptcha_enabled,1'],

            'facebook_login_enabled' => 'required|boolean',
            'facebook_login_app_id' => 'required_if:facebook_login_enabled,1',
            'facebook_login_app_secret' => 'required_if:facebook_login_enabled,1',

            'google_login_enabled' => 'required|boolean',
            'google_login_client_id' => 'required_if:google_login_enabled,1',
            'google_login_client_secret' => 'required_if:google_login_enabled,1',

            'whatsapp_otp_login_enabled' => 'required|boolean',
            'translatable.whatsapp_otp_login_label' => 'nullable',
            'whatsapp_otp_expiry_minutes' => 'required_if:whatsapp_otp_login_enabled,1|integer|min:1|max:30',

            'free_shipping_enabled' => 'required|boolean',
            'free_shipping_min_amount' => 'nullable|numeric',
            'translatable.free_shipping_label' => 'required_if:free_shipping_enabled,1',

            'local_pickup_enabled' => 'required|boolean',
            'translatable.local_pickup_label' => 'required_if:local_pickup_enabled,1',
            'local_pickup_cost' => ['required_if:local_pickup_enabled,1', 'nullable', 'numeric'],

            'flat_rate_enabled' => 'required|boolean',
            'translatable.flat_rate_label' => 'required_if:flat_rate_enabled,1',
            'flat_rate_cost' => ['required_if:flat_rate_enabled,1', 'nullable', 'numeric'],

            'paypal_enabled' => 'required|boolean',
            'translatable.paypal_label' => 'required_if:paypal_enabled,1',
            'translatable.paypal_description' => 'required_if:paypal_enabled,1',
            'paypal_test_mode' => 'required|boolean',
            'paypal_client_id' => 'required_if:paypal_enabled,1',
            'paypal_secret' => 'required_if:paypal_enabled,1',

            'stripe_enabled' => 'required|boolean',
            'translatable.stripe_label' => 'required_if:stripe_enabled,1',
            'translatable.stripe_description' => 'required_if:stripe_enabled,1',
            'stripe_publishable_key' => 'required_if:stripe_enabled,1',
            'stripe_secret_key' => 'required_if:stripe_enabled,1',

            'authorizenet_enabled' => 'required|boolean',
            'translatable.authorizenet_label' => 'required_if:authorizenet_enabled,1',
            'translatable.authorizenet_description' => 'required_if:authorizenet_enabled,1',
            'authorizenet_test_mode' => 'required|boolean',
            'authorizenet_merchant_login_id' => 'required_if:authorizenet_enabled,1',
            'authorizenet_merchant_transaction_key' => 'required_if:authorizenet_enabled,1',

            'flutterwave_enabled' => 'required|boolean',
            'translatable.flutterwave_label' => 'required_if:flutterwave_enabled,1',
            'translatable.flutterwave_description' => 'required_if:flutterwave_enabled,1',
            'flutterwave_test_mode' => 'required|boolean',
            'flutterwave_public_key' => 'required_if:flutterwave_enabled,1',
            'flutterwave_secret_key' => 'required_if:flutterwave_enabled,1',
            'flutterwave_encryption_key' => 'required_if:flutterwave_enabled,1',

            'chip_enabled' => 'required|boolean',
            'translatable.chip_label' => 'required_if:chip_enabled,1',
            'translatable.chip_description' => 'required_if:chip_enabled,1',
            'chip_test_mode' => 'required|boolean',
            'chip_brand_id' => 'required_if:chip_enabled,1',
            'chip_api_key' => 'required_if:chip_enabled,1',
            'chip_webhook_url' => 'nullable|url',
            'chip_webhook_secret' => 'nullable|string|max:255',
            'chip_all_methods_enabled' => 'required|boolean',
            'chip_fpx_enabled' => 'required|boolean',
            'translatable.chip_fpx_label' => 'required_if:chip_fpx_enabled,1',
            'translatable.chip_fpx_description' => 'required_if:chip_fpx_enabled,1',
            'chip_fpx_surcharge' => 'nullable|integer|min:0',
            'chip_fpx_whitelist' => 'nullable|string|max:500',
            'chip_card_enabled' => 'required|boolean',
            'translatable.chip_card_label' => 'required_if:chip_card_enabled,1',
            'translatable.chip_card_description' => 'required_if:chip_card_enabled,1',
            'chip_card_surcharge' => 'nullable|integer|min:0',
            'chip_card_whitelist' => 'nullable|string|max:500',
            'chip_atome_enabled' => 'required|boolean',
            'translatable.chip_atome_label' => 'required_if:chip_atome_enabled,1',
            'translatable.chip_atome_description' => 'required_if:chip_atome_enabled,1',
            'chip_atome_surcharge' => 'nullable|integer|min:0',
            'chip_atome_whitelist' => 'nullable|string|max:500',

            'cod_enabled' => 'required|boolean',
            'translatable.cod_label' => 'required_if:cod_enabled,1',
            'translatable.cod_description' => 'required_if:cod_enabled,1',

            'bank_transfer_enabled' => 'required|boolean',
            'translatable.bank_transfer_label' => 'required_if:bank_transfer_enabled,1',
            'translatable.bank_transfer_description' => 'required_if:bank_transfer_enabled,1',
            'translatable.bank_transfer_instructions' => 'required_if:bank_transfer_enabled,1',

            'check_payment_enabled' => 'required|boolean',
            'translatable.check_payment_label' => 'required_if:check_payment_enabled,1',
            'translatable.check_payment_description' => 'required_if:check_payment_enabled,1',
            'translatable.check_payment_instructions' => 'required_if:check_payment_enabled,1',

            'loyalty_earn_rate_per_rm' => 'required|numeric|min:0',
            'loyalty_point_value_rm' => 'required|numeric|min:0.01',
            'loyalty_max_redeem_percent' => 'required|numeric|min:1|max:100',
            'loyalty_points_expire_months' => 'required|integer|min:1|max:60',
            'loyalty_hold_minutes' => 'required|integer|min:5|max:120',
            'loyalty_allow_with_coupon' => 'required|boolean',
            'loyalty_birthday_bonus_enabled' => 'required|boolean',
            'loyalty_birthday_bonus_points' => 'required|integer|min:0',
            'loyalty_referral_enabled' => 'required|boolean',
            'loyalty_referral_bonus_referrer' => 'required|integer|min:0',
            'loyalty_referral_bonus_referee' => 'required|integer|min:0',
            'loyalty_expiring_notify_days' => 'required|integer|min:1|max:90',
            'loyalty_whatsapp_tier_upgrade' => 'required|boolean',
            'loyalty_whatsapp_points_earned' => 'required|boolean',
            'loyalty_whatsapp_points_expiring' => 'required|boolean',
            'loyalty_whatsapp_birthday_bonus' => 'required|boolean',
            'loyalty_whatsapp_referral_bonus' => 'required|boolean',

            'specialgift_enabled' => 'nullable|boolean',
            'specialgift_voucher_background' => 'nullable|integer',
            'specialgift_message_template' => 'nullable|string|max:2000',
        ];
    }


    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData()
    {
        $tab = SettingTabScope::activeTab($this);

        if ($tab === 'sms') {
            foreach ($this->shouldCheck as $attribute) {
                if (! $this->has($attribute)) {
                    $this->merge([$attribute => null]);
                }
            }
        }

        if ($tab === 'mail' && ! $this->has('email_order_statuses')) {
            $this->merge(['email_order_statuses' => null]);
        }

        return $this->all();
    }


    /**
     * Returns currency rate refresh frequencies..
     *
     * @return array
     */
    private function refreshFrequencies()
    {
        return array_keys(trans('setting::settings.form.auto_refresh_currency_rate_frequencies'));
    }


    /**
     * Returns order statuses.
     *
     * @return array
     */
    private function orderStatuses()
    {
        return array_keys(trans('order::statuses'));
    }


    /**
     * Returns mail encryption protocols.
     *
     * @return array
     */
    private function mailEncryptionProtocols()
    {
        return array_keys(trans('setting::settings.form.mail_encryption_protocols'));
    }
}
