<?php

namespace Modules\Support\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    private static ?HTMLPurifier $purifier = null;

    public static function clean(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        return self::instance()->purify($html);
    }

    private static function instance(): HTMLPurifier
    {
        if (self::$purifier !== null) {
            return self::$purifier;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^https://(www\.)?(youtube\.com|youtube-nocookie\.com|player\.vimeo\.com)%');
        // Only list allowed schemes (all values must be true; omit schemes to block them).
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
        ]);
        $config->set('Attr.AllowedFrameTargets', ['_blank', '_self']);
        $config->set('CSS.Trusted', true);

        self::$purifier = new HTMLPurifier($config);

        return self::$purifier;
    }
}
