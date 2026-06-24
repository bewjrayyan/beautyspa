<?php

namespace Modules\SpecialGift\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GiftVoucherImageService
{
    private const JPEG_QUALITY = 90;

    private ?string $lastErrorKey = null;


    public function lastErrorKey(): ?string
    {
        return $this->lastErrorKey;
    }


    public function generateFromPath(string $backgroundPath, string $recipientName, string $orderNumber): ?string
    {
        $this->lastErrorKey = null;

        if (! extension_loaded('gd')) {
            $this->lastErrorKey = 'image_failed_gd';
            Log::error('SpecialGift: GD extension is not available');

            return null;
        }

        if (! is_readable($backgroundPath)) {
            $this->lastErrorKey = 'image_failed_background';
            Log::error('SpecialGift: voucher background file is not readable', [
                'path' => $backgroundPath,
            ]);

            return null;
        }

        $imageInfo = @getimagesize($backgroundPath);

        if (! $imageInfo) {
            $this->lastErrorKey = 'image_failed_background';
            Log::error('SpecialGift: voucher background is not a valid image', [
                'path' => $backgroundPath,
            ]);

            return null;
        }

        $image = $this->createImageResource($backgroundPath, $imageInfo['mime']);

        if (! $image) {
            $this->lastErrorKey = 'image_failed_background';
            Log::error('SpecialGift: GD could not load voucher background', [
                'path' => $backgroundPath,
                'mime' => $imageInfo['mime'] ?? null,
            ]);

            return null;
        }

        $this->addTextOverlay($image, $recipientName, $orderNumber);

        $relativePath = 'media/gift-vouchers/voucher-'.date('Ymd-His').'-'.uniqid('', true).'.jpg';
        $disk = Storage::disk('public');

        if (! $this->ensureOutputDirectory($disk, 'media/gift-vouchers')) {
            imagedestroy($image);
            $this->lastErrorKey = 'image_failed_storage';

            return null;
        }

        ob_start();
        $encoded = @imagejpeg($image, null, self::JPEG_QUALITY);
        $binary = ob_get_clean();
        imagedestroy($image);

        if (! $encoded || ! is_string($binary) || $binary === '') {
            $this->lastErrorKey = 'image_failed';
            Log::error('SpecialGift: failed to encode voucher JPEG', [
                'error' => error_get_last(),
            ]);

            return null;
        }

        if (! $disk->put($relativePath, $binary)) {
            $this->lastErrorKey = 'image_failed_storage';
            Log::error('SpecialGift: failed to store generated voucher image', [
                'path' => $relativePath,
                'directory' => $disk->path('media/gift-vouchers'),
            ]);

            return null;
        }

        return cdn_url($disk->url($relativePath), 'media');
    }


    private function ensureOutputDirectory($disk, string $directory): bool
    {
        $absoluteDirectory = $disk->path($directory);

        if (! $disk->exists($directory)) {
            $disk->makeDirectory($directory, 0777, true);
        }

        if (! is_dir($absoluteDirectory)) {
            $this->lastErrorKey = 'image_failed_storage';
            Log::error('SpecialGift: voucher output directory could not be created', [
                'directory' => $absoluteDirectory,
            ]);

            return false;
        }

        if (! is_writable($absoluteDirectory)) {
            @chmod($absoluteDirectory, 0777);
        }

        if (is_writable($absoluteDirectory)) {
            return true;
        }

        Log::error('SpecialGift: voucher output directory is not writable', [
            'directory' => $absoluteDirectory,
            'owner' => function_exists('posix_getpwuid') && fileowner($absoluteDirectory)
                ? (posix_getpwuid(fileowner($absoluteDirectory))['name'] ?? fileowner($absoluteDirectory))
                : fileowner($absoluteDirectory),
            'permissions' => substr(sprintf('%o', fileperms($absoluteDirectory)), -4),
        ]);

        return false;
    }


    private function createImageResource(string $path, string $mime)
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }


    private function addTextOverlay($image, string $recipientName, string $orderNumber): void
    {
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $shadowColor = imagecolorallocate($image, 0, 0, 0);
        $fontSize = 48;
        $fontPath = module_path('SpecialGift', 'Resources/assets/fonts/DejaVuSans-Bold.ttf');

        if (! is_file($fontPath)) {
            $fontPath = $this->resolveSystemFont();
        }

        $useTtf = $fontPath && function_exists('imagettftext');
        $recipientText = $recipientName;
        $orderText = 'Order No: '.$orderNumber;

        if ($useTtf) {
            $recBox = imagettfbbox($fontSize, 0, $fontPath, $recipientText);
            $recWidth = $recBox[4] - $recBox[0];
            $orderBox = imagettfbbox($fontSize, 0, $fontPath, $orderText);
            $orderWidth = $orderBox[4] - $orderBox[0];
            $x = (int) (($imageWidth - $recWidth) / 2);
            $xOrder = (int) (($imageWidth - $orderWidth) / 2);
            $y = (int) ($imageHeight * 0.52);

            $this->drawTtfText($image, $fontSize, $x, $y, $fontPath, $recipientText, $textColor, $shadowColor);

            $yOrder = $y + $fontSize + 16;
            $this->drawTtfText($image, $fontSize, $xOrder, $yOrder, $fontPath, $orderText, $textColor, $shadowColor);

            return;
        }

        $builtInSize = 5;
        $recWidth = strlen($recipientText) * imagefontwidth($builtInSize);
        $orderWidth = strlen($orderText) * imagefontwidth($builtInSize);
        $x = (int) (($imageWidth - $recWidth) / 2);
        $xOrder = (int) (($imageWidth - $orderWidth) / 2);
        $y = (int) ($imageHeight * 0.52);

        imagestring($image, $builtInSize, $x + 1, $y + 1, $recipientText, $shadowColor);
        imagestring($image, $builtInSize, $x, $y, $recipientText, $textColor);

        $yOrder = $y + imagefontheight($builtInSize) + 6;
        imagestring($image, $builtInSize, $xOrder + 1, $yOrder + 1, $orderText, $shadowColor);
        imagestring($image, $builtInSize, $xOrder, $yOrder, $orderText, $textColor);
    }


    private function drawTtfText(
        $image,
        int $fontSize,
        int $x,
        int $y,
        string $fontPath,
        string $text,
        int $textColor,
        int $shadowColor,
    ): void {
        for ($ox = -1; $ox <= 1; $ox++) {
            for ($oy = -1; $oy <= 1; $oy++) {
                if ($ox !== 0 || $oy !== 0) {
                    imagettftext($image, $fontSize, 0, $x + $ox, $y + $oy, $shadowColor, $fontPath, $text);
                }
            }
        }

        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontPath, $text);
    }


    private function resolveSystemFont(): ?string
    {
        $candidates = [
            '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
            '/System/Library/Fonts/Supplemental/Arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
