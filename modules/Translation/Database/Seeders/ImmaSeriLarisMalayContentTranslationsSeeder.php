<?php

namespace Modules\Translation\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Category\Entities\Category;
use Modules\Page\Entities\Page;
use Modules\Menu\Entities\MenuItem;

class ImmaSeriLarisMalayContentTranslationsSeeder extends Seeder
{
    /**
     * Add Malay translations for pages, categories, and menu labels.
     *
     * @return void
     */
    public function run(): void
    {
        $this->translatePages();
        $this->translateCategories();
        $this->translateMenuItems();

        $this->command?->info('Malay content translations added for pages, categories, and menus.');
    }


    private function translatePages(): void
    {
        $pages = [
            'faq' => [
                'name' => 'Bantuan & Soalan Lazim',
                'body' => '<p>Cari jawapan tentang tempahan dan rawatan di bawah.</p>',
            ],
            'about-us' => [
                'name' => 'Tentang Kami',
                // body: use ImmaSeriLarisAboutPageSeeder for full Malay content
            ],
            'terms-conditions' => [
                'name' => 'Terma & Syarat',
            ],
            'privacy-policy' => [
                'name' => 'Dasar Privasi',
            ],
            'return-policy' => [
                'name' => 'Polisi Pemulangan',
            ],
        ];

        foreach ($pages as $slug => $data) {
            $page = Page::withoutGlobalScope('active')->where('slug', $slug)->first();

            if (! $page) {
                continue;
            }

            $msData = array_merge([
                'body' => $page->translate('en')?->body ?? '',
            ], $data);

            DB::table('page_translations')->updateOrInsert(
                ['page_id' => $page->id, 'locale' => 'ms'],
                $msData
            );
        }
    }


    private function translateCategories(): void
    {
        $map = [
            'spa' => 'SPA',
            'aesthetic-estetik' => 'ESTETIK / AESTHETIC',
            'cosmetik' => 'Cosmetik',
            'facial' => 'Facial',
            'massage' => 'Urutan',
            'waxing' => 'Waxing',
            'manicure-pedicure' => 'Manikur & Pedikur',
            'bridal-package' => 'Pakej Pengantin',
            'botox' => 'Botox',
            'filler' => 'Filler',
            'benang' => 'Benang (Thread Lift)',
            'drip' => 'IV Drip',
            'skin-booster' => 'Skin Booster',
        ];

        foreach ($map as $slug => $name) {
            $category = Category::withoutGlobalScope('active')->where('slug', $slug)->first();

            if (! $category) {
                continue;
            }

            DB::table('category_translations')->updateOrInsert(
                ['category_id' => $category->id, 'locale' => 'ms'],
                ['name' => $name]
            );
        }
    }


    private function translateMenuItems(): void
    {
        $map = [
            'Treatment' => 'Rawatan',
            'About Us' => 'Tentang Kami',
            'Terms Of Use' => 'Terma Penggunaan',
            'FAQ' => 'Soalan Lazim',
            'Help & FAQ' => 'Bantuan & Soalan Lazim',
        ];

        MenuItem::withoutGlobalScopes()
            ->where('is_root', false)
            ->get()
            ->each(function (MenuItem $item) use ($map) {
                $enName = $item->translate('en')?->name ?? $item->name;

                if (! isset($map[$enName])) {
                    return;
                }

                $item->translations()->updateOrCreate(
                    ['locale' => 'ms'],
                    ['name' => $map[$enName]]
                );
            });
    }
}
