<?php

namespace Modules\Setting\Admin;

use Modules\Admin\Ui\Tab;

class SettingTab extends Tab
{
    protected bool $usesCustomLayout = false;

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
