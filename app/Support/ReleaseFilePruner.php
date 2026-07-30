<?php

namespace AestheticCart\Support;

use RuntimeException;

class ReleaseFilePruner
{
    public const MANIFEST_PATH = 'app/release-deletions.php';

    /**
     * Paths which must always survive an in-place application update.
     *
     * @var list<string>
     */
    private const PROTECTED_PATHS = [
        '.env',
        '.git',
        '.github',
        '.htaccess',
        'node_modules',
        'public/storage',
        'public/uploads',
        'storage',
        'uploads',
        'vendor',
    ];

    /**
     * Move files retired by the current release into private quarantine.
     *
     * @return array{release: string, quarantine: string|null, paths: list<string>}
     */
    public function apply(
        string $applicationRoot,
        string $quarantineRoot,
        ?string $extractedReleaseRoot = null,
    ): array {
        $applicationRoot = $this->existingDirectory($applicationRoot);
        $manifest = $this->loadManifest($applicationRoot);
        $release = $manifest['release'];
        $paths = array_values(array_unique($manifest['paths']));
        $quarantine = null;
        $moved = [];

        foreach ($paths as $path) {
            $this->assertSafePath($path);

            if ($extractedReleaseRoot !== null && $this->pathExists($extractedReleaseRoot.'/'.$path)) {
                throw new RuntimeException("Release deletion manifest references an active release path: {$path}");
            }

            $source = $applicationRoot.'/'.$path;

            if (! $this->pathExists($source)) {
                continue;
            }

            if ($quarantine === null) {
                $quarantine = rtrim($quarantineRoot, '/').'/'.$this->quarantineName($release);
            }

            $destination = $quarantine.'/'.$path;
            $this->createDirectory(dirname($destination));

            if (! @rename($source, $destination)) {
                throw new RuntimeException("Could not quarantine retired release path: {$path}");
            }

            $moved[] = $path;
        }

        return [
            'release' => $release,
            'quarantine' => $quarantine,
            'paths' => $moved,
        ];
    }

    /**
     * @return array{release: string, paths: list<string>}
     */
    private function loadManifest(string $applicationRoot): array
    {
        $manifestPath = $applicationRoot.'/'.self::MANIFEST_PATH;

        if (! is_file($manifestPath)) {
            return ['release' => 'unknown', 'paths' => []];
        }

        $manifest = require $manifestPath;

        if (
            ! is_array($manifest)
            || ($manifest['version'] ?? null) !== 1
            || ! is_string($manifest['release'] ?? null)
            || ! is_array($manifest['paths'] ?? null)
        ) {
            throw new RuntimeException('The release deletion manifest is invalid.');
        }

        foreach ($manifest['paths'] as $path) {
            if (! is_string($path)) {
                throw new RuntimeException('The release deletion manifest contains a non-string path.');
            }
        }

        return [
            'release' => $manifest['release'],
            'paths' => array_values($manifest['paths']),
        ];
    }

    private function assertSafePath(string $path): void
    {
        if (
            $path === ''
            || $path !== trim($path)
            || str_starts_with($path, '/')
            || str_contains($path, '\\')
            || str_contains($path, "\0")
        ) {
            throw new RuntimeException("Unsafe release deletion path: {$path}");
        }

        $segments = explode('/', $path);

        if (in_array('.', $segments, true) || in_array('..', $segments, true)) {
            throw new RuntimeException("Unsafe release deletion path: {$path}");
        }

        foreach (self::PROTECTED_PATHS as $protectedPath) {
            if ($path === $protectedPath || str_starts_with($path, $protectedPath.'/')) {
                throw new RuntimeException("Protected release path cannot be deleted: {$path}");
            }
        }
    }

    private function existingDirectory(string $path): string
    {
        $resolved = realpath($path);

        if ($resolved === false || ! is_dir($resolved)) {
            throw new RuntimeException("Application root does not exist: {$path}");
        }

        return rtrim($resolved, '/');
    }

    private function createDirectory(string $path): void
    {
        if (! is_dir($path) && ! @mkdir($path, 0775, true) && ! is_dir($path)) {
            throw new RuntimeException("Could not create release quarantine directory: {$path}");
        }
    }

    private function pathExists(string $path): bool
    {
        return file_exists($path) || is_link($path);
    }

    private function quarantineName(string $release): string
    {
        $safeRelease = preg_replace('/[^A-Za-z0-9._-]/', '-', $release) ?: 'unknown';

        return $safeRelease.'-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4));
    }
}
