<?php

namespace Modules\Setting\Admin;

use Modules\Admin\Ui\Tab;

class SettingTab extends Tab
{
    protected bool $usesCustomLayout = false;

    /**
     * @var array<string, string>
     */
    private const NAV_ICONS = [
        'general' => 'fa-sliders',
        'logo' => 'fa-image',
        'maintenance' => 'fa-wrench',
        'system' => 'fa-server',
        'store' => 'fa-store',
        'pwa' => 'fa-mobile-alt',
        'currency' => 'fa-money-bill-wave',
        'sms' => 'fa-comment-dots',
        'google_sheets' => 'fa-table',
        'google_calendar' => 'fa-calendar-alt',
        'mail' => 'fa-envelope',
        'newsletter' => 'fa-newspaper',
        'google_recaptcha' => 'fa-shield-alt',
        'custom_css_js' => 'fa-code',
        'facebook' => 'fa-facebook',
        'google' => 'fa-google',
        'whatsapp_otp' => 'fa-whatsapp',
        'loyalty' => 'fa-gift',
        'specialgift' => 'fa-ticket-alt',
        'free_shipping' => 'fa-truck',
        'local_pickup' => 'fa-map-marker-alt',
        'flat_rate' => 'fa-shipping-fast',
        'paypal' => 'fa-paypal',
        'stripe' => 'fa-credit-card',
        'authorizenet' => 'fa-credit-card',
        'flutterwave' => 'fa-credit-card',
        'chip' => 'fa-wallet',
        'cod' => 'fa-money-bill',
        'bank_transfer' => 'fa-university',
        'check_payment' => 'fa-money-check',
    ];

    protected function navIcon(): ?string
    {
        return self::NAV_ICONS[$this->name] ?? 'fa-cog';
    }

    /**
     * Skip the default settings tab shell (e.g. fully custom tab UI).
     */
    public function customLayout(bool $custom = true): self
    {
        $this->usesCustomLayout = $custom;

        return $this;
    }

    public function getMainView($data = [])
    {
        $content = parent::getMainView($data);

        if ($this->usesCustomLayout || $this->hasCustomLayoutMarkup($content)) {
            return $content;
        }

        return view('setting::admin.settings.partials.tab-shell', [
            'content' => $content,
            'lead' => $this->resolveLead(),
        ])->render();
    }

    protected function hasCustomLayoutMarkup(string $content): bool
    {
        return str_contains($content, 'st-tab')
            || str_contains($content, 'sg-settings');
    }

    protected function resolveLead(): ?string
    {
        $key = "setting::settings.tab_leads.{$this->name}";
        $lead = trans($key);

        return $lead === $key ? null : $lead;
    }
}
