<?php

namespace AestheticCart\Scripts;

use AestheticCart\Support\ReleaseFilePruner;
use Illuminate\Support\Facades\Log;

class V4_9_82
{
    public function run(): void
    {
        $result = app(ReleaseFilePruner::class)->apply(
            base_path(),
            storage_path('app/private/release-quarantine'),
        );

        if ($result['paths'] !== []) {
            Log::notice('Retired release files quarantined by updater script.', $result);
        }
    }
}
