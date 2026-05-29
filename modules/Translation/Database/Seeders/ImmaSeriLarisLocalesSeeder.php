<?php

namespace Modules\Translation\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Setting;

class ImmaSeriLarisLocalesSeeder extends Seeder
{
    /**
     * Enable English and Bahasa Malaysia only.
     *
     * @return void
     */
    public function run(): void
    {
        Setting::setMany([
            'supported_locales' => ['en', 'ms'],
            'default_locale' => 'en',
        ]);

        $listingTitle = Setting::firstOrCreate(
            ['key' => 'storefront_products_listing_title'],
            ['is_translatable' => true]
        );

        $listingTitle->update(['is_translatable' => true]);

        foreach (['en' => 'Treatment', 'ms' => 'Rawatan'] as $locale => $value) {
            DB::table('setting_translations')->updateOrInsert(
                [
                    'setting_id' => $listingTitle->id,
                    'locale' => $locale,
                ],
                ['value' => serialize($value)]
            );
        }

        Setting::setMany([
            'storefront_product_consultation_enabled' => true,
            'storefront_product_consultation_url' => 'https://immaserilaris.com/chat/treatment/?dynamic_product={product_name}&dynamic_url={product_url}',
        ]);

        $consultationLabel = Setting::firstOrCreate(
            ['key' => 'storefront_product_consultation_label'],
            ['is_translatable' => true]
        );

        $consultationLabel->update(['is_translatable' => true]);

        foreach (['en' => 'Get Free Consultations', 'ms' => 'Dapatkan Konsultasi Percuma'] as $locale => $value) {
            DB::table('setting_translations')->updateOrInsert(
                [
                    'setting_id' => $consultationLabel->id,
                    'locale' => $locale,
                ],
                ['value' => serialize($value)]
            );
        }

        foreach (supported_locale_keys() as $locale) {
            Cache::forget(md5('settings.all:' . $locale));
        }

        Cache::tags('translations')->flush();

        $this->command?->info('Locales set to English + Bahasa Malaysia (ar removed).');
    }
}
