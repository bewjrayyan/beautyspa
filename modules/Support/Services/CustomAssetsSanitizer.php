<?php

namespace Modules\Support\Services;

/**
 * Restricts admin "custom header/footer assets" to reduce stored-XSS risk.
 *
 * - Strips event-handler attributes (onclick, onerror, …)
 * - Blocks javascript: / data:text/html URLs
 * - Allows <script src="…"> only from configured HTTPS host allowlist
 * - Drops inline <script> bodies unless explicitly allowed in config
 * - Keeps <style>, <link rel="stylesheet">, <meta>, <noscript>, and markup tags
 */
class CustomAssetsSanitizer
{
    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $html = self::stripEventHandlers($html);
        $html = self::neutralizeDangerousUrls($html);
        $html = self::filterScriptTags($html);

        return trim($html);
    }

    private static function stripEventHandlers(string $html): string
    {
        return (string) preg_replace(
            '/\son[a-z]+\s*=\s*(?:([\'"])(?:\\\\.|(?!\1).)*\1|[^\s>]+)/i',
            '',
            $html
        );
    }

    private static function neutralizeDangerousUrls(string $html): string
    {
        $html = (string) preg_replace(
            '/\s(href|src|xlink:href)\s*=\s*([\'"])\s*javascript:[^\'"]*\2/i',
            ' $1=$2#$2',
            $html
        );

        return (string) preg_replace(
            '/\s(href|src)\s*=\s*([\'"])\s*data:text\/html[^\'"]*\2/i',
            ' $1=$2#$2',
            $html
        );
    }

    private static function filterScriptTags(string $html): string
    {
        $allowInline = (bool) config('security.custom_assets.allow_inline_scripts', false);
        $allowlist = array_values(array_filter(array_map(
            'strtolower',
            (array) config('security.custom_assets.script_host_allowlist', [])
        )));

        return (string) preg_replace_callback(
            '/<script\b([^>]*)>(.*?)<\/script>/is',
            static function (array $matches) use ($allowInline, $allowlist): string {
                $attrs = $matches[1] ?? '';
                $body = $matches[2] ?? '';

                if (preg_match('/\bsrc\s*=\s*([\'"])([^\'"]+)\1/i', $attrs, $srcMatch)) {
                    $src = trim($srcMatch[2]);

                    if (self::isAllowedScriptSrc($src, $allowlist)) {
                        // Keep external scripts; drop inline body if any was present.
                        return '<script' . self::safeScriptAttributes($attrs) . '></script>';
                    }

                    return '<!-- blocked custom script src -->';
                }

                if ($allowInline && trim($body) !== '') {
                    return '<script' . self::safeScriptAttributes($attrs) . '>' . $body . '</script>';
                }

                return '<!-- blocked inline custom script -->';
            },
            $html
        );
    }

    private static function safeScriptAttributes(string $attrs): string
    {
        // Drop inline event handlers already stripped globally; also drop javascript: handlers in attrs.
        $attrs = self::stripEventHandlers($attrs);

        return $attrs === '' ? '' : ' ' . trim($attrs);
    }

    private static function isAllowedScriptSrc(string $src, array $allowlist): bool
    {
        if ($src === '' || str_starts_with(strtolower($src), 'javascript:')) {
            return false;
        }

        // Protocol-relative or absolute https only.
        if (str_starts_with($src, '//')) {
            $src = 'https:' . $src;
        }

        $parts = parse_url($src);

        if (! is_array($parts) || empty($parts['host'])) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));

        if ($scheme !== 'https') {
            return false;
        }

        $host = strtolower($parts['host']);

        foreach ($allowlist as $allowed) {
            $allowed = ltrim($allowed, '*.');

            if ($host === $allowed || str_ends_with($host, '.' . $allowed)) {
                return true;
            }
        }

        return false;
    }
}
