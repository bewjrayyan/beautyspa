<?php

namespace Modules\Support\Services;

use GdImage;

class IcoEncoder
{
    /**
     * @param  array<int, GdImage>  $images  Square GD images keyed by pixel size.
     */
    public static function encode(array $images): string
    {
        if ($images === []) {
            return '';
        }

        ksort($images);

        $count = count($images);
        $offset = 6 + (16 * $count);
        $data = pack('vvv', 0, 1, $count);
        $bitmapData = '';

        foreach ($images as $size => $image) {
            $bmp = self::encodeBitmap($image, $size, $size);
            $length = strlen($bmp);
            $data .= pack('CCCCvvVV', $size, $size, 0, 0, 1, 32, $length, $offset);
            $offset += $length;
            $bitmapData .= $bmp;
        }

        return $data.$bitmapData;
    }


    private static function encodeBitmap(GdImage $image, int $width, int $height): string
    {
        $xor = self::xorBitmap($image, $width, $height);
        $and = self::andMask($image, $width, $height);

        $header = pack('V', 40)
            .pack('V', $width)
            .pack('V', $height * 2)
            .pack('v', 1)
            .pack('v', 32)
            .pack('V', 0)
            .pack('V', strlen($xor) + strlen($and))
            .pack('V', 0)
            .pack('V', 0)
            .pack('V', 0)
            .pack('V', 0);

        return $header.$xor.$and;
    }


    private static function xorBitmap(GdImage $image, int $width, int $height): string
    {
        $data = '';

        for ($y = $height - 1; $y >= 0; $y--) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                $red = ($rgba >> 16) & 0xFF;
                $green = ($rgba >> 8) & 0xFF;
                $blue = $rgba & 0xFF;

                $data .= chr($blue).chr($green).chr($red).chr(255 - $alpha);
            }
        }

        return $data;
    }


    private static function andMask(GdImage $image, int $width, int $height): string
    {
        $rowBytes = (int) (ceil($width / 32) * 4);
        $data = '';

        for ($y = $height - 1; $y >= 0; $y--) {
            $bits = '';

            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                $bits .= $alpha > 63 ? '1' : '0';
            }

            $bits = str_pad($bits, (int) (ceil($width / 32) * 32), '0', STR_PAD_RIGHT);
            $row = '';

            for ($i = 0; $i < strlen($bits); $i += 8) {
                $row .= chr(bindec(strrev(substr($bits, $i, 8))));
            }

            $data .= str_pad($row, $rowBytes, "\0");
        }

        return $data;
    }
}
