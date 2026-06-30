<?php

namespace Modules\User\Sentinel;

use Cartalyst\Sentinel\Hashing\HasherInterface;
use Modules\User\Support\WordPressPhpass;

class WordPressCompatibleHasher implements HasherInterface
{
    private WordPressPhpass $phpass;

    public function __construct()
    {
        $this->phpass = new WordPressPhpass();
    }

    public function hash(string $value): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function check(string $value, string $hashedValue): bool
    {
        $hash = $this->normalizeStoredHash($hashedValue);

        if (password_verify($value, $hash)) {
            return true;
        }

        return $this->phpass->check($value, $hashedValue);
    }

    public function normalizeStoredHash(string $hash): string
    {
        if (str_starts_with($hash, '$wp$')) {
            return '$' . substr($hash, 4);
        }

        return $hash;
    }
}
