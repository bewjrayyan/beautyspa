<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Support\SensitiveSetting;

return new class extends Migration
{
    public function up(): void
    {
        Setting::query()
            ->whereIn('key', SensitiveSetting::keys())
            ->get()
            ->each(function (Setting $setting): void {
                $stored = $setting->getRawOriginal('plain_value');

                if (SensitiveSetting::isEncrypted($stored)) {
                    return;
                }

                $setting->plain_value = $setting->value;
                $setting->saveQuietly();
            });

        $this->clearSettingCaches();
    }

    public function down(): void
    {
        Setting::query()
            ->whereIn('key', SensitiveSetting::keys())
            ->get()
            ->each(function (Setting $setting): void {
                $stored = $setting->getRawOriginal('plain_value');

                if (! SensitiveSetting::isEncrypted($stored)) {
                    return;
                }

                $setting->setRawAttributes(array_merge($setting->getAttributes(), [
                    'plain_value' => SensitiveSetting::decryptSerialized($stored),
                ]));
                $setting->saveQuietly();
            });

        $this->clearSettingCaches();
    }

    private function clearSettingCaches(): void
    {
        foreach (supported_locale_keys() as $locale) {
            Cache::forget(md5('settings.all:'.$locale));
            Cache::forget(md5('settings.public.v2:'.$locale));
        }
    }
};
