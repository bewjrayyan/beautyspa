<?php

namespace Modules\Support\Services;

use GdImage;
use Illuminate\Support\Facades\File as FileSystem;
use Illuminate\Support\Facades\Log;
use Modules\Media\Entities\File;

class FaviconService
{
    private const SIZES = [16, 32, 48];

    private const CACHE_DIR = 'favicon';


    public function getIco(File $file): ?string
    {
        if ($this->isIcoFile($file)) {
            $path = $file->realPath();

            return ($path && is_readable($path)) ? (string) file_get_contents($path) : null;
        }

        $cachePath = $this->cachePath($file);

        if ($cachePath && is_readable($cachePath)) {
            return (string) file_get_contents($cachePath);
        }

        $ico = $this->generateIco($file);

        if ($ico === null) {
            return null;
        }

        if ($cachePath) {
            FileSystem::ensureDirectoryExists(dirname($cachePath));
            @file_put_contents($cachePath, $ico);
        }

        return $ico;
    }


    public function clearCache(?File $file = null): void
    {
        $directory = storage_path('app/'.self::CACHE_DIR);

        if (! FileSystem::isDirectory($directory)) {
            return;
        }

        if ($file === null) {
            FileSystem::cleanDirectory($directory);

            return;
        }

        $cachePath = $this->cachePath($file);

        if ($cachePath && is_file($cachePath)) {
            @unlink($cachePath);
        }
    }


    private function generateIco(File $file): ?string
    {
        if (! extension_loaded('gd')) {
            Log::warning('FaviconService: GD extension is not available');

            return null;
        }

        $sourcePath = $file->realPath();

        if (! $sourcePath || ! is_readable($sourcePath)) {
            return null;
        }

        $imageInfo = @getimagesize($sourcePath);

        if (! $imageInfo) {
            return null;
        }

        $source = $this->createImageResource($sourcePath, $imageInfo['mime']);

        if (! $source instanceof GdImage) {
            return null;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $images = [];

        foreach (self::SIZES as $size) {
            $resized = imagecreatetruecolor($size, $size);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $size, $size, $transparent);
            imagecopyresampled(
                $resized,
                $source,
                0,
                0,
                0,
                0,
                $size,
                $size,
                $sourceWidth,
                $sourceHeight
            );
            $images[$size] = $resized;
        }

        imagedestroy($source);

        $ico = IcoEncoder::encode($images);

        foreach ($images as $image) {
            imagedestroy($image);
        }

        return $ico !== '' ? $ico : null;
    }


    private function createImageResource(string $path, string $mime): GdImage|false
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }


    private function isIcoFile(File $file): bool
    {
        $mime = strtolower((string) $file->mime);

        return $mime === 'image/x-icon'
            || $mime === 'image/vnd.microsoft.icon'
            || str_ends_with(strtolower((string) $file->filename), '.ico');
    }


    private function cachePath(File $file): ?string
    {
        $sourcePath = $file->realPath();

        if (! $sourcePath || ! is_file($sourcePath)) {
            return null;
        }

        $signature = md5($file->id.'|'.$sourcePath.'|'.filemtime($sourcePath).'|'.filesize($sourcePath));

        return storage_path('app/'.self::CACHE_DIR.'/'.$signature.'.ico');
    }
}
