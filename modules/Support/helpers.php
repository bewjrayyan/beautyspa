<?php

use AestheticCart\AestheticCart;
use Modules\Support\Locale;
use Modules\Support\RTLDetector;
use Modules\Support\Services\HtmlSanitizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\Intl\Currencies;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

if (!function_exists('str_between')) {
    /**
     * Get the portion of a string between the given values.
     *
     * @param string $subject
     * @param string $search
     *
     * @return string
     */
    function str_between($subject, $startsWith, $endsWith)
    {
        return str_after(str_before($subject, $endsWith), $startsWith);
    }
}

if (!function_exists('locale')) {
    /**
     * Get current locale.
     *
     * @return string
     */
    function locale()
    {
        return app()->getLocale();
    }
}

if (!function_exists('is_rtl')) {
    /**
     * Determine if the given / current locale is RTL script.
     *
     * @param string|null $locale
     *
     * @return bool
     */
    function is_rtl($locale = null)
    {
        return RTLDetector::detect($locale ?: locale());
    }
}

if (!function_exists('supported_currencies')) {
    /**
     * Get supported currency codes with safe fallbacks.
     *
     * @return array<int, string>
     */
    function supported_currencies()
    {
        $defaultCurrency = setting('default_currency') ?: 'MYR';
        $currencies = setting('supported_currencies');

        if (! is_array($currencies) || $currencies === []) {
            return [$defaultCurrency];
        }

        return $currencies;
    }
}

if (!function_exists('currency')) {
    /**
     * Get current currency.
     *
     * @return string
     */
    function currency()
    {
        $defaultCurrency = setting('default_currency') ?: 'MYR';

        if (app('inAdminPanel')) {
            return $defaultCurrency;
        }

        $currency = Cookie::get('currency');

        if (! in_array($currency, supported_currencies(), true)) {
            $currency = $defaultCurrency;
        }

        return $currency;
    }
}

if (!function_exists('locale_display_name')) {
    /**
     * Human-readable locale name for language selectors.
     *
     * @param string|null $locale
     *
     * @return string
     */
    function locale_display_name($locale = null)
    {
        $locale = $locale ?? locale();

        $names = [
            'en' => 'English',
            'ms' => 'Bahasa Malaysia',
        ];

        return $names[$locale] ?? Locale::name($locale);
    }
}

if (!function_exists('supported_locales')) {
    /**
     * Get all supported locales.
     *
     * @return array
     */
    function supported_locales()
    {
        return LaravelLocalization::getSupportedLocales();
    }
}

if (!function_exists('supported_locale_keys')) {
    /**
     * Get all supported locale keys.
     *
     * @return array
     */
    function supported_locale_keys()
    {
        return LaravelLocalization::getSupportedLanguagesKeys();
    }
}

if (!function_exists('aestheticcart_strip_install_base_from_url')) {
    /**
     * Remove APP_URL subdirectory from a URL path before LaravelLocalization runs.
     */
    function aestheticcart_strip_install_base_from_url(string $url): string
    {
        $basePath = \AestheticCart\Http\FixSubdirectoryRequest::basePath();

        if ($basePath === '') {
            return $url;
        }

        $parts = parse_url($url);

        if (! isset($parts['path']) || ! str_starts_with($parts['path'], $basePath)) {
            return $url;
        }

        $path = substr($parts['path'], strlen($basePath)) ?: '/';

        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'] ?? 'localhost';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $result = "{$scheme}://{$host}{$port}{$path}";

        if (! empty($parts['query'])) {
            $result .= '?' . $parts['query'];
        }

        if (! empty($parts['fragment'])) {
            $result .= '#' . $parts['fragment'];
        }

        return $result;
    }
}

