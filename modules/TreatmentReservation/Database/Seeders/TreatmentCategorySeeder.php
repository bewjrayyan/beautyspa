<?php

namespace Modules\TreatmentReservation\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\TreatmentReservation\Entities\TreatmentCategory;

class TreatmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Facial', 'slug' => 'facial', 'color' => '#38bdf8', 'position' => 1],
            ['name' => 'Hair', 'slug' => 'hair', 'color' => '#a78bfa', 'position' => 2],
            ['name' => 'Nails', 'slug' => 'nails', 'color' => '#f472b6', 'position' => 3],
            ['name' => 'Body', 'slug' => 'body', 'color' => '#34d399', 'position' => 4],
            ['name' => 'Package', 'slug' => 'package', 'color' => '#fb923c', 'position' => 5],
        ];

        foreach ($categories as $category) {
            TreatmentCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category + ['is_active' => true]
            );
        }
    }
}
