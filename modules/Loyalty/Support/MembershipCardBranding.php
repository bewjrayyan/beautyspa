<?php

namespace Modules\Loyalty\Support;

use Illuminate\Support\Facades\Cache;
use Modules\Media\Entities\File;

class MembershipCardBranding
{
    /**
     * Admin small logo from Settings → Logo, with full logo fallback.
     */
    public static function watermarkUrl(): ?string
    {
        foreach (['admin_small_logo', 'admin_logo'] as $settingKey) {
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
     * Same cache bucket as admin layout composer (stores File model, not path string).
     */
    private static function resolveFile(int|string $fileId): ?File
    {
        $file = Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
            return File::find($fileId);
        });

        return $file?->id ? $file : null;
    }
}
