<?php

namespace Modules\Translation\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class CopyEnglishLangFilesToMalaySeeder extends Seeder
{
    /**
     * Copy module English lang files to Malay as a translation baseline.
     *
     * @return void
     */
    public function run(): void
    {
        $copied = 0;
        $paths = array_merge(
            glob(base_path('modules/*/Resources/lang/en/*.php')) ?: [],
            glob(base_path('resources/lang/en/*.php')) ?: []
        );

        foreach ($paths as $enFile) {
            $msFile = str_replace('/en/', '/ms/', $enFile);
            $msDir = dirname($msFile);

            if (! is_dir($msDir)) {
                mkdir($msDir, 0755, true);
            }

            copy($enFile, $msFile);
            $copied++;
        }

        Cache::tags('translations')->flush();

        $this->command?->info("Copied {$copied} English lang files to ms/.");
    }
}
