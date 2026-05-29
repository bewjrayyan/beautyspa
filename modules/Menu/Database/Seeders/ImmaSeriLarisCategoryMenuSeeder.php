<?php

namespace Modules\Menu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuItem;
use Modules\Category\Entities\Category;

class ImmaSeriLarisCategoryMenuSeeder extends Seeder
{
    private const CATEGORY_MENU_NAME = 'Category Menu';


    /**
     * Rebuild the storefront category mega menu with SPA, Aesthetic, and Cosmetik roots.
     *
     * @return void
     */
    public function run(): void
    {
        $menu = Menu::withoutGlobalScope('active')
            ->whereTranslation('name', self::CATEGORY_MENU_NAME)
            ->first();

        if (! $menu) {
            $this->command?->error('Category Menu not found.');

            return;
        }

        $spa = Category::withoutGlobalScope('active')->where('slug', 'spa')->first();
        $aesthetic = Category::withoutGlobalScope('active')->where('slug', 'aesthetic-estetik')->first();
        $cosmetik = Category::withoutGlobalScope('active')->where('slug', 'cosmetik')->first();

        if (! $spa || ! $aesthetic || ! $cosmetik) {
            $this->command?->error('SPA / Aesthetic / Cosmetik categories not found. Run category seeders first.');

            return;
        }

        $rootId = MenuItem::withoutGlobalScope('not_root')
            ->where('menu_id', $menu->id)
            ->where('is_root', true)
            ->value('id');

        if (! $rootId) {
            $this->command?->error('Category menu root item not found.');

            return;
        }

        MenuItem::withoutGlobalScopes()
            ->where('menu_id', $menu->id)
            ->where('is_root', false)
            ->delete();

        $this->createCategoryMenuItem($menu->id, $rootId, $aesthetic, 'las la-syringe', 3);
        $this->createCategoryMenuItem($menu->id, $rootId, $spa, 'las la-spa', 2);
        $this->createCategoryMenuItem($menu->id, $rootId, $cosmetik, 'las la-palette', 1);

        Cache::tags(['mega_menu', 'menu_items', 'categories', 'pages', 'settings'])->flush();

        $this->command?->info('Category menu updated: SPA + AESTHETIC / ESTETIK + Cosmetik.');
    }


    private function createCategoryMenuItem(
        int $menuId,
        int $rootId,
        Category $category,
        string $icon,
        int $position
    ): void {
        $data = [
            'menu_id' => $menuId,
            'parent_id' => $rootId,
            'category_id' => $category->id,
            'type' => 'category',
            'target' => '_self',
            'position' => $position,
            'icon' => $icon,
            'is_root' => false,
            'is_fluid' => false,
            'is_active' => true,
        ];

        foreach (supported_locales() as $locale => $language) {
            $data[$locale]['name'] = $category->name;
        }

        MenuItem::create($data);
    }
}
