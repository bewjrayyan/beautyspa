<?php

namespace Modules\Payment\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Setting;

class ChipPaymentSettingsDefaults
{
    /**
     * Restore Admin → Settings → CHIP tab fields when missing or empty.
     *
     * @return array<int, string>
     */
    public static function applyMissingOnly(): array
    {
        $applied = [];

        foreach (self::plain() as $key => $value) {
            if (! self::isMissing($key)) {
                continue;
            }

            Setting::set($key, $value);
            $applied[] = $key;
        }

        foreach (self::translatable() as $key => $locales) {
            if (! self::isTranslatableMissing($key)) {
                continue;
            }

            self::setTranslatable($key, $locales);
            $applied[] = "translatable.{$key}";
        }

        if ($applied !== []) {
            Cache::forget('settings');
        }

        return $applied;
    }

    /**
     * @return array<string, mixed>
     */
    public static function plain(): array
    {
        return [
            'chip_enabled' => true,
            'chip_test_mode' => true,
            'chip_all_methods_enabled' => false,
            'chip_brand_id' => '',
            'chip_api_key' => '',
            'chip_webhook_url' => '',
            'chip_public_key' => '',
            'chip_webhook_secret' => '',
            'chip_fpx_enabled' => true,
            'chip_fpx_surcharge' => 100,
            'chip_fpx_whitelist' => '',
            'chip_card_enabled' => true,
            'chip_card_surcharge_percent' => 2.0,
            'chip_card_whitelist' => '',
            'chip_atome_enabled' => true,
            'chip_atome_surcharge_percent' => 5.3,
            'chip_atome_whitelist' => '',
        ];
    }

    /**
     * @return array<string, array{en: string, ms: string}>
     */
    public static function translatable(): array
    {
        return [
            'chip_label' => [
                'en' => 'CHIP',
                'ms' => 'CHIP',
            ],
            'chip_description' => [
                'en' => 'Pay with FPX online banking, credit or debit cards, or Atome via CHIP Collect (MYR).',
                'ms' => 'Bayar melalui FPX, kad kredit/debit, atau Atome dengan CHIP Collect (MYR).',
            ],
            'chip_fpx_label' => [
                'en' => 'FPX',
                'ms' => 'FPX',
            ],
            'chip_fpx_description' => [
                'en' => 'Online banking (Malaysia)',
                'ms' => 'Perbankan dalam talian (Malaysia)',
            ],
            'chip_card_label' => [
                'en' => 'Credit & debit cards',
                'ms' => 'Kad kredit & debit',
            ],
            'chip_card_description' => [
                'en' => 'Visa, Mastercard, and other card networks',
                'ms' => 'Visa, Mastercard, dan rangkaian kad lain',
            ],
            'chip_atome_label' => [
                'en' => 'Atome',
                'ms' => 'Atome',
            ],
            'chip_atome_description' => [
                'en' => 'Buy now, pay later',
                'ms' => 'Beli sekarang, bayar kemudian',
            ],
        ];
    }

    private static function isMissing(string $key): bool
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

        return is_array($current) && $current === [];
    }

    private static function isTranslatableMissing(string $key): bool
    {
        $setting = Setting::where('key', $key)->first();

        if ($setting === null) {
            return true;
        }

        foreach (['en', 'ms'] as $locale) {
            $row = DB::table('setting_translations')
                ->where('setting_id', $setting->id)
                ->where('locale', $locale)
                ->value('value');

            if ($row === null) {
                return true;
            }

            $value = @unserialize($row);

            if (! is_string($value) || trim($value) === '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, string> $locales
     */
    private static function setTranslatable(string $key, array $locales): void
    {
        $setting = Setting::firstOrCreate(
            ['key' => $key],
            ['is_translatable' => true]
        );

        $setting->update(['is_translatable' => true]);

        foreach ($locales as $locale => $value) {
            DB::table('setting_translations')->updateOrInsert(
                [
                    'setting_id' => $setting->id,
                    'locale' => $locale,
                ],
                ['value' => serialize($value)]
            );
        }
    }
}
