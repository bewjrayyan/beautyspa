<?php

use Modules\Menu\MegaMenu\Menu;

if (!function_exists('storefront_js_trans')) {
    /**
     * Resolve a storefront translation for inline JS (AestheticCart.langs).
     * Falls back to module lang files when the translation loader cache is stale.
     */
    function storefront_js_trans(string $key): string
    {
        $line = trans($key);

        if ($line !== $key) {
            return $line;
        }

        if (! preg_match('/^storefront::([^.]+)\.(.+)$/', $key, $matches)) {
            return $key;
        }

        [, $group, $item] = $matches;

        foreach ([locale(), 'en'] as $lang) {
            $path = module_path('Storefront', "Resources/lang/{$lang}/{$group}.php");

            if (! is_file($path)) {
                continue;
            }

            $value = data_get(require $path, $item);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return $key;
    }
}

if (!function_exists('storefront_header_logo_id')) {
    function storefront_header_logo_id()
    {
        return setting('storefront_header_logo') ?: setting('admin_logo');
    }
}

if (!function_exists('storefront_square_logo_id')) {
    /**
     * Media file ID for the 1:1 square brand logo.
     */
    function storefront_square_logo_id()
    {
        return setting('storefront_square_logo')
            ?: setting('storefront_favicon')
            ?: setting('admin_small_logo');
    }
}

if (!function_exists('storefront_square_logo_url')) {
    /**
     * Public URL for the 1:1 square brand logo, or null when none is configured.
     */
    function storefront_square_logo_url(): ?string
    {
        $fileId = storefront_square_logo_id();

        if (! $fileId) {
            return null;
        }

        $file = \Illuminate\Support\Facades\Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
            return \Modules\Media\Entities\File::findOrNew($fileId);
        });

        return ($file && $file->exists && $file->path) ? $file->path : null;
    }
}

if (!function_exists('storefront_favicon_file')) {
    function storefront_favicon_file(): ?\Modules\Media\Entities\File
    {
        $fileId = setting('storefront_favicon') ?: setting('storefront_header_logo') ?: setting('admin_logo');

        if (! $fileId) {
            return null;
        }

        $file = \Modules\Media\Entities\File::find($fileId);

        return ($file && $file->exists) ? $file : null;
    }
}

if (!function_exists('storefront_favicon_url')) {
    function storefront_favicon_url(): ?string
    {
        if (! storefront_favicon_file()) {
            return null;
        }

        return absolute_public_url('/favicon.ico');
    }
}

if (!function_exists('storefront_favicon_touch_icon_url')) {
    function storefront_favicon_touch_icon_url(): ?string
    {
        $path = storefront_favicon_file()?->path;

        return $path ? absolute_public_url($path) : null;
    }
}

if (!function_exists('storefront_favicon_mime')) {
    function storefront_favicon_mime(): string
    {
        return 'image/x-icon';
    }
}

if (!function_exists('resolve_theme_color')) {
    /**
     * Resolve color code by the given theme name.
     *
     * @param string $name
     *
     * @return string
     */
    function resolve_theme_color($color)
    {
        $colors = [
            'blue' => '#0068e1',
            'bondi-blue' => '#0095b6',
            'cornflower' => '#6453f7',
            'violet' => '#723881',
            'red' => '#f51e46',
            'yellow' => '#fa9928',
            'orange' => '#fd6602',
            'green' => '#59b210',
            'pink' => '#ff749f',
            'black' => '#2a3447',
            'indigo' => '#4b0082',
            'magenta' => '#f8008c',
        ];

        return $colors[$color] ?? '#0068e1';
    }
}

if (!function_exists('storefront_theme_color')) {
    function storefront_theme_color()
    {
        if (setting('storefront_theme_color') === 'custom_color') {
            return setting('storefront_custom_theme_color', '#0068e1');
        }

        return resolve_theme_color(setting('storefront_theme_color'));
    }
}

if (!function_exists('mail_theme_color')) {
    function mail_theme_color()
    {
        if (setting('storefront_mail_theme_color') === 'custom_color') {
            return setting('storefront_custom_mail_theme_color', '#0068e1');
        }

        return resolve_theme_color(setting('storefront_mail_theme_color'));
    }
}

