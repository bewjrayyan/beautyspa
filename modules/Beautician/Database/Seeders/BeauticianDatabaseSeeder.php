<?php

namespace Modules\Beautician\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Beautician\Entities\Beautician;

class BeauticianDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $beauticians = [
            [
                'first_name' => 'JIEHA',
                'last_name' => '',
                'phone' => '60100000001',
                'profile_color' => '#e91e63',
                'job_title' => 'Senior Beautician',
                'position' => 1,
            ],
            [
                'first_name' => 'SITI',
                'last_name' => '',
                'phone' => '60100000002',
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
