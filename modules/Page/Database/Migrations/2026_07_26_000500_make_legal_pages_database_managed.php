<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Page\Support\LegalPageDefaults;

return new class extends Migration
{
    private const PLACEHOLDERS = [
        '<p>Please read our terms for spa, aesthetic and cosmetik services at IMMA Seri Laris.</p>',
        '<p>Sila baca terma perkhidmatan spa, estetik dan cosmetik IMMA Seri Laris.</p>',
        '<p>How IMMA Seri Laris protects your personal data.</p>',
        '<p>Bagaimana IMMA Seri Laris melindungi data peribadi anda.</p>',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('pages') || ! Schema::hasTable('page_translations')) {
            return;
        }

        foreach (['terms-conditions' => 'terms', 'privacy-policy' => 'privacy'] as $slug => $document) {
            $pageId = DB::table('pages')->where('slug', $slug)->value('id');
            $contentUpdated = false;

            if (! $pageId) {
                continue;
            }

            DB::table('pages')->where('id', $pageId)->update(['is_active' => true]);

            foreach (['en', 'ms'] as $locale) {
                $translation = DB::table('page_translations')
                    ->where('page_id', $pageId)
                    ->where('locale', $locale)
                    ->first();

                $hasCustomBody = $translation
                    && filled($translation->body)
                    && ! in_array($translation->body, self::PLACEHOLDERS, true);

                if ($hasCustomBody) {
                    continue;
                }

                DB::table('page_translations')->updateOrInsert(
                    ['page_id' => $pageId, 'locale' => $locale],
                    [
                        'name' => $document === 'terms'
                            ? ($locale === 'ms' ? 'Terma & Syarat' : 'Terms & Conditions')
                            : ($locale === 'ms' ? 'Dasar Privasi' : 'Privacy Policy'),
                        'body' => LegalPageDefaults::body($document, $locale),
                    ]
                );
                $contentUpdated = true;
            }

            if ($contentUpdated) {
                DB::table('pages')->where('id', $pageId)->update(['updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // Legal content is editorial data and must not be destroyed on rollback.
    }
};
