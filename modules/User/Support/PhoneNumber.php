<?php

namespace Modules\User\Support;

class PhoneNumber
{
    public static function normalize(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = '60' . substr($digits, 1);
        }

        // Repair legacy values saved as 618xxxxxxxxx instead of 6018xxxxxxxxx.
        if (str_starts_with($digits, '61') && ! str_starts_with($digits, '60')) {
            $digits = '60' . substr($digits, 1);
        }

        return $digits;
    }


    /**
     * @return array<int, string>
     */
    public static function variants(string $normalized): array
    {
        if ($normalized === '') {
            return [];
        }

        $variants = [$normalized];

        if (str_starts_with($normalized, '60')) {
            $local = '0' . substr($normalized, 2);
            $variants[] = $local;
            $variants[] = '+' . $normalized;
        }

        return array_values(array_unique($variants));
    }
}
