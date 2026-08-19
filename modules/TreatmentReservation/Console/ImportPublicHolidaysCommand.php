<?php

namespace Modules\TreatmentReservation\Console;

use Illuminate\Console\Command;
use Modules\TreatmentReservation\Services\MalaysiaHolidayImportService;

class ImportPublicHolidaysCommand extends Command
{
    protected $signature = 'treatment-reservations:import-public-holidays
                            {year : Year to import (e.g. 2026)}
                            {--state= : Optional state code (e.g. SGR, KDH, ...)}';

    protected $description = 'Import Malaysian public holidays master data from malaysia-holiday API.';

    public function handle(): int
    {
        $year = (int) $this->argument('year');
        $state = $this->option('state');

        $imported = app(MalaysiaHolidayImportService::class)->importYear(
            $year,
            is_string($state) && $state !== '' ? $state : null
        );

        $this->info(sprintf('Imported %d holiday rows for year %d.', $imported, $year));

        return self::SUCCESS;
    }
}

