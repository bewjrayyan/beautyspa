<?php

namespace Modules\Storefront\Support;

use Modules\Media\Entities\File;

class MobileHomePromoSettings
{
    public static function isEnabled(): bool
    {
        return (bool) setting('storefront_mobile_home_promo_enabled');
    }


    /**
     * @return array<string, mixed>|null
     */
    public static function forHomePage(): ?array
    {
        if (! self::isEnabled()) {
            return null;
        }

        $type = setting('storefront_mobile_home_promo_media_type', 'image');
        $callToActionUrl = trim((string) setting('storefront_mobile_home_promo_call_to_action_url', ''));
        $openInNewWindow = (bool) setting('storefront_mobile_home_promo_open_in_new_window');

        if ($type === 'video') {
            $video = self::resolveFile(setting('storefront_mobile_home_promo_video_file_id'));

            if (! $video) {
                return null;
            }

            $poster = self::resolveFile(setting('storefront_mobile_home_promo_video_poster_file_id'));

            return [
                'type' => 'video',
                'url' => $video,
                'poster' => $poster,
                'call_to_action_url' => $callToActionUrl,
                'open_in_new_window' => $openInNewWindow,
            ];
        }

        $image = self::resolveFile(setting('storefront_mobile_home_promo_image_file_id'));

        if (! $image) {
            return null;
        }

        return [
            'type' => 'image',
            'url' => $image,
            'poster' => null,
            'call_to_action_url' => $callToActionUrl,
            'open_in_new_window' => $openInNewWindow,
        ];
    }


    private static function resolveFile($fileId): ?string
    {
        if (empty($fileId)) {
            return null;
        }

        $file = File::find($fileId);

        if (! $file || ! $file->exists || ! $file->path) {
            return null;
        }

        return $file->path;
    }
}
