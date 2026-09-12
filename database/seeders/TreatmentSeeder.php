<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TreatmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $treatments = [
            [
                'name' => 'GLOW RESTORE',
                'description' => 'Aesthetic treatment for skin glow restoration',
                'category' => 'Skin Booster',
                'price' => 610,
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CRYSTAL GLOW',
                'description' => 'Aesthetic treatment with crystal formulation',
                'category' => 'Skin Booster',
                'price' => 610,
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'EYE LUXE',
                'description' => 'Specialized eye area treatment',
                'category' => 'Skin Booster',
                'price' => 2670,
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'HYDRA LUXE',
                'description' => 'Hydrating luxury treatment',
                'category' => 'Skin Booster',
                'price' => 3630,
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SKIN REVIVAL',
                'description' => 'Intensive skin revival treatment',
                'category' => 'Skin Booster',
                'price' => 5400,
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SMOOTH GLOW',
                'description' => 'Smooth and glowing skin treatment',
                'category' => 'Skin Booster',
                'price' => 1699,
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'K-GLOW',
                'description' => 'Korean-inspired glow treatment',
                'category' => 'Skin Booster',
                'price' => 2670,
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BABY SKIN',
                'description' => 'Treatment for soft, baby-like skin',
                'category' => 'Skin Booster',
                'price' => 4599,
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'AURA FACE SCULPT',
                'description' => 'Face sculpting treatment for enhanced facial features',
                'category' => 'Face Sculpting',
                'price' => 4399,
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'LUMIERE LIPS SCULPT',
                'description' => 'Lip sculpting treatment with luminous effect',
                'category' => 'Lip Enhancement',
                'price' => 5499,
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'RINASCITA THERAPY',
                'description' => 'Rejuvenation therapy for overall skin renewal',
                'category' => 'Rejuvenation',
                'price' => 5499,
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('treatments')->insert($treatments);
    }
}
