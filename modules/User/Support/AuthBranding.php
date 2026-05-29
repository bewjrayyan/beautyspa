<?php

namespace Modules\User\Support;

use Illuminate\Support\Facades\Cache;
use Modules\Media\Entities\File;

class AuthBranding
{
    /**
     * Public URL for the auth screen logo, or null when no file is configured.
     */
    public static function logoUrl(bool $preferAdminLogo = true): ?string
    {
        $keys = $preferAdminLogo
            ? ['admin_logo', 'admin_small_logo', 'storefront_header_logo']
            : ['storefront_header_logo'];

        foreach ($keys as $settingKey) {
            $fileId = setting($settingKey);

            if (! $fileId) {
                continue;
            }

            $file = static::resolveFile($fileId);

            if ($file?->path) {
                return $file->path;
            }
        }

        return null;
    }


    /**
     * Where the logo should link on admin auth pages (stay on login, not storefront).
     */
    public static function adminLogoHref(): string
    {
        return route('admin.login');
    }


    /**
     * Storefront URL for “back to shop” controls on admin auth.
     */
    public static function storefrontHref(): string
    {
        return route('home');
    }


    private static function resolveFile(int|string $fileId): ?File
    {
        $file = Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
            return File::find($fileId);
        });

        return $file?->id ? $file : null;
    }
}
