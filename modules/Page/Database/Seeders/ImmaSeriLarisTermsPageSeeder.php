<?php

namespace Modules\Page\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Page\Entities\Page;

class ImmaSeriLarisTermsPageSeeder extends Seeder
{
    /**
     * Update Terms & Conditions page for IMMA Seri Laris.
     *
     * @return void
     */
    public function run(): void
    {
        $page = Page::withoutGlobalScope('active')->where('slug', 'terms-conditions')->first();

        if (! $page) {
            $this->command?->error('Page terms-conditions not found.');

            return;
        }

        $page->update(['is_active' => true]);

        DB::table('page_translations')->updateOrInsert(
            ['page_id' => $page->id, 'locale' => 'en'],
            [
                'name' => 'Terms & Conditions',
                'body' => '<p>Please read our terms for spa, aesthetic and cosmetik services at IMMA Seri Laris.</p>',
            ]
        );

        DB::table('page_translations')->updateOrInsert(
            ['page_id' => $page->id, 'locale' => 'ms'],
            [
                'name' => 'Terma & Syarat',
                'body' => '<p>Sila baca terma perkhidmatan spa, estetik dan cosmetik IMMA Seri Laris.</p>',
            ]
        );

        $page->saveMetaData([
            'meta_title' => 'Terms & Conditions | IMMA Seri Laris',
            'meta_description' => 'Terms and conditions for booking spa, aesthetic and cosmetik services at IMMA Seri Laris (immaserilaris.com).',
        ]);

        $this->command?->info('Terms & Conditions page updated for IMMA Seri Laris.');
    }
}
