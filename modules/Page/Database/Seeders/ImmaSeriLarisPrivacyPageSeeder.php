<?php

namespace Modules\Page\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Page\Entities\Page;
use Modules\Page\Support\LegalPageDefaults;

class ImmaSeriLarisPrivacyPageSeeder extends Seeder
{
    /**
     * Update Privacy Policy page for IMMA Seri Laris.
     *
     * @return void
     */
    public function run(): void
    {
        $page = Page::withoutGlobalScope('active')->where('slug', 'privacy-policy')->first();

        if (! $page) {
            $this->command?->error('Page privacy-policy not found.');

            return;
        }

        $page->update(['is_active' => true]);

        DB::table('page_translations')->updateOrInsert(
            ['page_id' => $page->id, 'locale' => 'en'],
            [
                'name' => 'Privacy Policy',
                'body' => LegalPageDefaults::body('privacy', 'en'),
            ]
        );

        DB::table('page_translations')->updateOrInsert(
            ['page_id' => $page->id, 'locale' => 'ms'],
            [
                'name' => 'Dasar Privasi',
                'body' => LegalPageDefaults::body('privacy', 'ms'),
            ]
        );

        $page->touch();

        $page->saveMetaData([
            'meta_title' => 'Privacy Policy | IMMA Seri Laris',
            'meta_description' => 'Privacy policy for IMMA Seri Laris spa, aesthetic and cosmetik services at immaserilaris.com (PDPA Malaysia).',
        ]);

        $this->command?->info('Privacy Policy page updated for IMMA Seri Laris.');
    }
}
