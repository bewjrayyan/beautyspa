<?php

namespace Modules\Storefront\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Setting;

/**
 * Re-enable the homepage blog carousel after settings were wiped or reset.
 *
 * Idempotent: safe to run multiple times.
 */
class ImmaSeriLarisBlogSectionSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('storefront_blogs_section_enabled', true);
        Setting::set('storefront_recent_blogs', 5);

        $this->setTranslatableSetting('storefront_blogs_section_title', [
            'en' => 'From Our Blog',
            'ms' => 'Artikel Blog Kami',
        ]);

        Cache::forget('settings');

        $this->command?->info('Homepage blog section restored (enabled, title, recent posts limit).');
    }

    /**
     * @param array<string, string> $values
     */
    private function setTranslatableSetting(string $key, array $values): void
    {
        $setting = Setting::firstOrCreate(
            ['key' => $key],
            ['is_translatable' => true]
        );

        $setting->update(['is_translatable' => true]);

        foreach ($values as $locale => $value) {
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
