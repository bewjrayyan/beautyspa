<?php

namespace Modules\Translation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Modules\Translation\Entities\Translation;
use Nwidart\Modules\Facades\Module;

class RefreshTranslationCacheCommand extends Command
{
    protected $signature = 'translation:refresh-cache
                            {--sync : Also sync lang files into the translations database table}
                            {--module= : Limit to one module namespace (e.g. admin, loyalty)}';

    protected $description = 'Clear stale translation loader cache and optionally sync PHP lang files to the database';

    public function handle(): int
    {
        $locales = array_keys(supported_locales());
        $onlyModule = $this->option('module');
        $forgotten = 0;
        $synced = 0;

        foreach (Module::allEnabled() as $module) {
            $namespace = $module->getLowerName();

            if ($onlyModule && $namespace !== strtolower((string) $onlyModule)) {
                continue;
            }

            $langRoot = $module->getPath() . '/Resources/lang';

            if (! is_dir($langRoot)) {
                continue;
            }

            foreach ($locales as $locale) {
                $localeDir = "{$langRoot}/{$locale}";

                if (! is_dir($localeDir)) {
                    continue;
                }

                foreach (glob("{$localeDir}/*.php") ?: [] as $file) {
                    $group = basename($file, '.php');
                    Cache::tags('translations')->forget(
                        md5("translation_loader.{$locale}.{$group}.{$namespace}")
                    );
                    $forgotten++;

                    if ($this->option('sync')) {
                        $synced += $this->syncFile($namespace, $group, $locale, $file);
                    }
                }
            }
        }

        try {
            Cache::tags('translations')->forget(md5('translations.all'));
        } catch (\Throwable $e) {
            $this->warn($e->getMessage());
        }

        $this->info("Refreshed {$forgotten} translation loader cache entries.");

        if ($this->option('sync')) {
            $this->info("Synced {$synced} translation values to the database.");
        }

        return self::SUCCESS;
    }


    private function syncFile(string $namespace, string $group, string $locale, string $file): int
    {
        $lines = Arr::dot(require $file);
        $count = 0;

        foreach ($lines as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            $fullKey = "{$namespace}::{$group}.{$key}";

            $translation = Translation::firstOrCreate(['key' => $fullKey]);
            $translation->translations()->updateOrCreate(
                ['locale' => $locale],
                ['value' => $value]
            );

            $count++;
        }

        return $count;
    }
}
