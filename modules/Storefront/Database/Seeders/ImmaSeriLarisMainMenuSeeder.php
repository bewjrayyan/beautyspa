<?php

namespace Modules\Storefront\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuItem;
use Modules\Page\Entities\Page;
use Modules\Setting\Entities\Setting;
use Modules\Category\Entities\Category;

/**
 * Rebuild the storefront main navigation after the menus table was wiped:
 *
 *  - Primary Menu  -> horizontal top navigation (storefront_primary_menu)
 *  - Category Menu -> "All Categories" mega dropdown (storefront_category_menu)
 *
 * Idempotent: re-running rebuilds the same two menus and re-wires the settings.
 */
class ImmaSeriLarisMainMenuSeeder extends Seeder
{
    private const PRIMARY_MENU_NAME = 'Primary Menu';
    private const CATEGORY_MENU_NAME = 'Category Menu';

    private const ROOT_CATEGORY_SLUGS = ['spa', 'aesthetic-estetik', 'cosmetik'];

    public function run(): void
    {
        $primaryId = $this->buildPrimaryMenu();
        $categoryId = $this->buildCategoryMenu();

        Setting::set('storefront_primary_menu', $primaryId);
        Setting::set('storefront_category_menu', $categoryId);

        $this->flushCaches();

        $this->command?->info('Main navigation restored: Primary Menu + Category Menu.');
    }

    private function buildPrimaryMenu(): int
    {
        $menu = $this->ensureMenu(self::PRIMARY_MENU_NAME);
        $rootId = $this->rootItemId($menu->id);
        $this->clearItems($menu->id);

        $position = 1;

        $this->createUrlItem($menu->id, $rootId, '/', [
            'en' => 'Home',
            'ms' => 'Laman Utama',
        ], $position++);

        foreach (self::ROOT_CATEGORY_SLUGS as $slug) {
            $category = Category::withoutGlobalScope('active')->where('slug', $slug)->first();

            if ($category) {
                $this->createCategoryItem($menu->id, $rootId, $category, $position++);
            }
        }

        $about = Page::withoutGlobalScope('active')->where('slug', 'about-us')->first();

        if ($about) {
            $this->createPageItem($menu->id, $rootId, $about->id, [
                'en' => 'About Us',
                'ms' => 'Tentang Kami',
            ], $position++);
        }

        $this->createUrlItem($menu->id, $rootId, 'contact', [
            'en' => 'Contact Us',
            'ms' => 'Hubungi Kami',
        ], $position++);

        return $menu->id;
    }

    private function buildCategoryMenu(): int
    {
        $menu = $this->ensureMenu(self::CATEGORY_MENU_NAME);
        $rootId = $this->rootItemId($menu->id);
        $this->clearItems($menu->id);

        $position = count(self::ROOT_CATEGORY_SLUGS);

        foreach (self::ROOT_CATEGORY_SLUGS as $slug) {
            $category = Category::withoutGlobalScope('active')->where('slug', $slug)->first();

            if (! $category) {
                continue;
            }

            $parentItem = $this->createCategoryItem($menu->id, $rootId, $category, $position--);

            $children = Category::withoutGlobalScope('active')
                ->where('parent_id', $category->id)
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            $childPosition = $children->count();

            foreach ($children as $child) {
                $this->createCategoryItem($menu->id, $parentItem->id, $child, $childPosition--);
            }
        }

        return $menu->id;
    }

    private function ensureMenu(string $internalName): Menu
    {
        $menu = Menu::withoutGlobalScope('active')
            ->whereTranslation('name', $internalName)
            ->first();

        if (! $menu) {
            $menu = new Menu();
            $menu->is_active = true;

            foreach (supported_locales() as $locale => $language) {
                $menu->translateOrNew($locale)->name = $internalName;
            }

            // Menu::created fires CreateRootMenuItem which builds the root item.
            $menu->save();
        }

        return $menu;
    }

    private function rootItemId(int $menuId): int
    {
        $rootId = MenuItem::withoutGlobalScope('not_root')
            ->where('menu_id', $menuId)
            ->where('is_root', true)
            ->value('id');

        if (! $rootId) {
            $data = [
                'menu_id' => $menuId,
                'type' => 'URL',
                'target' => '_self',
                'position' => 0,
                'is_root' => true,
                'is_fluid' => false,
                'is_active' => true,
            ];

            foreach (supported_locales() as $locale => $language) {
                $data[$locale]['name'] = 'root';
            }

            $rootId = MenuItem::create($data)->id;
        }

        return (int) $rootId;
    }

    private function clearItems(int $menuId): void
    {
        MenuItem::withoutGlobalScopes()
            ->where('menu_id', $menuId)
            ->where('is_root', false)
            ->delete();
    }

    /**
     * @param array<string, string> $names
     */
    private function createUrlItem(int $menuId, int $parentId, string $url, array $names, int $position): MenuItem
    {
        $data = [
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'type' => 'url',
            'url' => $url,
            'target' => '_self',
            'position' => $position,
            'is_root' => false,
            'is_fluid' => false,
            'is_active' => true,
        ];

        foreach (supported_locales() as $locale => $language) {
            $data[$locale]['name'] = $names[$locale] ?? $names['en'];
        }

        return MenuItem::create($data);
    }

    private function createCategoryItem(int $menuId, int $parentId, Category $category, int $position): MenuItem
    {
        $data = [
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'category_id' => $category->id,
            'type' => 'category',
            'target' => '_self',
            'position' => $position,
            'is_root' => false,
            'is_fluid' => false,
            'is_active' => true,
        ];

        foreach (supported_locales() as $locale => $language) {
            $data[$locale]['name'] = optional($category->translate($locale))->name ?: $category->name;
        }

        return MenuItem::create($data);
    }

    /**
     * @param array<string, string> $names
     */
    private function createPageItem(int $menuId, int $parentId, int $pageId, array $names, int $position): MenuItem
    {
        $data = [
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'page_id' => $pageId,
            'type' => 'page',
            'target' => '_self',
            'position' => $position,
            'is_root' => false,
            'is_fluid' => false,
            'is_active' => true,
        ];

        foreach (supported_locales() as $locale => $language) {
            $data[$locale]['name'] = $names[$locale] ?? $names['en'];
        }

        return MenuItem::create($data);
    }

    private function flushCaches(): void
    {
        foreach (['mega_menu', 'menu_items', 'categories', 'pages', 'settings'] as $tag) {
            try {
                Cache::tags($tag)->flush();
            } catch (\Throwable) {
                // Cache driver may not support tagging; full flush below covers it.
            }
        }

        try {
            Cache::flush();
        } catch (\Throwable) {
            // ignore
        }
    }
}
