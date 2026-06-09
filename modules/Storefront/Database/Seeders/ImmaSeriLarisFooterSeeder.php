<?php

namespace Modules\Storefront\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuItem;
use Modules\Page\Entities\Page;
use Modules\Tag\Entities\Tag;
use Modules\Setting\Entities\Setting;
use Modules\Category\Entities\Category;
use Modules\Page\Database\Seeders\ImmaSeriLarisAboutPageSeeder;
use Modules\Page\Database\Seeders\ImmaSeriLarisTermsPageSeeder;
use Modules\Page\Database\Seeders\ImmaSeriLarisPrivacyPageSeeder;

/**
 * Rebuild the storefront footer columns (Footer Menu One, Footer Menu Two,
 * Footer Tags) after the menus / pages tables were wiped.
 *
 * Idempotent: re-running updates the same menus, pages and settings.
 */
class ImmaSeriLarisFooterSeeder extends Seeder
{
    private const MENU_ONE_NAME = 'Footer Quick Links';
    private const MENU_TWO_NAME = 'Footer Information';

    public function run(): void
    {
        $pages = $this->ensurePages();

        // About / Terms / Privacy seeders use DB::updateOrInsert and are safe to
        // run against pre-created pages. FAQ is handled inline because its seeder
        // uses an Astrotomic model update that conflicts with existing translations.
        $this->call([
            ImmaSeriLarisAboutPageSeeder::class,
            ImmaSeriLarisTermsPageSeeder::class,
            ImmaSeriLarisPrivacyPageSeeder::class,
        ]);

        $this->fillFaqPage($pages['faq'] ?? null);

        $menuOneId = $this->buildQuickLinksMenu();
        $menuTwoId = $this->buildInformationMenu($pages);

        $this->restoreSettings($menuOneId, $menuTwoId, $pages);

        $this->flushCaches();

        $this->command?->info('Footer columns restored: Quick Links + Information menus, tags and settings.');
    }

    /**
     * Recreate the standard CMS pages used by the footer with the exact slugs
     * expected by the content seeders. Body is a placeholder; the content
     * seeders fill the real copy.
     *
     * @return array<string, int> slug => page id
     */
    private function ensurePages(): array
    {
        $definitions = [
            'about-us' => ['en' => 'About Us', 'ms' => 'Tentang Kami'],
            'faq' => ['en' => 'FAQ', 'ms' => 'Soalan Lazim'],
            'terms-conditions' => ['en' => 'Terms & Conditions', 'ms' => 'Terma & Syarat'],
            'privacy-policy' => ['en' => 'Privacy Policy', 'ms' => 'Dasar Privasi'],
        ];

        $pages = [];

        foreach ($definitions as $slug => $names) {
            $page = Page::withoutGlobalScope('active')->where('slug', $slug)->first();

            if (! $page) {
                $page = new Page();
                $page->is_active = true;

                foreach (supported_locales() as $locale => $language) {
                    $name = $names[$locale] ?? $names['en'];
                    $page->translateOrNew($locale)->name = $name;
                    $page->translateOrNew($locale)->body = '<p>' . e($name) . '</p>';
                }

                $page->save();

                // Sluggable derives the slug from the current-locale name; force
                // the canonical slug the content seeders look up.
                DB::table('pages')->where('id', $page->id)->update(['slug' => $slug]);
                $page->slug = $slug;
            } else {
                $page->update(['is_active' => true]);
            }

            $pages[$slug] = $page->id;
        }

        return $pages;
    }

    private function fillFaqPage(?int $pageId): void
    {
        if (! $pageId) {
            return;
        }

        DB::table('page_translations')->updateOrInsert(
            ['page_id' => $pageId, 'locale' => 'en'],
            ['name' => 'Help & FAQ', 'body' => '<p>Find answers about booking and treatments below.</p>']
        );

        DB::table('page_translations')->updateOrInsert(
            ['page_id' => $pageId, 'locale' => 'ms'],
            ['name' => 'Bantuan & Soalan Lazim', 'body' => '<p>Cari jawapan tentang tempahan dan rawatan di bawah.</p>']
        );

        $page = Page::withoutGlobalScope('active')->find($pageId);

        $page?->saveMetaData([
            'meta_title' => 'FAQ — Treatments & Booking | IMMA Seri Laris',
            'meta_description' => 'Frequently asked questions about spa, aesthetic & estetik treatments, cosmetik products, and online booking at IMMA Seri Laris.',
        ]);
    }

