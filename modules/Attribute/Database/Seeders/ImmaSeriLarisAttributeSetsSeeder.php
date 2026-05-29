<?php

namespace Modules\Attribute\Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Modules\Attribute\Entities\Attribute;
use Modules\Category\Entities\Category;
use Modules\Attribute\Entities\AttributeSet;
use Modules\Attribute\Entities\AttributeValue;

class ImmaSeriLarisAttributeSetsSeeder extends Seeder
{
    /**
     * Attribute sets with attributes and values for immaserilaris.com.
     *
     * @return array<int, array<string, mixed>>
     */
    private function sets(): array
    {
        return [
            [
                'name' => 'SPA Treatments',
                'category_slugs' => $this->spaCategorySlugs(),
                'attributes' => [
                    [
                        'name' => 'Duration',
                        'slug' => 'spa-duration',
                        'is_filterable' => true,
                        'values' => [
                            '30 Minutes',
                            '45 Minutes',
                            '60 Minutes',
                            '90 Minutes',
                            '120 Minutes',
                        ],
                    ],
                    [
                        'name' => 'Treatment Area',
                        'slug' => 'spa-treatment-area',
                        'is_filterable' => true,
                        'values' => [
                            'Full Face',
                            'Half Face',
                            'T-zone',
                            'Neck & Décolletage',
                            'Eye Area',
                            'Full Body',
                            'Back',
                        ],
                    ],
                    [
                        'name' => 'Skin Type',
                        'slug' => 'spa-skin-type',
                        'is_filterable' => true,
                        'values' => [
                            'Normal',
                            'Dry',
                            'Oily',
                            'Combination',
                            'Sensitive',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Aesthetic & Estetik',
                'category_slugs' => $this->aestheticCategorySlugs(),
                'attributes' => [
                    [
                        'name' => 'Treatment Zone',
                        'slug' => 'aesthetic-zone',
                        'is_filterable' => true,
                        'values' => [
                            'Forehead',
                            'Crow\'s Feet',
                            'Glabella',
                            'Lips',
                            'Cheeks',
                            'Jawline',
                            'Nose Tip',
                            'Chin',
                            'Full Face',
                            'Neck',
                        ],
                    ],
                    [
                        'name' => 'Treatment Type',
                        'slug' => 'aesthetic-treatment-type',
                        'is_filterable' => true,
                        'values' => [
                            'Botox',
                            'Filler',
                            'Thread Lift',
                            'Skin Booster',
                            'PRP',
                            'Rejuran',
                            'Juvelook',
                            'Profhilo',
                            'Whitening Booster',
                            'Lipo',
                            'Dimple',
                        ],
                    ],
                    [
                        'name' => 'Package Tier',
                        'slug' => 'aesthetic-package-tier',
                        'is_filterable' => true,
                        'values' => [
                            'Standard',
                            'Premium',
                            'VIP',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Nail Services',
                'category_slugs' => ['manicure-pedicure'],
                'attributes' => [
                    [
                        'name' => 'Service Type',
                        'slug' => 'nail-service-type',
                        'is_filterable' => true,
                        'values' => [
                            'Classic Manicure',
                            'Gel Manicure',
                            'Classic Pedicure',
                            'Gel Pedicure',
                            'Mani + Pedi Combo',
                        ],
                    ],
                    [
                        'name' => 'Nail Finish',
                        'slug' => 'nail-finish',
                        'is_filterable' => true,
                        'values' => [
                            'No Polish',
                            'Basic Colour',
                            'French Tip',
                            'Gel Colour',
                            'Custom Nail Art',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Waxing Services',
                'category_slugs' => ['waxing'],
                'attributes' => [
                    [
                        'name' => 'Waxing Area',
                        'slug' => 'waxing-area',
                        'is_filterable' => true,
                        'values' => [
                            'Upper Lip',
                            'Underarm',
                            'Half Arm',
                            'Full Arm',
                            'Bikini',
                            'Brazilian',
                            'Half Leg',
                            'Full Leg',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Laser Treatment',
                'category_slugs' => ['laser'],
                'attributes' => [
                    [
                        'name' => 'Laser Area',
                        'slug' => 'laser-area',
                        'is_filterable' => true,
                        'values' => [
                            'Face',
                            'Underarm',
                            'Bikini',
                            'Half Leg',
                            'Full Leg',
                            'Back',
                            'Chest',
                        ],
                    ],
                    [
                        'name' => 'Session Package',
                        'slug' => 'laser-session-package',
                        'is_filterable' => true,
                        'values' => [
                            'Single Session',
                            '3 Sessions',
                            '5 Sessions',
                            '10 Sessions',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'IV Drip & Booster',
                'category_slugs' => ['drip', 'skin-booster', 'whitening-booster', 'injection'],
                'attributes' => [
                    [
                        'name' => 'Drip Formula',
                        'slug' => 'drip-formula',
                        'is_filterable' => true,
                        'values' => [
                            'Immune Booster',
                            'Skin Glow',
                            'Energy Boost',
                            'Detox',
                            'Whitening',
                            'Combo Drip',
                        ],
                    ],
                    [
                        'name' => 'Booster Brand',
                        'slug' => 'booster-brand',
                        'is_filterable' => true,
                        'values' => [
                            'Rejuran',
                            'Juvelook',
                            'Profhilo',
                            'Baby Booster',
                            'Vitamin C',
                            'Glutathione',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Bridal & Package',
                'category_slugs' => ['bridal-package'],
                'attributes' => [
                    [
                        'name' => 'Package Type',
                        'slug' => 'bridal-package-type',
                        'is_filterable' => true,
                        'values' => [
                            'Bridal Trial',
                            'Bridal Day Package',
                            'Bridal Full Package',
                            'Pre-Wedding Package',
                            'Groom Package',
                        ],
                    ],
                    [
                        'name' => 'Package Tier',
                        'slug' => 'bridal-package-tier',
                        'is_filterable' => true,
                        'values' => [
                            'Silver',
                            'Gold',
                            'Platinum',
                        ],
                    ],
                ],
            ],
        ];
    }


    /**
     * @return array<int, string>
     */
    private function spaCategorySlugs(): array
    {
        return $this->categorySlugsForParent('spa');
    }


    /**
     * @return array<int, string>
     */
    private function aestheticCategorySlugs(): array
    {
        return $this->categorySlugsForParent('aesthetic-estetik');
    }


    /**
     * @param string $parentSlug
     *
     * @return array<int, string>
     */
    private function categorySlugsForParent(string $parentSlug): array
    {
        $parent = Category::withoutGlobalScope('active')
            ->where('slug', $parentSlug)
            ->first();

        if (! $parent) {
            return [$parentSlug];
        }

        $slugs = Category::withoutGlobalScope('active')
            ->where('parent_id', $parent->id)
            ->pluck('slug')
            ->all();

        return array_merge([$parentSlug], $slugs);
    }


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $setsCreated = 0;
        $attrsCreated = 0;
        $skipped = 0;

        foreach ($this->sets() as $definition) {
            if ($this->setExists($definition['name'])) {
                $skipped++;

                continue;
            }

            $set = AttributeSet::create([
                'name' => $definition['name'],
            ]);

            $categoryIds = $this->categoryIds($definition['category_slugs'] ?? []);

            foreach ($definition['attributes'] as $index => $attributeDefinition) {
                $attribute = Attribute::create([
                    'name' => $attributeDefinition['name'],
                    'attribute_set_id' => $set->id,
                    'slug' => $attributeDefinition['slug'] ?? Str::slug($attributeDefinition['name']),
                    'is_filterable' => $attributeDefinition['is_filterable'] ?? true,
                ]);

                if (! empty($categoryIds)) {
                    $attribute->categories()->sync($categoryIds);
                }

                foreach ($attributeDefinition['values'] ?? [] as $position => $value) {
                    AttributeValue::create([
                        'value' => $value,
                        'attribute_id' => $attribute->id,
                        'position' => $position,
                    ]);
                }

                $attrsCreated++;
            }

            $setsCreated++;
        }

        $this->command?->info("Attribute sets: {$setsCreated} created, {$skipped} skipped.");
        $this->command?->info("Attributes created: {$attrsCreated}");
        $this->command?->info('Total sets: ' . AttributeSet::count());
    }


    /**
     * @param string $name
     *
     * @return bool
     */
    private function setExists(string $name): bool
    {
        return AttributeSet::whereHas('translations', function ($query) use ($name) {
            $query->where('name', $name);
        })->exists();
    }


    /**
     * @param array<int, string> $slugs
     *
     * @return array<int, int>
     */
    private function categoryIds(array $slugs): array
    {
        return Category::withoutGlobalScope('active')
            ->whereIn('slug', $slugs)
            ->pluck('id')
            ->all();
    }
}
