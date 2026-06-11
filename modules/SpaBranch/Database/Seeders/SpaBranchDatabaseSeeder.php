<?php

namespace Modules\SpaBranch\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Beautician\Entities\Beautician;
use Modules\SpaBranch\Entities\SpaBranch;

class SpaBranchDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'IMMA Seri Laris Beauty & Wellness',
                'code' => 'KJG01',
                'phone' => '601133411016',
                'email' => 'booking@immaserilaris.com',
                'address' => "IMMA Seri Laris Beauty & Wellness\nKajang, Selangor 43000",
                'position' => 1,
            ],
        ];

        foreach ($branches as $data) {
            $branch = SpaBranch::query()->updateOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['is_active' => true])
            );

            if (class_exists(Beautician::class)) {
                $beauticianIds = Beautician::query()
                    ->where('is_active', true)
                    ->orderBy('position')
                    ->pluck('id')
                    ->all();

                $branch->beauticians()->sync($beauticianIds);
            }
        }
    }
}
