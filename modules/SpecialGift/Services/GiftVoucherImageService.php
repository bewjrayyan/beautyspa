<?php

namespace Modules\SpecialGift\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Entities\File;

class GiftVoucherImageService
{
    private const JPEG_QUALITY = 90;


    public function generate(File $background, string $recipientName, string $orderNumber): ?string
    {
        if (! extension_loaded('gd')) {
            Log::error('SpecialGift: GD extension is not available');

            return null;
        }

        $backgroundPath = $background->realPath();

        if (! $backgroundPath || ! is_readable($backgroundPath)) {
            Log::error('SpecialGift: voucher background file is not readable');

            return null;
        }

        $imageInfo = @getimagesize($backgroundPath);

        if (! $imageInfo) {
            return null;
        }

        $image = $this->createImageResource($backgroundPath, $imageInfo['mime']);

        if (! $image) {
            return null;
        }

        $this->addTextOverlay($image, $recipientName, $orderNumber);

        $relativePath = 'specialgift/generated/voucher-'.date('Ymd-His').'-'.uniqid('', true).'.jpg';
        $disk = Storage::disk('public');

        if (! $disk->exists('specialgift/generated')) {
            $disk->makeDirectory('specialgift/generated');
        }

        $absolutePath = $disk->path($relativePath);
        $saved = @imagejpeg($image, $absolutePath, self::JPEG_QUALITY);
        imagedestroy($image);

        if (! $saved || ! is_file($absolutePath)) {
            return null;
        }

        return cdn_url($disk->url($relativePath), 'media');
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
            $x = (int) (($imageWidth - $recWidth) / 2) + 350;
            $y = (int) min($imageHeight * 0.3, $imageHeight - 100) + 130;

            $this->drawTtfText($image, $fontSize, $x, $y, $fontPath, $recipientText, $textColor, $shadowColor);

            $yOrder = $y + $fontSize + 12;
            $this->drawTtfText($image, $fontSize, $x, $yOrder, $fontPath, $orderText, $textColor, $shadowColor);

            return;
        }

        $builtInSize = 5;
        $recWidth = strlen($recipientText) * imagefontwidth($builtInSize);
        $x = (int) (($imageWidth - $recWidth) / 2) + 350;
        $y = (int) min($imageHeight * 0.3, $imageHeight - 80) + 130;

        imagestring($image, $builtInSize, $x + 1, $y + 1, $recipientText, $shadowColor);
        imagestring($image, $builtInSize, $x, $y, $recipientText, $textColor);

        $yOrder = $y + imagefontheight($builtInSize) + 6;
        imagestring($image, $builtInSize, $x + 1, $yOrder + 1, $orderText, $shadowColor);
        imagestring($image, $builtInSize, $x, $yOrder, $orderText, $textColor);
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
