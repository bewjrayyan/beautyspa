<?php

namespace Modules\User\Support;

/**
 * Portable PHP password hashing framework (phpass) as used by WordPress.
 *
 * @see https://www.openwall.com/phpass/
 */
class WordPressPhpass
{
    private string $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public function check(string $password, string $storedHash): bool
    {
        if (! str_starts_with($storedHash, '$P$') && ! str_starts_with($storedHash, '$H$')) {
            return false;
        }

        $countLog2 = strpos($this->itoa64, $storedHash[3]);

        if ($countLog2 < 7 || $countLog2 > 30) {
            return false;
        }

        $count = 1 << $countLog2;
        $salt = substr($storedHash, 4, 8);

        if (strlen($salt) !== 8) {
            return false;
        }

        $hash = md5($salt . $password, true);

        for ($i = 0; $i < $count; $i++) {
            $hash = md5($hash . $password, true);
        }

        $output = substr($storedHash, 0, 12) . $this->encode64($hash, 16);

        return hash_equals($storedHash, $output);
    }

    private function encode64(string $input, int $count): string
    {
        $output = '';
        $i = 0;

        do {
            $value = ord($input[$i++]);
            $output .= $this->itoa64[$value & 0x3f];

            if ($i < $count) {
                $value |= ord($input[$i]) << 8;
            }

            $output .= $this->itoa64[($value >> 6) & 0x3f];

            if ($i++ >= $count) {
                break;
            }

            if ($i < $count) {
                $value |= ord($input[$i]) << 16;
            }

            $output .= $this->itoa64[($value >> 12) & 0x3f];

            if ($i++ >= $count) {
                break;
            }

            $output .= $this->itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $output;
    }
}
