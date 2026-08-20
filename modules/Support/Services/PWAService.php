<?php

namespace Modules\Support\Services;

use Modules\Media\Entities\File;
use Illuminate\Support\Facades\File as FileSystem;
use RuntimeException;

class PWAService
{
    public function generateIcons(File $iconFile): void
    {
        $sourcePath = $iconFile->realPath();

        if (! is_string($sourcePath) || ! is_readable($sourcePath)) {
            throw new RuntimeException('PWA icon file is missing or unreadable.');
        }

        $outputPath = public_path('pwa/icons');

        FileSystem::isDirectory($outputPath) or FileSystem::makeDirectory($outputPath, 0755, true, true);
        FileSystem::cleanDirectory($outputPath);

        $imageData = @getimagesize($sourcePath);

        if ($imageData === false) {
            throw new RuntimeException('PWA icon must be a valid PNG, JPEG, or WebP image.');
        }

        $origWidth = $imageData[0];
        $origHeight = $imageData[1];
        $mediaType = $imageData['mime'] ?? null;

        if (! is_string($mediaType)) {
            throw new RuntimeException('PWA icon must be a valid PNG, JPEG, or WebP image.');
        }

        $orig = match ($mediaType) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };

        if ($orig === false) {
            throw new RuntimeException('PWA icon must be a valid PNG, JPEG, or WebP image.');
        }

        $ratios = [48, 72, 96, 128, 144, 152, 192, 384, 512];

        foreach ($ratios as $ratio) {
            $new = imagecreatetruecolor($ratio, $ratio);

            if ($mediaType === 'image/png' || $mediaType === 'image/webp') {
                imagealphablending($new, false);
                imagesavealpha($new, true);
                $transparent = imagecolorallocatealpha($new, 0, 0, 0, 127);
                imagefilledrectangle($new, 0, 0, $ratio, $ratio, $transparent);
            }

            imagecopyresampled(
                $new,
                $orig,
                0,
                0,
                0,
                0,
                $ratio,
                $ratio,
                $origWidth,
                $origHeight
            );

            imagepng($new, $outputPath.'/'.$ratio.'x'.$ratio.'.png');
            imagedestroy($new);
        }

        imagedestroy($orig);
    }


    public function updatePWAVersionInServiceWorkerJs(): void
    {
        $path = public_path('serviceworker.js');
        $serviceWorkerJs = @file_get_contents($path);

        if ($serviceWorkerJs === false) {
            return;
        }

        if (strpos($serviceWorkerJs, 'const pwaVersion')) {
            $serviceWorkerJs = preg_replace('/^const pwaVersion.*\n$/m', 'const pwaVersion = '.time().";\n", $serviceWorkerJs);
        } else {
            $serviceWorkerJs .= "\n".'const pwaVersion = '.time().';';
        }

        @file_put_contents($path, $serviceWorkerJs);
    }
}