if (!function_exists('mega_menu_classes')) {
    function mega_menu_classes(Menu $menu, $type = 'category_menu')
    {
        $classes = [];

        if ($type === 'primary_menu') {
            array_push($classes, 'nav-item swiper-slide');
        }

        if ($menu->isFluid()) {
            array_push($classes, 'fluid-menu');
        } else if ($menu->hasSubMenus()) {
            array_push($classes, 'dropdown', 'multi-level');
        }

        return implode(' ', $classes);
    }
}

if (!function_exists('product_type_label')) {
    /**
     * @param \Modules\Product\Entities\Product $product
     */
    function product_type_label($product): ?string
    {
        if ($product->isPhysicalProduct()) {
            return null;
        }

        if ($product->isVirtualTreatment()) {
            return trans('storefront::product.virtual_treatment');
        }

        return null;
    }
}


if (!function_exists('product_consultation_label')) {
    function product_consultation_label(): string
    {
        $label = setting('storefront_product_consultation_label');

        if (filled($label)) {
            return $label;
        }

        return trans('storefront::product.get_free_consultations');
    }
}


if (!function_exists('product_consultation_url')) {
    /**
     * @param \Modules\Product\Entities\Product $product
     */
    function product_consultation_url($product): string
    {
        $template = setting('storefront_product_consultation_url');

        if (blank($template)) {
            return route('contact.create');
        }

        return str_replace(
            [
                '{product_name}',
                '{product_url}',
                '{product_id}',
                '{product_slug}',
            ],
            [
                rawurlencode($product->name),
                rawurlencode($product->url()),
                (string) $product->id,
                $product->slug,
            ],
            $template
        );
    }
}


if (!function_exists('products_listing_title')) {
    /**
     * Title for the main products listing page (no category/brand/tag filter).
     */
    function products_listing_title(): string
    {
        $title = setting('storefront_products_listing_title');

        if (filled($title)) {
            return $title;
        }

        return trans('storefront::products.shop');
    }
}

if (!function_exists('products_view_mode')) {
    /**
     * Get the products view mode.
     *
     * @return string
     */
    function products_view_mode()
    {
        return request('viewMode', 'grid');
    }
}

if (!function_exists('order_status_badge_class')) {
    /**
     * Get the products view mode.
     *
     * @param string $status
     *
     * @return string
     */
    function order_status_badge_class($status)
    {
        $classes = [
            'pending' => 'badge-info',
            'processing' => 'badge-primary',
            'completed' => 'badge-success',
            'canceled' => 'badge-danger',
            // legacy migrated values
            'on_hold' => 'badge-info',
            'pending_payment' => 'badge-info',
            'refunded' => 'badge-danger',
        ];

        return $classes[$status] ?? 'badge-info';
    }
}

if (!function_exists('payment_status_badge_class')) {
    function payment_status_badge_class(?string $paymentStatus): string
    {
        $classes = [
            'pending' => 'badge-warning',
            'processing' => 'badge-info',
            'paid' => 'badge-success',
            'canceled' => 'badge-danger',
            'refunded' => 'badge-danger',
        ];

        return $classes[$paymentStatus] ?? 'badge-default';
    }
}

if (!function_exists('treatment_status_badge_class')) {
    function treatment_status_badge_class(?string $treatmentStatus): string
    {
        $classes = [
            'pending' => 'badge-warning',
            'in_progress' => 'badge-info',
            'completed' => 'badge-success',
            'canceled' => 'badge-danger',
        ];

        return $classes[$treatmentStatus] ?? 'badge-default';
    }
}

if (!function_exists('social_links')) {
    /**
     * Get the social links.
     *
     * @param string $status
     *
     * @return string
     */
    function social_links()
    {
        return collect([
            'lab la-facebook' => setting('storefront_facebook_link'),
            'lab la-twitter' => setting('storefront_twitter_link'),
            'lab la-instagram' => setting('storefront_instagram_link'),
            'lab la-youtube' => setting('storefront_youtube_link'),
        ])->reject(function ($link) {
            return is_null($link);
        });
    }
}

