<?php

namespace Modules\Account\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPngSignature implements ValidationRule
{
    private const MAX_BINARY_BYTES = 1_000_000;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $binary = $this->decode((string) $value);

        if ($binary === null || ! $this->hasValidImage($binary) || ! $this->hasVisibleInk($binary)) {
            $fail('account::consultation.validation.signature_invalid')->translate();
        }
    }

    private function decode(string $value): ?string
    {
        if (! str_starts_with($value, 'data:image/png;base64,')) {
            return null;
        }

        $binary = base64_decode(substr($value, 22), true);

        if ($binary === false || strlen($binary) > self::MAX_BINARY_BYTES) {
            return null;
        }

        return $binary;
    }

    private function hasValidImage(string $binary): bool
    {
        $details = @getimagesizefromstring($binary);

        return is_array($details)
            && ($details['mime'] ?? null) === 'image/png'
            && $details[0] >= 200
            && $details[0] <= 1600
            && $details[1] >= 80
            && $details[1] <= 600;
    }

    private function hasVisibleInk(string $binary): bool
    {
        $image = @imagecreatefromstring($binary);

        if ($image === false) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $inkPixels = 0;

        for ($y = 0; $y < $height; $y += 4) {
            for ($x = 0; $x < $width; $x += 4) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                $red = ($rgba >> 16) & 0xFF;
                $green = ($rgba >> 8) & 0xFF;
                $blue = $rgba & 0xFF;

                if ($alpha < 120 && ($red + $green + $blue) < 660) {
                    $inkPixels++;

                    if ($inkPixels >= 8) {
                        if (PHP_VERSION_ID < 80500) {
                            imagedestroy($image);
                        }

                        return true;
                    }
                }
            }
        }

        if (PHP_VERSION_ID < 80500) {
            imagedestroy($image);
        }

        return false;
    }
}
