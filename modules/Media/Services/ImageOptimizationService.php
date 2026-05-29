<?php

namespace Modules\Media\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
class ImageOptimizationService
{
    /**
     * @return array{path: string, extension: string, mime: string, size: int, responsive_paths: array<int, string>}
     */
    public function processUploadedFile(UploadedFile $file, string $storedPath, string $diskName): array
    {
        $disk = Storage::disk($diskName);
        $absolutePath = $disk->path($storedPath);
        $mime = $file->getClientMimeType();
        $extension = $file->guessClientExtension() ?? pathinfo($storedPath, PATHINFO_EXTENSION);
        $responsivePaths = [];

        if (! $this->isOptimizableRaster($mime, $absolutePath)) {
            return [
                'path' => $storedPath,
                'extension' => $extension,
                'mime' => $mime,
                'size' => (int) $file->getSize(),
                'responsive_paths' => $responsivePaths,
            ];
        }

        $widths = config('performance.image.widths', [480, 960]);
        $quality = (int) config('performance.image.webp_quality', 82);

        foreach ($widths as $width) {
            $variantPath = $this->buildVariantPath($storedPath, (int) $width);

            if ($this->writeWebpVariant($absolutePath, $disk->path($variantPath), (int) $width, $quality)) {
                $responsivePaths[(int) $width] = $variantPath;
            }
        }

        $mainPath = $storedPath;
        $mainMime = $mime;
        $mainExtension = $extension;

        if (config('performance.image.webp_enabled', true) && function_exists('imagewebp')) {
            $webpPath = $this->buildVariantPath($storedPath, 'full');

            if ($this->writeWebpVariant($absolutePath, $disk->path($webpPath), 9999, $quality)) {
                if (config('performance.image.replace_original_with_webp', true)) {
                    $disk->delete($storedPath);
                    $mainPath = $webpPath;
                    $mainMime = 'image/webp';
                    $mainExtension = 'webp';
                }
            }
        }

        return [
            'path' => $mainPath,
            'extension' => $mainExtension,
            'mime' => $mainMime,
            'size' => (int) ($disk->exists($mainPath) ? $disk->size($mainPath) : $file->getSize()),
            'responsive_paths' => $responsivePaths,
        ];
    }

    private function isOptimizableRaster(string $mime, string $absolutePath): bool
    {
        if (! is_file($absolutePath)) {
            return false;
        }

        return in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    private function buildVariantPath(string $storedPath, int|string $suffix): string
    {
        $directory = pathinfo($storedPath, PATHINFO_DIRNAME);
        $filename = pathinfo($storedPath, PATHINFO_FILENAME);
        $variantName = $filename . '-' . $suffix . '.webp';

        return $directory === '.' ? $variantName : $directory . '/' . $variantName;
    }

    private function writeWebpVariant(string $source, string $destination, int $maxWidth, int $quality): bool
    {
        if (! function_exists('imagewebp')) {
            return false;
        }

        $image = $this->loadImage($source);

        if (! $image) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($maxWidth < 9999 && $width > $maxWidth) {
            $newHeight = (int) round($height * ($maxWidth / $width));
            $resized = imagecreatetruecolor($maxWidth, $newHeight);

            if ($resized) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
            }
        }

        $directory = dirname($destination);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $success = imagewebp($image, $destination, $quality);
        imagedestroy($image);

        return $success;
    }

    /**
     * @return \GdImage|resource|false
     */
    private function loadImage(string $path)
    {
        $info = @getimagesize($path);

        if (! $info) {
            return false;
        }

        return match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }
}
