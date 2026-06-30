<?php

namespace Modules\SpecialGift\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\SpecialGift\Support\SpecialGiftPageSettings;

class UpdateGiftVoucherSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAccess('admin.gift_voucher_submissions.settings') ?? false;
    }


    public function rules(): array
    {
        $section = (string) $this->input('section', 'settings');

        return match ($section) {
            'content' => $this->contentRules(),
            'design' => $this->designRules(),
            default => $this->operationalRules(),
        };
    }


    /**
     * @return array<string, mixed>
     */
    public function validatedSettings(): array
    {
        $section = (string) $this->input('section', 'settings');
        $validated = $this->validated();

        if ($section === 'content') {
            return [
                'translatable' => $validated['translatable'] ?? [],
            ];
        }

        unset($validated['section']);

        return $validated;
    }


    /**
     * @return array<string, string|array<int, string>>
     */
    private function contentRules(): array
    {
        $rules = [
            'section' => 'required|in:content',
            'translatable' => 'required|array',
        ];

        foreach (SpecialGiftPageSettings::contentKeys() as $key) {
            $rules["translatable.{$key}"] = 'nullable|string|max:500';
        }

        return $rules;
    }


    /**
     * @return array<string, string|array<int, string>>
     */
    private function designRules(): array
    {
        return [
            'section' => 'required|in:design',
            'specialgift_page_preset' => 'required|in:'.implode(',', [
                SpecialGiftPageSettings::PRESET_AESTHETIC,
                SpecialGiftPageSettings::PRESET_MINIMAL,
                SpecialGiftPageSettings::PRESET_CLASSIC,
                SpecialGiftPageSettings::PRESET_CUSTOM,
            ]),
            'specialgift_page_color_source' => 'required|in:'.implode(',', [
                SpecialGiftPageSettings::COLOR_STORE_THEME,
                SpecialGiftPageSettings::COLOR_CUSTOM,
            ]),
            'specialgift_page_accent_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'specialgift_page_gradient_enabled' => 'nullable|boolean',
            'specialgift_page_bokeh_enabled' => 'nullable|boolean',
            'specialgift_page_sparkles_enabled' => 'nullable|boolean',
        ];
    }


    /**
     * @return array<string, string|array<int, string>>
     */
    private function operationalRules(): array
    {
        return [
            'section' => 'required|in:settings',
            'specialgift_enabled' => 'nullable|boolean',
            'specialgift_voucher_background' => 'nullable|integer',
            'specialgift_message_template' => 'nullable|string|max:2000',
        ];
    }
}
