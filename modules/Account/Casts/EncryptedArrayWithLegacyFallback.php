<?php

namespace Modules\Account\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use JsonException;

/**
 * Encrypts new array values while continuing to read legacy JSON rows.
 *
 * The fallback can be removed after all historical rows have been re-encrypted.
 */
class EncryptedArrayWithLegacyFallback implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        return self::decrypt($value);
    }

    public static function decrypt(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $value = Crypt::decryptString($value);
        } catch (DecryptException) {
            // Existing records were stored as plain JSON before encryption was introduced.
        }

        try {
            $decoded = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return self::encrypt($value);
    }

    public static function encrypt(array $value): string
    {
        return Crypt::encryptString(json_encode(
            $value,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ));
    }
}