if (!function_exists('aestheticcart_normalize_install_url')) {
    /**
     * Remove duplicate install subdirectory segments (e.g. /fleetcart/en/fleetcart/blog).
     */
    function aestheticcart_normalize_install_url(string $url): string
    {
        $basePath = \AestheticCart\Http\FixSubdirectoryRequest::basePath();

        if ($basePath === '') {
            return $url;
        }

        $parts = parse_url($url);

        if (! isset($parts['path'])) {
            return $url;
        }

        $path = $parts['path'];
        $localePattern = implode('|', array_map(
            fn (string $locale) => preg_quote($locale, '#'),
            supported_locale_keys()
        ));

        if ($localePattern !== '') {
            $path = preg_replace(
                '#(' . preg_quote($basePath, '#') . '/(?:' . $localePattern . '))' . preg_quote($basePath, '#') . '#',
                '$1',
                $path
            ) ?? $path;
        }

        while (str_contains($path, $basePath . $basePath)) {
            $path = str_replace($basePath . $basePath, $basePath, $path);
        }

        $parts['path'] = $path;

        return aestheticcart_build_url($parts);
    }
}

if (!function_exists('aestheticcart_apply_install_base_url')) {
    /**
     * Prepend APP_URL subdirectory (e.g. /fleetcart) when localization strips it.
     */
    function aestheticcart_apply_install_base_url(string $url): string
    {
        $basePath = \AestheticCart\Http\FixSubdirectoryRequest::basePath();

        if ($basePath === '') {
            return $url;
        }

        $url = aestheticcart_normalize_install_url($url);

        $parts = parse_url($url);

        if (! isset($parts['path'])) {
            return $url;
        }

        if (str_starts_with($parts['path'], $basePath)) {
            return aestheticcart_build_url($parts);
        }

        $parts['path'] = rtrim($basePath, '/') . '/' . ltrim($parts['path'], '/');

        return aestheticcart_normalize_install_url(aestheticcart_build_url($parts));
    }
}

if (!function_exists('aestheticcart_build_url')) {
    /**
     * @param array<string, mixed> $parts
     */
    function aestheticcart_build_url(array $parts): string
    {
        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'] ?? 'localhost';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '/';
        $result = "{$scheme}://{$host}{$port}{$path}";

        if (! empty($parts['query'])) {
            $result .= '?' . $parts['query'];
        }

        if (! empty($parts['fragment'])) {
            $result .= '#' . $parts['fragment'];
        }

        return $result;
    }
}

if (!function_exists('storefront_locale_base_url')) {
    /**
     * Base URL for storefront AJAX (locale-prefixed, with install subdirectory).
     */
    function storefront_locale_base_url(): string
    {
        return rtrim(config('app.url'), '/') . '/' . locale();
    }
}

if (! function_exists('storefront_home_url')) {
    /**
     * Localized storefront home URL with install subdirectory (safe for /fleetcart installs).
     */
    function storefront_home_url(): string
    {
        $homeUrl = \Illuminate\Support\Facades\Route::has('home')
            ? route('home')
            : url('/');

        return localized_url(locale(), $homeUrl);
    }
}

if (! function_exists('storefront_route')) {
    /**
     * Named storefront route with locale + install subdirectory (no duplicate /fleetcart).
     *
     * @param  array<string, mixed>  $parameters
     */
    function storefront_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        return aestheticcart_normalize_install_url(
            localized_url(locale(), route($name, $parameters, $absolute))
        );
    }
}

if (!function_exists('localized_url')) {
    /**
     * Returns a URL adapted to the given locale.
     *
     * @param string $locale
     * @param string $url
     *
     * @return string
     */
    function localized_url($locale, $url = null)
    {
        $url = $url ?? request()->fullUrl();
        $url = aestheticcart_strip_install_base_from_url($url);

        $localized = LaravelLocalization::getLocalizedURL($locale, $url, [], true);

        return aestheticcart_normalize_install_url(
            aestheticcart_apply_install_base_url($localized)
        );
    }
}