if (!function_exists('social_link_name')) {
    /**
     * Get the social link name.
     *
     * @param string $icon
     *
     * @return string
     */
    function social_link_name($icon)
    {
        return [
            'lab la-facebook' => trans('storefront::storefront.social_links.facebook'),
            'lab la-twitter' => trans('storefront::storefront.social_links.twitter'),
            'lab la-instagram' => trans('storefront::storefront.social_links.instagram'),
            'lab la-youtube' => trans('storefront::storefront.social_links.youtube'),
        ][$icon];
    }
}

if (!function_exists('mobile_product_tab_label')) {
    /**
     * Short label for homepage product tabs on mobile (admin titles may be long).
     */
    function mobile_product_tab_label(?string $title): string
    {
        if (blank($title)) {
            return '';
        }

        $slug = \Illuminate\Support\Str::slug($title, '_');
        $translationKey = "storefront::storefront.mobile_tab_labels.{$slug}";
        $translated = trans($translationKey);

        if ($translated !== $translationKey) {
            return $translated;
        }

        $patterns = [
            '/^latest\b/iu' => 'storefront::storefront.mobile_tab_labels._latest',
            '/^recently viewed\b/iu' => 'storefront::storefront.mobile_tab_labels._recent',
            '/^popular\b/iu' => 'storefront::storefront.mobile_tab_labels._popular',
            '/^new arrivals?\b/iu' => 'storefront::storefront.mobile_tab_labels._new',
            '/^our\b/iu' => 'storefront::storefront.mobile_tab_labels._our',
        ];

        foreach ($patterns as $pattern => $key) {
            if (preg_match($pattern, trim($title))) {
                return trans($key);
            }
        }

        $firstWord = strtok(trim($title), ' ');

        return \Illuminate\Support\Str::limit($firstWord ?: $title, 10, '');
    }
}

if (!function_exists('vite_build_asset')) {
    function vite_build_asset(string $source): ?string
    {
        static $manifest = null;

        $manifestPath = public_path('build/manifest.json');

        if (! is_readable($manifestPath)) {
            return null;
        }

        if ($manifest === null) {
            $manifest = json_decode((string) file_get_contents($manifestPath), true);

            if (! is_array($manifest)) {
                $manifest = [];
            }
        }

        if (! isset($manifest[$source]['file'])) {
            return null;
        }

        try {
            return \Illuminate\Support\Facades\Vite::asset($source);
        } catch (\Throwable) {
            return null;
        }
    }
}

if (!function_exists('font_slug')) {
    /**
     * Map storefront display font name to local @fontsource CSS slug.
     */
    function font_slug(?string $font): string
    {
        return match ($font) {
            'Rubik' => 'rubik',
            'Roboto' => 'roboto',
            'Open Sans' => 'open-sans',
            'Montserrat' => 'montserrat',
            'Nunito' => 'nunito',
            'Raleway' => 'raleway',
            'Oswald' => 'oswald',
            'Quicksand' => 'quicksand',
            'Hind' => 'hind',
            'Fira Sans' => 'fira-sans',
            'Mukta' => 'mukta',
            'Karla' => 'karla',
            'Barlow' => 'barlow',
            'Source Sans 3' => 'source-sans-3',
            'IBM Plex Sans' => 'ibm-plex-sans',
            'Work Sans' => 'work-sans',
            default => 'poppins',
        };
    }
}

if (!function_exists('font_vite_entry')) {
    /**
     * Vite entry path for the selected self-hosted display font.
     */
    function font_vite_entry(?string $font = null): string
    {
        $slug = font_slug($font ?: setting('storefront_display_font', 'Poppins'));

        return "modules/Storefront/Resources/assets/public/fonts/{$slug}.css";
    }
}

if (!function_exists('font_url')) {
    /**
     * Public URL for the selected self-hosted display font stylesheet.
     *
     * @param string $font
     *
     * @return string
     */
    function font_url($font)
    {
        $entry = font_vite_entry($font);

        try {
            return \Illuminate\Support\Facades\Vite::asset($entry);
        } catch (\Throwable $e) {
            return $entry;
        }
    }
}


