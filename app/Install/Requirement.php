<?php

namespace AestheticCart\Install;

class Requirement
{
    private const extensions = [
        'intl' => 'Intl',
        'pdo' => 'PDO',
        'json' => 'JSON',
        'ctype' => 'Ctype',
        'xml' => 'XML',
        'tokenizer' => 'Tokenizer',
        'mbstring' => 'Mbstring',
        'openssl' => 'OpenSSL',
        'gd' => 'GD',
        'exif' => 'exif',
    ];


    public function satisfied(): bool
    {
        return collect($this->php())
            ->merge($this->extensions())
            ->merge($this->packages())
            ->every(fn ($item) => $item);
    }


    public function php(): array
    {
        return [
            'PHP >= 8.2.0' => version_compare(phpversion(), '8.2.0', '>='),
        ];
    }


    public function extensions(): array
    {
        $extensions = [];

        foreach (self::extensions as $extension => $name) {
            $extensions[$name.' PHP Extension'] = extension_loaded($extension);
        }

        return $extensions;
    }

    public function packages(): array
    {
        return [
            'Composer dependencies (vendor/)' => is_file(base_path('vendor/autoload.php')),
            'Frontend assets (public/build/)' => is_file(public_path('build/manifest.json')),
        ];
    }
}
