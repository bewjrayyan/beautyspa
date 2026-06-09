<?php

namespace Modules\Storefront\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Entities\File;
use Modules\Slider\Entities\Slider;
use Modules\Slider\Entities\SliderSlide;
use Modules\Setting\Entities\Setting;
use Modules\Category\Entities\Category;

/**
 * Rebuild the storefront home hero slider after the sliders/files tables were
 * wiped. The original banner images still exist on disk (storage/app/public/media)
 * but lost their `files` records, so we recreate the File rows, the Slider, its
 * slides, and wire the `storefront_slider` setting.
 *
 * Idempotent: re-running rebuilds the same slider and re-links the images.
 */
class ImmaSeriLarisSliderSeeder extends Seeder
{
    private const SLIDER_NAME = 'Home Slider';

    /**
     * Ordered slides: existing media filename => CTA target category slug
     * (null = link to the all-products page).
     *
     * @var array<int, array{file: string, category: ?string}>
     */
    private const SLIDES = [
        ['file' => 'zTpcS2rDK25dT7bA1TAlxzqSdqXUaVPKpsnbdrks.jpg', 'category' => null],            // Rawatan Spa & Aesthetic
        ['file' => 'nJsfRlEQZN3WZdOaEsRfpHYyCTdlE9HUzEPbgrJi.jpg', 'category' => 'skin-booster'],   // Injection Skin Booster
        ['file' => 'szwov8kz8KOWbgBY8n7WKch98LODo71e49f8TCsb.jpg', 'category' => 'benang'],         // Benang Thread Lift
        ['file' => 'zOhCgH1LSMeROhyqEoe85jnaCI5NGuLarJiI52mH.jpg', 'category' => 'eyelash-brow'],   // Eye Lash / Brow
    ];

    public function run(): void
    {
        $slider = $this->ensureSlider();

        $slider->slides()->delete();

        $position = 0;

        foreach (self::SLIDES as $definition) {
            $fileId = $this->ensureFile($definition['file']);

            if ($fileId === null) {
                $this->command?->warn("Slide image missing on disk: {$definition['file']}");

                continue;
            }

            $this->createSlide($slider, $fileId, $this->ctaUrl($definition['category']), $position++);
        }

        Setting::set('storefront_slider', $slider->id);

        $slider->clearCache();
        $this->flushCaches();

        $this->command?->info("Home slider restored with {$position} slide(s).");
    }

    private function ensureSlider(): Slider
    {
        $slider = Slider::whereTranslation('name', self::SLIDER_NAME)->first();

        if (! $slider) {
            $slider = new Slider([
                'speed' => 1000,
                'autoplay' => true,
                'autoplay_speed' => 5000,
                'fade' => false,
                'dots' => true,
                'arrows' => true,
            ]);

            foreach (supported_locales() as $locale => $language) {
                $slider->translateOrNew($locale)->name = self::SLIDER_NAME;
            }

            // The Slider `saved` event runs saveSlides(request('slides', [])),
            // which is a no-op here because no slides exist yet.
            $slider->save();
        }

        return $slider;
    }

    private function ensureFile(string $filename): ?int
    {
        $path = "media/{$filename}";

        if (! Storage::disk('public_storage')->exists($path)) {
            return null;
        }

        $file = File::where('path', $path)->first();

        if ($file) {
            return $file->id;
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $uploaderId = File::query()->whereNotNull('user_id')->value('user_id')
            ?? optional(\Modules\User\Entities\User::orderBy('id')->first())->id
            ?? 1;

        $file = File::create([
            'user_id' => $uploaderId,
            'filename' => $filename,
            'disk' => 'public_storage',
            'path' => $path,
            'extension' => $extension,
            'mime' => $this->mimeFor($extension),
            'size' => Storage::disk('public_storage')->size($path),
        ]);

        return $file->id;
    }

    private function createSlide(Slider $slider, int $fileId, string $ctaUrl, int $position): void
    {
        /** @var SliderSlide $slide */
        $slide = $slider->slides()->create([
            'options' => [],
            'call_to_action_url' => $ctaUrl,
            'open_in_new_window' => false,
            'position' => $position,
        ]);

        foreach (supported_locales() as $locale => $language) {
            $translation = $slide->translateOrNew($locale);
            $translation->file_id = $fileId;
            $translation->caption_1 = null;
            $translation->caption_2 = null;
            $translation->call_to_action_text = null;
            $translation->direction = 'right';
        }

        $slide->save();
    }

    private function ctaUrl(?string $categorySlug): string
    {
        if ($categorySlug) {
            $category = Category::withoutGlobalScope('active')->where('slug', $categorySlug)->first();

            if ($category) {
                return $category->url();
            }
        }

        return storefront_route('products.index');
    }

    private function mimeFor(string $extension): string
    {
        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }

    private function flushCaches(): void
    {
        foreach (['sliders', 'settings'] as $tag) {
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
