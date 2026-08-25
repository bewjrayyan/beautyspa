<?php

namespace Modules\Beautician\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Beautician\Entities\Beautician;

class BeauticianDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Profile stubs only — WhatsApp recipients must be set from Admin
        // (Beauticians → phone). Never hardcode test/sender numbers here.
        $beauticians = [
            [
                'first_name' => 'JIEHA',
                'last_name' => '',
                'phone' => null,
                'profile_color' => '#e91e63',
                'job_title' => 'Senior Beautician',
                'position' => 1,
            ],
            [
                'first_name' => 'SITI',
                'last_name' => '',
                'phone' => null,
                'profile_color' => '#9c27b0',
                'job_title' => 'Facial Specialist',
                'position' => 2,
            ],
        ];

        foreach ($beauticians as $data) {
            Beautician::query()->updateOrCreate(
                ['first_name' => $data['first_name'], 'last_name' => $data['last_name']],
                array_merge($data, ['is_active' => true])
            );
        }
    }
}
