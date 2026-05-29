<?php

namespace Modules\Option\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Option\Entities\Option;

class ImmaSeriLarisOptionsSeeder extends Seeder
{
    /**
     * Global product options for immaserilaris.com (spa & aesthetic).
     *
     * @return array<int, array<string, mixed>>
     */
    private function options(): array
    {
        return [
            [
                'name' => 'Duration',
                'type' => 'radio',
                'is_required' => true,
                'position' => 1,
                'values' => [
                    ['label' => '30 Minutes', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => '45 Minutes', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => '60 Minutes', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => '90 Minutes', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => '120 Minutes', 'price' => 0, 'price_type' => 'fixed'],
                ],
            ],
            [
                'name' => 'Session Package',
                'type' => 'dropdown',
                'is_required' => false,
                'position' => 2,
                'values' => [
                    ['label' => 'Single Session', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => '3 Sessions', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => '5 Sessions', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => '10 Sessions', 'price' => 0, 'price_type' => 'fixed'],
                ],
            ],
            [
                'name' => 'Treatment Area',
                'type' => 'dropdown',
                'is_required' => true,
                'position' => 3,
                'values' => [
                    ['label' => 'Full Face', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Half Face', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'T-zone', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Neck & Décolletage', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Eye Area', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Full Body', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Underarm', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Bikini Line', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Half Legs', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Full Legs', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Back', 'price' => 0, 'price_type' => 'fixed'],
                ],
            ],
            [
                'name' => 'Package Tier',
                'type' => 'radio',
                'is_required' => false,
                'position' => 4,
                'values' => [
                    ['label' => 'Standard', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Premium', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'VIP', 'price' => 0, 'price_type' => 'fixed'],
                ],
            ],
            [
                'name' => 'Skin Type',
                'type' => 'radio',
                'is_required' => false,
                'position' => 5,
                'values' => [
                    ['label' => 'Normal', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Dry', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Oily', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Combination', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Sensitive', 'price' => 0, 'price_type' => 'fixed'],
                ],
            ],
            [
                'name' => 'Gender',
                'type' => 'radio',
                'is_required' => false,
                'position' => 6,
                'values' => [
                    ['label' => 'Female', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Male', 'price' => 0, 'price_type' => 'fixed'],
                ],
            ],
            [
                'name' => 'Add-on Treatment',
                'type' => 'checkbox_custom',
                'is_required' => false,
                'position' => 7,
                'values' => [
                    ['label' => 'Deep Cleansing', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'LED Light Therapy', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Ampoule Booster', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Eye Treatment Mask', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Neck Treatment', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Hand Massage', 'price' => 0, 'price_type' => 'fixed'],
                ],
            ],
            [
                'name' => 'IV Drip Formula',
                'type' => 'dropdown',
                'is_required' => false,
                'position' => 8,
                'values' => [
                    ['label' => 'Immune Booster', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Skin Glow', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Energy Boost', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Detox', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Whitening', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Combo Drip', 'price' => 0, 'price_type' => 'fixed'],
                ],
            ],
            [
                'name' => 'Waxing Area',
                'type' => 'checkbox_custom',
                'is_required' => false,
                'position' => 9,
                'values' => [
                    ['label' => 'Upper Lip', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Underarm', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Half Arm', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Full Arm', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Bikini', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Brazilian', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Half Leg', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Full Leg', 'price' => 0, 'price_type' => 'fixed'],
                ],
            ],
            [
                'name' => 'Laser Area',
                'type' => 'checkbox_custom',
                'is_required' => false,
                'position' => 10,
                'values' => [
                    ['label' => 'Face', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Underarm', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Bikini', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Half Leg', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Full Leg', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Back', 'price' => 0, 'price_type' => 'fixed'],
                ],
            ],
            [
                'name' => 'Nail Service',
                'type' => 'radio',
                'is_required' => false,
                'position' => 11,
                'values' => [
                    ['label' => 'Classic Manicure', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Gel Manicure', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Classic Pedicure', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Gel Pedicure', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Mani + Pedi Combo', 'price' => 0, 'price_type' => 'fixed'],
                ],
            ],
            [
                'name' => 'Nail Finish',
                'type' => 'dropdown',
                'is_required' => false,
                'position' => 12,
                'values' => [
                    ['label' => 'No Polish', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Basic Colour', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'French Tip', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Gel Colour', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Remove Nail Polish', 'price' => 0, 'price_type' => 'fixed'],
                ],
            ],
            [
                'name' => 'Aesthetic Zone',
                'type' => 'dropdown',
                'is_required' => false,
                'position' => 13,
                'values' => [
                    ['label' => 'Forehead', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Crow\'s Feet', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Glabella', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Lips', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Cheeks', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Jawline', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Nose Tip', 'price' => 0, 'price_type' => 'fixed'],
                    ['label' => 'Chin', 'price' => 0, 'price_type' => 'fixed'],
                ],
            ],
            [
                'name' => 'Special Instructions',
                'type' => 'textarea',
                'is_required' => false,
                'position' => 14,
                'values' => [],
            ],
            [
                'name' => 'Preferred Date',
                'type' => 'date',
                'is_required' => false,
                'position' => 15,
                'values' => [],
            ],
            [
                'name' => 'Preferred Time',
                'type' => 'time',
                'is_required' => false,
                'position' => 16,
                'values' => [],
            ],
        ];
    }


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $created = 0;
        $skipped = 0;

        foreach ($this->options() as $definition) {
            if ($this->optionExists($definition['name'])) {
                $skipped++;

                continue;
            }

            $option = Option::create([
                'name' => $definition['name'],
                'type' => $definition['type'],
                'is_required' => $definition['is_required'],
                'is_global' => true,
                'position' => $definition['position'],
            ]);

            if (! empty($definition['values'])) {
                $option->saveValues($definition['values']);
            }

            $created++;
        }

        $this->command?->info("Global options: {$created} created, {$skipped} already existed.");
        $this->command?->info('Total global options: ' . Option::where('is_global', true)->count());
    }


    /**
     * @param string $name
     *
     * @return bool
     */
    private function optionExists(string $name): bool
    {
        return Option::where('is_global', true)
            ->whereHas('translations', function ($query) use ($name) {
                $query->where('name', $name);
            })
            ->exists();
    }
}
