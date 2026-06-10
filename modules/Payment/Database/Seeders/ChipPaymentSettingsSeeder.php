<?php

namespace Modules\Payment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Payment\Support\ChipPaymentSettingsDefaults;

class ChipPaymentSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $applied = ChipPaymentSettingsDefaults::applyMissingOnly();

        $this->command?->info(
            $applied === []
                ? 'CHIP payment settings already present — nothing to restore.'
                : 'CHIP payment settings restored ('.count($applied).' keys).'
        );
    }
}
