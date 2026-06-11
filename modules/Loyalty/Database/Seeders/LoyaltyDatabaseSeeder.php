<?php

namespace Modules\Loyalty\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Loyalty\Entities\LoyaltyStampProgram;
use Modules\Loyalty\Entities\LoyaltyTier;

class LoyaltyDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'slug' => 'silver',
                'name' => 'Silver',
                'min_lifetime_spend' => 0,
                'earn_multiplier' => 1.0,
                'benefits' => [
                    '1 mata setiap RM1 belanja',
                    'Akses promosi ahli',
                ],
                'sort_order' => 1,
            ],
            [
                'slug' => 'gold',
                'name' => 'Gold',
                'min_lifetime_spend' => 2000,
                'earn_multiplier' => 1.25,
                'benefits' => [
                    '1.25× mata ganjaran',
                    'Keutamaan slot rawatan',
                ],
                'sort_order' => 2,
            ],
            [
                'slug' => 'platinum',
                'name' => 'Platinum',
                'min_lifetime_spend' => 5000,
                'earn_multiplier' => 1.5,
                'benefits' => [
                    '1.5× mata ganjaran',
                    'Hadiah hari lahir',
                    'Akses promosi eksklusif',
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($tiers as $tier) {
            LoyaltyTier::updateOrCreate(
                ['slug' => $tier['slug']],
                array_merge($tier, ['is_active' => true])
            );
        }

        LoyaltyStampProgram::updateOrCreate(
            ['name' => '7 Times Free 1 Basic Wash'],
            [
                'reward_description' => '7 lawatan rawatan — 1 cucian asas percuma',
                'stamps_required' => 7,
                'validity_days' => 30,
                'virtual_treatments_only' => true,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );
    }
}
