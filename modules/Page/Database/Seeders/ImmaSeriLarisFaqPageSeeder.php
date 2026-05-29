<?php

namespace Modules\Page\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Page\Entities\Page;

class ImmaSeriLarisFaqPageSeeder extends Seeder
{
    /**
     * Update FAQ page title and SEO for IMMA Seri Laris.
     *
     * @return void
     */
    public function run(): void
    {
        $page = Page::withoutGlobalScope('active')->where('slug', 'faq')->first();

        if (! $page) {
            $this->command?->error('FAQ page (slug: faq) not found.');

            return;
        }

        $page->update(['is_active' => true]);

        $page->update([
            'en' => [
                'name' => 'Help & FAQ',
                'body' => '<p>Find answers about booking and treatments below.</p>',
            ],
            'ms' => [
                'name' => 'Bantuan & Soalan Lazim',
                'body' => '<p>Cari jawapan tentang tempahan dan rawatan di bawah.</p>',
            ],
        ]);

        $page->saveMetaData([
            'meta_title' => 'FAQ — Treatments & Booking | IMMA Seri Laris',
            'meta_description' => 'Frequently asked questions about spa, aesthetic & estetik treatments, cosmetik products, and online booking at IMMA Seri Laris.',
        ]);

        $this->command?->info('FAQ page updated for IMMA Seri Laris.');
    }
}
