<?php

namespace Modules\Tag\Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Modules\Tag\Entities\Tag;

class ImmaSeriLarisTagsSeeder extends Seeder
{
    /**
     * Canonical tags for immaserilaris.com (aligned with SPA / AESTHETIC categories).
     *
     * @return array<int, string>
     */
    private function canonicalTags(): array
    {
        return [
            // SPA — selaras subkategori
            'Facial',
            'Laser',
            'Massage',
            'Bath & Body',
            'Waxing',
            'Eyelash & Brow',
            'Hair Treatment',
            'Nail Care',
            'Bridal Package',
            'Skin Care',
            'Hair Removal',

            // Aesthetic / Estetik — selaras subkategori
            'Botox',
            'Filler',
            'Thread Lift',
            'IV Drip Therapy',
            'Skin Booster',
            'Injection',
            'Body Contouring',
            'Dimple',
            'Whitening Booster',
            'Surgery',

            // Jenama & protokol
            'Rejuran',
            'Juvelook',
            'Profhilo',
            'PRP',
            'Pigmentation Treatment',

            // Penapis rawatan tambahan
            'Anti-Aging',
            'Acne Treatment',
            'Scar Treatment',
            'Nose Reshaping',
            'Vitamin Booster',
            'Double Eyelid',
            'Lip Enhancement',

            // Promosi & pakej
            'Promo',
            'Package Deal',
            'Seasonal Promo',
        ];
    }

    /**
     * Deprecated tags to remove (pricing labels, duplicates, typos).
     *
     * @return array<int, string>
     */
    private function deprecatedSlugs(): array
    {
        return [
            'normal-price',
            'promo-year-end-t2',
            'remove-nail-polish',
            'nosetip',
            'tipnose',
            'eye-lash',
            'eyelash-extension',
            'manicure',
            'pedicure',
            'combo-drip',
            'benang',
            'estetik',
            'profilo',
            'pigment',
            'spa',
            'drip',
            'lipo',
            'baby-booster',
            'aesthetic-medicine',
            'promo', // replaced by title-case slug from "Promo" — same slug, keep one
        ];
    }


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $removed = $this->removeDeprecatedTags();
        $created = $this->ensureCanonicalTags();

        Cache::tags('tags')->flush();

        $this->command?->info("Tags synced: {$created} created, {$removed} deprecated removed.");
        $this->command?->info('Total tags: ' . Tag::count());
    }


    /**
     * Remove tags that should not be used for treatment filtering.
     *
     * @return int
     */
    private function removeDeprecatedTags(): int
    {
        $slugs = array_unique(array_merge(
            $this->deprecatedSlugs(),
            $this->slugsNotInCanonical()
        ));

        $tags = Tag::whereIn('slug', $slugs)->get();
        $count = $tags->count();

        foreach ($tags as $tag) {
            $tag->delete();
        }

        return $count;
    }


    /**
     * Slugs of existing tags that are not in the canonical list.
     *
     * @return array<int, string>
     */
    private function slugsNotInCanonical(): array
    {
        $canonicalSlugs = array_map(
            fn (string $name) => Str::slug($name),
            $this->canonicalTags()
        );

        return Tag::query()
            ->whereNotIn('slug', $canonicalSlugs)
            ->pluck('slug')
            ->all();
    }


    /**
     * Create any missing canonical tags.
     *
     * @return int
     */
    private function ensureCanonicalTags(): int
    {
        $created = 0;

        foreach ($this->canonicalTags() as $name) {
            if ($this->tagExists($name)) {
                continue;
            }

            Tag::create(['name' => $name]);
            $created++;
        }

        return $created;
    }


    /**
     * Check if a tag already exists by name or slug.
     *
     * @param string $name
     *
     * @return bool
     */
    private function tagExists(string $name): bool
    {
        $slug = Str::slug($name);

        return Tag::where('slug', $slug)
            ->orWhereHas('translations', function ($query) use ($name) {
                $query->where('name', $name);
            })
            ->exists();
    }
}
