<?php

namespace Modules\Order\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Modules\Translation\Entities\Translation;
use Nwidart\Modules\Facades\Module;

class SyncOrderTranslationsCommand extends Command
{
    protected $signature = 'order:sync-translations';

    protected $description = 'Sync Order module lang files to the database, prune stale keys, and refresh translation cache';

    public function handle(): int
    {
        $module = Module::find('Order');

        if (! $module) {
            $this->error('Order module is not installed.');

            return self::FAILURE;
        }

        $validKeys = $this->collectValidKeys($module->getPath() . '/Resources/lang');
        $removed = $this->pruneStaleKeys($validKeys);

        if ($removed > 0) {
            $this->info("Removed {$removed} stale order translation keys from the database.");
        }

        $this->call('translation:refresh-cache', [
            '--sync' => true,
            '--module' => 'order',
        ]);

        $this->call('view:clear');

        $this->info('Order translations synced. Hard-refresh the admin page if labels still show keys.');

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function collectValidKeys(string $langRoot): array
    {
        $keys = [];

        foreach (glob("{$langRoot}/*/*.php") ?: [] as $file) {
            $group = basename($file, '.php');
            $lines = Arr::dot(require $file);

            foreach ($lines as $key => $value) {
                if (is_string($value)) {
                    $keys[] = "order::{$group}.{$key}";
                }
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @param list<string> $validKeys
     */
    private function pruneStaleKeys(array $validKeys): int
    {
        $stale = Translation::query()
            ->where('key', 'like', 'order::%')
            ->whereNotIn('key', $validKeys)
            ->get();

        foreach ($stale as $translation) {
            $translation->delete();
        }

        return $stale->count();
    }
}
