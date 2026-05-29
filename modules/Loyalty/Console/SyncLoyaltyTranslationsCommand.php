<?php

namespace Modules\Loyalty\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Modules\Translation\Entities\Translation;

class SyncLoyaltyTranslationsCommand extends Command
{
    protected $signature = 'loyalty:sync-translations {--locale=* : Locales to sync (default: all supported)}';

    protected $description = 'Sync Loyalty module lang files to the translations table and refresh translation cache';

    public function handle(): int
    {
        $locales = $this->option('locale');

        if ($locales === [] || $locales === null) {
            $locales = array_keys(supported_locales());
        }

        $groups = $this->discoverGroups();
        $synced = 0;

        foreach ($locales as $locale) {
            foreach ($groups as $group) {
                $synced += $this->syncGroup($locale, $group);
            }

            $this->forgetLoaderCache($locale, $groups);
        }

        try {
            Cache::tags('translations')->forget(md5('translations.all'));
            Cache::tags('translations')->flush();
        } catch (\Throwable $e) {
            $this->warn('Could not flush translation cache tag: ' . $e->getMessage());
        }

        $this->info("Synced {$synced} loyalty translation values and refreshed cache.");

        return self::SUCCESS;
    }


    /**
     * @return array<int, string>
     */
    private function discoverGroups(): array
    {
        $groups = [];

        foreach (glob($this->langPath('en', '*.php')) ?: [] as $file) {
            $groups[] = basename($file, '.php');
        }

        return $groups;
    }


    private function syncGroup(string $locale, string $group): int
    {
        $file = $this->langPath($locale, "{$group}.php");

        if (! is_file($file)) {
            return 0;
        }

        $lines = Arr::dot(require $file);
        $count = 0;

        foreach ($lines as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            $fullKey = "loyalty::{$group}.{$key}";

            $translation = Translation::firstOrCreate(['key' => $fullKey]);
            $translation->translations()->updateOrCreate(
                ['locale' => $locale],
                ['value' => $value]
            );

            $count++;
        }

        return $count;
    }


    /**
     * @param  array<int, string>  $groups
     */
    private function forgetLoaderCache(string $locale, array $groups): void
    {
        foreach ($groups as $group) {
            Cache::tags('translations')->forget(
                md5("translation_loader.{$locale}.{$group}.loyalty")
            );
        }
    }


    private function langPath(string $locale, string $file): string
    {
        return base_path("modules/Loyalty/Resources/lang/{$locale}/{$file}");
    }
}
