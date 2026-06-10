<?php

namespace Modules\Setting\Admin;

use Modules\Admin\Ui\Tab;

class SettingTab extends Tab
{
    protected bool $usesCustomLayout = false;

    /**
     * Font Awesome 4.7 icon classes (admin bundle does not ship FA5).
     *
     * @var array<string, string>
     */
    private const NAV_ICONS = [
        'general' => 'fa-sliders',
        'logo' => 'fa-image',
        'maintenance' => 'fa-wrench',
        'system' => 'fa-server',
        'store' => 'fa-shopping-bag',
        'pwa' => 'fa-mobile',
        'currency' => 'fa-money',
        'sms' => 'fa-comments',
        'google_sheets' => 'fa-table',
        'google_calendar' => 'fa-calendar',
        'mail' => 'fa-envelope',
        'newsletter' => 'fa-newspaper-o',
        'google_recaptcha' => 'fa-shield',
        'custom_css_js' => 'fa-code',
        'facebook' => 'fa-facebook',
        'google' => 'fa-google',
        'whatsapp_otp' => 'fa-whatsapp',
        'loyalty' => 'fa-gift',
        'specialgift' => 'fa-ticket',
        'free_shipping' => 'fa-truck',
        'local_pickup' => 'fa-map-marker',
        'flat_rate' => 'fa-send',
        'paypal' => 'fa-paypal',
        'stripe' => 'fa-credit-card',
        'authorizenet' => 'fa-credit-card',
        'flutterwave' => 'fa-credit-card',
        'chip' => 'fa-bank',
        'cod' => 'fa-money',
        'bank_transfer' => 'fa-university',
        'check_payment' => 'fa-check-square-o',
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