if (!function_exists('non_localized_url')) {
    /**
     * It returns a URL without locale.
     *
     * @param string $url
     *
     * @return string
     */
    function non_localized_url($url = null)
    {
        $url = $url ?? request()->fullUrl();
        $url = aestheticcart_strip_install_base_from_url($url);

        return aestheticcart_apply_install_base_url(LaravelLocalization::getNonLocalizedURL($url));
    }
}

if (!function_exists('is_multilingual')) {
    /**
     * Determine if the app has multi-language.
     *
     * @return bool
     */
    function is_multilingual()
    {
        return count(supported_locales()) > 1;
    }
}

if (!function_exists('is_multi_currency')) {
    /**
     * Determine if the app has multi currency.
     *
     * @return bool
     */
    function is_multi_currency()
    {
        return count(supported_currencies()) > 1;
    }
}

if (!function_exists('is_module_enabled')) {
    /**
     * Determine if the given module is enabled.
     *
     * @param string $module
     *
     * @return bool
     */
    function is_module_enabled($module)
    {
        return array_key_exists(strtolower($module), app('modules')->allEnabled());
    }
}

if (!function_exists('is_core_module')) {
    /**
     * Determine if the given module is core module.
     *
     * @param string $module
     *
     * @return bool
     */
    function is_core_module($module)
    {
        return in_array(strtolower($module), config('fleetcart.modules.core.config.core_modules'));
    }
}

if (!function_exists('slugify')) {
    /**
     * Generate a URL friendly "slug" from a given string
     *
     * @param string $value
     */
    function slugify($value)
    {
        $slug = preg_replace('/[\s<>[\]{}|\\^%&\$,\/:;=?@#\'\"]/', '-', mb_strtolower($value));

        // Remove duplicate separators.
        $slug = preg_replace('/-+/', '-', $slug);

        // Trim special characters from the beginning and end of the slug.
        return trim($slug, '!"#$%&\'()*+,-./:;<=>?@[]^_`{|}~');
    }
}

if (!function_exists('v')) {
    /**
     * Version a relative asset using the time its contents last changed.
     *
     * @param string $value
     *
     * @return string
     */
    function v($path)
    {
        if (config('app.env') === 'local') {
            $version = uniqid();
        } else {
            $version = aestheticcart_version();
        }

        return "{$path}?v=" . $version;
    }
}

if (!function_exists('aestheticcart_version')) {
    /**
     * Get the fleetcart version.
     *
     * @return string
     */
    function aestheticcart_version()
    {
        return AestheticCart::VERSION;
    }
}

if (!function_exists('old_json')) {
    /**
     * Retrieve and json encode an old input item.
     *
     * @param string $array
     * @param mixed $default
     * @param mixed $options
     *
     * @return string
     */
    function old_json($key, $default = [], $options = null)
    {
        $old = array_reset_index(old($key, []));

        return json_encode($old ?: $default, $options);
    }
}

if (!function_exists('array_reset_index')) {
    /**
     * Reset numeric index of an array recursively.
     *
     * @param array $array
     *
     * @return array|Collection
     *
     * @see https://stackoverflow.com/a/12399408/5736257
     */
    function array_reset_index($array)
    {
        $array = $array instanceof Collection
            ? $array->toArray()
            : $array;

        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $array[$key] = array_reset_index($val);
            }
        }

        if (isset($key) && is_numeric($key)) {
            return array_values($array);
        }

        return $array;
    }
}

if (!function_exists('html_attrs')) {
    /**
     * Convert array to html attributes.
     *
     * @param array $attributes
     *
     * @return string
     */
    function html_attrs(array $attributes)
    {
        $string = '';

        foreach ($attributes as $name => $value) {
            $string .= " {$name}={$value}";
        }

        return $string;
    }
}

