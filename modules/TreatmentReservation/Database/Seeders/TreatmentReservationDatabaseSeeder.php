<?php

namespace Modules\TreatmentReservation\Database\Seeders;

use Illuminate\Database\Seeder;

class TreatmentReservationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TreatmentCategorySeeder::class);
    }
}