if (!function_exists('category_showcase_theme')) {
    /**
     * Visual theme key for category product listing hero banners.
     */
    function category_showcase_theme(string $slug, string $name = ''): string
    {
        $haystack = mb_strtolower($slug . ' ' . $name);

        return match (true) {
            str_contains($haystack, 'spa') => 'spa',
            str_contains($haystack, 'aesthetic') || str_contains($haystack, 'estetik') => 'aesthetic',
            str_contains($haystack, 'cosmetik') || str_contains($haystack, 'cosmetic') => 'cosmetik',
            default => 'default',
        };
    }
}

if (!function_exists('category_showcase_icon')) {
    /**
     * Icon class for category product listing hero banners.
     */
    function category_showcase_icon(string $slug, string $name = ''): string
    {
        $haystack = mb_strtolower($slug . ' ' . $name);

        return match (true) {
            str_contains($haystack, 'spa') => 'las la-spa',
            str_contains($haystack, 'aesthetic') || str_contains($haystack, 'estetik') => 'las la-syringe',
            str_contains($haystack, 'cosmetik') || str_contains($haystack, 'cosmetic') => 'las la-palette',
            default => 'las la-layer-group',
        };
    }
}

if (!function_exists('category_showcase_copy')) {
    /**
     * Bilingual hero copy for a category products page.
     * Uses slug-specific strings when present; otherwise generic :name templates.
     *
     * @return array{eyebrow: string, title: string, description: string, shop_by_category: string, coming_soon: string, icon: string, theme: string}
     */
    function category_showcase_copy(\Modules\Category\Entities\Category $category): array
    {
        $name = $category->name;
        $slug = $category->slug;
        $prefix = "storefront::products.category_showcase.{$slug}";
        $hasSpecific = trans("{$prefix}.title") !== "{$prefix}.title";

        $replace = ['name' => $name];

        if ($hasSpecific) {
            $eyebrow = trans("{$prefix}.eyebrow");
            $title = trans("{$prefix}.title");
            $description = trans("{$prefix}.description");
        } else {
            $eyebrow = trans('storefront::products.category_showcase.generic.eyebrow', $replace);
            $title = trans('storefront::products.category_showcase.generic.title', $replace);
            $description = trans('storefront::products.category_showcase.generic.description', $replace);
        }

        return [
            'eyebrow' => $eyebrow,
            'title' => $title,
            'description' => $description,
            'shop_by_category' => trans('storefront::products.category_showcase.shop_by_category', $replace),
            'coming_soon' => trans('storefront::products.category_showcase.coming_soon'),
            'icon' => category_showcase_icon($slug, $name),
            'theme' => category_showcase_theme($slug, $name),
        ];
    }
}

if (!function_exists('category_menu_item_icon')) {
    /**
     * Icon for storefront category mega menu items (SPA, aesthetic, cosmetik, …).
     */
    function category_menu_item_icon(Menu $menu): string
    {
        $haystack = mb_strtolower($menu->name());

        $mapped = match (true) {
            str_contains($haystack, 'spa') => 'las la-spa',
            str_contains($haystack, 'aesthetic') || str_contains($haystack, 'estetik') => 'las la-syringe',
            str_contains($haystack, 'cosmetik') || str_contains($haystack, 'cosmetic') => 'las la-palette',
            default => null,
        };

        if ($mapped !== null) {
            return $mapped;
        }

        if ($menu->hasIcon()) {
            return $menu->icon();
        }

        return 'las la-layer-group';
    }
}

if (!function_exists('category_menu_item_label')) {
    /**
     * Short label for narrow category sidebar (e.g. "AESTHETIC / ES......").
     */
    function category_menu_item_label(string $name, int $visibleLength = 14): string
    {
        $name = trim($name);

        if (mb_strlen($name) <= $visibleLength) {
            return $name;
        }

        return rtrim(mb_substr($name, 0, $visibleLength)) . '......';
    }
}