if (!function_exists('currency_symbol_fallback')) {
    function currency_symbol_fallback(string $currencyCode): string
    {
        return match ($currencyCode) {
            'MYR' => 'RM',
            'USD' => '$',
            'SGD' => 'S$',
            'EUR' => '€',
            'GBP' => '£',
            default => $currencyCode,
        };
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Convert currency code to currency symbol.
     *
     * @param string $currencyCode
     *
     * @return string
     */

    function currency_symbol(string $currencyCode): string
    {
        if (! class_exists(\NumberFormatter::class)) {
            return currency_symbol_fallback($currencyCode);
        }

        try {
            return Currencies::getSymbol($currencyCode);
        } catch (\Throwable) {
            return currency_symbol_fallback($currencyCode);
        }
    }
}


if (!function_exists('take_percent')) {
    /**
     * Take the given percent of a given amount.
     *
     * @param int|float $percent
     * @param $amount
     *
     * @return float
     */

    function take_percent($percent, $amount)
    {
        return ($percent / 100) * $amount;
    }
}

if (!function_exists('calculate_percentage')) {
    /**
     * Calculate percentage of an amount in another amount.
     *
     * @param $amount1
     * @param $amount2
     *
     * @return float
     */

    function calculate_percentage($amount1, $amount2)
    {
        return ($amount1 / $amount2) * 100;
    }
}

if (!function_exists('number_format_kmbt')) {
    /**
     * Format a number to K/M/B/T.
     *
     * @param $number
     * @param $precision
     *
     * @return string
     */

    function number_format_kmbt($number, $precision)
    {
        if ($number < 1000) {
            $amount = number_format($number);
        } else if ($number < 1000000) {
            $amount = number_format($number / 1000, $precision) . 'K';
        } else if ($number < 1000000000) {
            $amount = number_format($number / 1000000, $precision) . 'M';
        } else if ($number < 1000000000000) {
            $amount = number_format($number / 1000000000, $precision) . 'B';
        } else {
            $amount = number_format($number / 1000000000000, $precision) . 'T';
        }

        return $amount;
    }
}

if (!function_exists('generate_color_shade')) {
    function generate_color_shade($color,$amount)
    {
        $newColor = new \TinyColor\Color($color);

        $newColor->r = max(round($newColor->r * (1 - $amount)), 0);
        $newColor->g = max(round($newColor->g * (1 - $amount)), 0);
        $newColor->b = max(round($newColor->b * (1 - $amount)), 0);

        return $newColor->toHexString();
    }
}

if (!function_exists('fix_storage_urls_in_content')) {
    /**
     * Rewrite root-absolute /storage/... URLs to include APP_URL (subdirectory installs).
     *
     * @param string|null $content
     *
     * @return string|null
     */
    function fix_storage_urls_in_content($content)
    {
        if (!is_string($content) || $content === '') {
            return $content;
        }

        $base = rtrim(config('app.url'), '/');

        return str_replace(
            ['"/storage/', "'/storage/"],
            ['"' . $base . '/storage/', "'" . $base . '/storage/'],
            $content
        );
    }
}

if (!function_exists('cdn_url')) {
    /**
     * Rewrite an application URL to use the configured CDN origin.
     */
    function cdn_url(?string $url, string $type = 'media'): ?string
    {
        if ($url === null || $url === '') {
            return $url;
        }

        $cdn = $type === 'asset'
            ? config('performance.cdn.asset_url')
            : (config('performance.cdn.media_url') ?: config('performance.cdn.asset_url'));

        if (! $cdn) {
            return $url;
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        $cdn = rtrim((string) $cdn, '/');

        if ($appUrl !== '' && str_starts_with($url, $appUrl)) {
            return $cdn . substr($url, strlen($appUrl));
        }

        return $url;
    }
}

if (!function_exists('clean_html')) {
    /**
     * Sanitize HTML for safe storefront output (strips scripts, event handlers, etc.).
     */
    function clean_html(?string $html): string
    {
        $html = fix_storage_urls_in_content($html);

        if (! is_string($html) || $html === '') {
            return '';
        }

        return HtmlSanitizer::clean($html);
    }
}

