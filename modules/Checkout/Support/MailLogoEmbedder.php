<?php

namespace Modules\Checkout\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Entities\File;

class MailLogoEmbedder
{
    public function embed(Mailable $mailable): ?string
    {
        $file = $this->resolveLogoFile();

        if (! $file?->id) {
            return null;
        }

        $disk = Storage::disk($file->disk);
        $rawPath = $file->getRawOriginal('path');

        if (! is_string($rawPath) || $rawPath === '' || ! $disk->exists($rawPath)) {
            return null;
        }

        $extension = pathinfo($rawPath, PATHINFO_EXTENSION) ?: 'png';
        $mime = $disk->mimeType($rawPath) ?: 'image/png';

        return $mailable->embedData(
            $disk->get($rawPath),
            'store-logo.' . $extension,
            ['mime' => $mime],
        );
    }


    private function resolveLogoFile(): ?File
    {
        foreach (['storefront_mail_logo', 'storefront_header_logo'] as $settingKey) {
            $fileId = setting($settingKey);

            if ($fileId && ($file = File::find($fileId))) {
                return $file;
            }
        }

        return null;
    }
}
