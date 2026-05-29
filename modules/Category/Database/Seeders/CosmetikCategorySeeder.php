<?php

namespace Modules\Category\Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Modules\Category\Entities\Category;

class CosmetikCategorySeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private array $children = [
        'Skincare',
        'Makeup',
        'Body Care',
        'Hair Care',
        'Sun Protection',
        'Fragrance',
        'Tools & Accessories',
    ];


    /**
     * @return void
     */
    public function run(): void
    {
        if (Category::withoutGlobalScope('active')->where('slug', 'cosmetik')->exists()) {
            $this->command?->info('Cosmetik category already exists. Skipping.');

            return;
        }

        $position = (int) Category::withoutGlobalScope('active')->max('position') + 1;

        $root = Category::withoutGlobalScope('active')->create([
            'name' => 'Cosmetik',
            'parent_id' => null,
            'slug' => 'cosmetik',
            'position' => $position,
            'is_searchable' => true,
            'is_active' => true,
        ]);

        foreach ($this->children as $index => $childName) {
            Category::withoutGlobalScope('active')->create([
                'name' => $childName,
                'parent_id' => $root->id,
                'slug' => Str::slug($childName),
                'position' => $index,
                'is_searchable' => true,
                'is_active' => true,
            ]);
        }

        Cache::tags('categories')->flush();

        $this->command?->info('Cosmetik category created with ' . count($this->children) . ' subcategories.');
    }
}