    private function buildQuickLinksMenu(): int
    {
        $menu = $this->ensureMenu(self::MENU_ONE_NAME, [
            'en' => 'Quick Links',
            'ms' => 'Pautan Pantas',
        ]);

        $rootId = $this->rootItemId($menu->id);

        $this->clearItems($menu->id);

        $position = 1;

        $this->createUrlItem($menu->id, $rootId, 'products', [
            'en' => 'All Products',
            'ms' => 'Semua Produk',
        ], $position++);

        foreach (['spa', 'aesthetic-estetik', 'cosmetik', 'facial', 'massage'] as $slug) {
            $category = Category::withoutGlobalScope('active')->where('slug', $slug)->first();

            if ($category) {
                $this->createCategoryItem($menu->id, $rootId, $category, $position++);
            }
        }

        return $menu->id;
    }

    /**
     * @param array<string, int> $pages
     */
    private function buildInformationMenu(array $pages): int
    {
        $menu = $this->ensureMenu(self::MENU_TWO_NAME, [
            'en' => 'Information',
            'ms' => 'Maklumat',
        ]);

        $rootId = $this->rootItemId($menu->id);

        $this->clearItems($menu->id);

        $position = 1;

        $pageLinks = [
            'about-us' => ['en' => 'About Us', 'ms' => 'Tentang Kami'],
            'faq' => ['en' => 'FAQ', 'ms' => 'Soalan Lazim'],
            'terms-conditions' => ['en' => 'Terms & Conditions', 'ms' => 'Terma & Syarat'],
            'privacy-policy' => ['en' => 'Privacy Policy', 'ms' => 'Dasar Privasi'],
        ];

        foreach ($pageLinks as $slug => $names) {
            if (isset($pages[$slug])) {
                $this->createPageItem($menu->id, $rootId, $pages[$slug], $names, $position++);
            }
        }

        $this->createUrlItem($menu->id, $rootId, 'contact', [
            'en' => 'Contact Us',
            'ms' => 'Hubungi Kami',
        ], $position++);

        return $menu->id;
    }

    /**
     * @param array<string, string> $names
     */
    private function ensureMenu(string $internalName, array $names): Menu
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
    private function createUrlItem(int $menuId, int $rootId, string $url, array $names, int $position): void
    {
        $data = [
            'menu_id' => $menuId,
            'parent_id' => $rootId,
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

        MenuItem::create($data);
    }

    private function createCategoryItem(int $menuId, int $rootId, Category $category, int $position): void
    {
        $data = [
            'menu_id' => $menuId,
            'parent_id' => $rootId,
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

        MenuItem::create($data);
    }

    /**
     * @param array<string, string> $names
     */
    private function createPageItem(int $menuId, int $rootId, int $pageId, array $names, int $position): void
    {
        $data = [
            'menu_id' => $menuId,
            'parent_id' => $rootId,
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

        MenuItem::create($data);
    }

    /**
     * @param array<string, int> $pages
     */
    private function restoreSettings(int $menuOneId, int $menuTwoId, array $pages): void
    {
        Setting::set('storefront_footer_menu_one', $menuOneId);
        Setting::set('storefront_footer_menu_two', $menuTwoId);

        $this->setTranslatableSetting('storefront_footer_menu_one_title', [
            'en' => 'Quick Links',
            'ms' => 'Pautan Pantas',
        ]);

        $this->setTranslatableSetting('storefront_footer_menu_two_title', [
            'en' => 'Information',
            'ms' => 'Maklumat',
        ]);

        $tagIds = Tag::orderBy('id')->take(8)->pluck('id')->map(fn ($id) => (int) $id)->all();
        Setting::set('storefront_footer_tags', $tagIds);

        if (isset($pages['privacy-policy'])) {
            Setting::set('storefront_privacy_page', $pages['privacy-policy']);
        }
    }

    /**
     * @param array<string, string> $values
     */
    private function setTranslatableSetting(string $key, array $values): void
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        $setting->is_translatable = true;
        $setting->save();

        foreach (supported_locales() as $locale => $language) {
            $setting->translateOrNew($locale)->value = $values[$locale] ?? $values['en'];
        }

        $setting->save();
    }

    private function flushCaches(): void
    {
        foreach (['menu_items', 'categories', 'pages', 'settings', 'tags', 'mega_menu'] as $tag) {
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
