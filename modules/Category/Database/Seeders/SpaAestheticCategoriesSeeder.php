<?php

namespace Modules\Category\Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Modules\Category\Entities\Category;

class SpaAestheticCategoriesSeeder extends Seeder
{
    /**
     * SPA and Aesthetic / Estetik category tree.
     *
     * @var array<string, array<int, string>>
     */
    private array $tree = [
        'SPA' => [
            'Facial',
            'Laser',
            'Massage',
            'Bath & Body',
            'Waxing',
            'Eyelash & Brow',
            'Hair Treatment',
            'Manicure & Pedicure',
            'Bridal Package',
        ],
        'AESTHETIC / ESTETIK' => [
            'Botox',
            'Filler',
            'Benang',
            'Drip',
            'Skin Booster',
            'Injection',
            'Lipo',
            'Dimple',
            'Whitening Booster',
            'Surgery',
        ],
        'Cosmetik' => [
            'Skincare',
            'Makeup',
            'Body Care',
            'Hair Care',
            'Sun Protection',
            'Fragrance',
            'Tools & Accessories',
        ],
    ];


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (Category::withoutGlobalScope('active')->where('slug', 'spa')->exists()) {
            $this->command?->info('SPA / Aesthetic categories already exist. Skipping.');

            return;
        }

        $rootPosition = (int) Category::withoutGlobalScope('active')->max('position') + 1;

        foreach ($this->tree as $rootName => $children) {
            $root = $this->createCategory($rootName, null, $rootPosition++);

            foreach ($children as $index => $childName) {
                $this->createCategory($childName, $root->id, $index);
            }
        }

        Cache::tags('categories')->flush();

        $this->command?->info('SPA and AESTHETIC / ESTETIK categories created successfully.');
    }


    /**
     * Create a category with translation.
     *
     * @param string $name
     * @param int|null $parentId
     * @param int $position
     *
     * @return Category
     */
    private function createCategory(string $name, ?int $parentId, int $position): Category
    {
        return Category::withoutGlobalScope('active')->create([
            'name' => $name,
            'parent_id' => $parentId,
            'slug' => Str::slug($name),
            'position' => $position,
            'is_searchable' => true,
            'is_active' => true,
        ]);
    }
}
