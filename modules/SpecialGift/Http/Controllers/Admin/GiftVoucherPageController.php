<?php

namespace Modules\SpecialGift\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Media\Entities\File;
use Modules\SpecialGift\Http\Requests\UpdateGiftVoucherSettingsRequest;
use Modules\SpecialGift\Services\SpecialGiftConfig;
use Modules\SpecialGift\Support\SpecialGiftPageSettings;

class GiftVoucherPageController
{
    public function __construct(
        private SpecialGiftConfig $config,
    ) {}

    public function content(): View
    {
        SpecialGiftPageSettings::applyMissingOnly();

        return view('specialgift::admin.content.edit', [
            'activeTab' => 'content',
            'settings' => SpecialGiftPageSettings::formSettings(),
            'sendGiftUrl' => route('specialgift.send.create'),
        ]);
    }


    public function design(): View
    {
        SpecialGiftPageSettings::applyMissingOnly();

        $settings = SpecialGiftPageSettings::formSettings();
        $storeThemeColor = function_exists('storefront_theme_color') ? storefront_theme_color() : '#f274ac';

        return view('specialgift::admin.design.edit', [
            'activeTab' => 'design',
            'settings' => $settings,
            'storeThemeColor' => $storeThemeColor,
            'isCustomPreset' => ($settings['specialgift_page_preset'] ?? SpecialGiftPageSettings::PRESET_AESTHETIC) === SpecialGiftPageSettings::PRESET_CUSTOM,
            'sendGiftUrl' => route('specialgift.send.create'),
        ]);
    }


    public function settings(): View
    {
        SpecialGiftPageSettings::applyMissingOnly();

        $settings = SpecialGiftPageSettings::formSettings();
        $specialGiftSettings = $settings;
        $currentMessageTemplate = old('specialgift_message_template', $settings['specialgift_message_template'] ?? '');

        if (trim((string) $currentMessageTemplate) === '') {
            $specialGiftSettings['specialgift_message_template'] = trans('specialgift::settings.message_template_default');
        }

        return view('specialgift::admin.settings.edit', [
            'activeTab' => 'settings',
            'settings' => $settings,
            'specialGiftSettings' => $specialGiftSettings,
            'voucherBackground' => File::findOrNew(setting('specialgift_voucher_background')),
            'specialGiftConfig' => $this->config,
            'sendGiftUrl' => route('specialgift.send.create'),
        ]);
    }


    public function update(UpdateGiftVoucherSettingsRequest $request): RedirectResponse
    {
        $section = (string) $request->input('section', 'settings');
        $data = $request->validatedSettings();

        if ($section === 'design') {
            $preset = (string) ($data['specialgift_page_preset'] ?? SpecialGiftPageSettings::PRESET_CUSTOM);

            if ($preset !== SpecialGiftPageSettings::PRESET_CUSTOM) {
                $data = array_merge($data, SpecialGiftPageSettings::presetValues($preset));
            }
        }

        setting($data);

        $route = match ($section) {
            'content' => 'admin.gift_voucher_submissions.content',
            'design' => 'admin.gift_voucher_submissions.design',
            default => 'admin.gift_voucher_submissions.settings',
        };

        return redirect()
            ->route($route)
            ->with('success', trans('specialgift::admin.settings_saved'));
    }
}
