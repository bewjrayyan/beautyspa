<?php

namespace Modules\Core\Providers;

use AlternativeLaravelCache\Provider\AlternativeCacheStoresServiceProvider;
use Illuminate\Support\Arr;
use Modules\Core\Filesystem\XamppPermissiveLocalFilesystemAdapter;
use Modules\Core\Support\WritableStorageBootstrap;

/**
 * Use permissive Flysystem permissions on local/XAMPP so Apache and CLI can share cache.
 */
class XamppCompatibleCacheServiceProvider extends AlternativeCacheStoresServiceProvider
{
    protected function getNormalizedPermissions(array $cacheConfig): array
    {
        $permissions = parent::getNormalizedPermissions($cacheConfig);

        if (! WritableStorageBootstrap::isLocalEnvironment()) {
            return $permissions;
        }

        return [
            'file' => [
                'public' => 0666,
                'private' => 0666,
            ],
            'dir' => [
                'public' => 0777,
                'private' => 0777,
            ],
        ];
    }

    protected function addFileCacheDriver($cacheManager, bool $hasLocks): void
    {
        if (WritableStorageBootstrap::isLocalEnvironment()) {
            $cacheConfig = $this->app['config']->get('cache.stores.file', []);
            Arr::set($cacheConfig, 'path', WritableStorageBootstrap::fileCachePath());
            Arr::set($cacheConfig, 'permissions', [
                'file' => ['public' => 0666, 'private' => 0666],
                'dir' => ['public' => 0777, 'private' => 0777],
            ]);
            $this->app['config']->set('cache.stores.file', $cacheConfig);
            WritableStorageBootstrap::apply();
        }

        parent::addFileCacheDriver($cacheManager, $hasLocks);
    }

    public function makeFileCacheAdapter(array $cacheConfig)
    {
        if (! WritableStorageBootstrap::isLocalEnvironment()) {
            return parent::makeFileCacheAdapter($cacheConfig);
        }

        switch (strtolower($cacheConfig['driver'])) {
            case $this->fileDriverName:
            case $this->hierarchialFileDriverName:
                $permissions = $this->getNormalizedPermissions($cacheConfig);

                if (class_exists('League\Flysystem\Adapter\Local')) {
                    return new \League\Flysystem\Adapter\Local(
                        $cacheConfig['path'],
                        LOCK_EX,
                        \League\Flysystem\Adapter\Local::DISALLOW_LINKS,
                        $permissions
                    );
                }

                return new XamppPermissiveLocalFilesystemAdapter(
                    $cacheConfig['path'],
                    \League\Flysystem\UnixVisibility\PortableVisibilityConverter::fromArray(
                        $permissions,
                        \League\Flysystem\Visibility::PUBLIC
                    ),
                );
            default:
                return parent::makeFileCacheAdapter($cacheConfig);
        }
    }
}
