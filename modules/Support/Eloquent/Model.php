<?php

namespace Modules\Support\Eloquent;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Modules\Core\Support\WritableStorageBootstrap;

abstract class Model extends Eloquent
{
    public static function queryWithoutEagerRelations()
    {
        return (new static)->newQueryWithoutEagerRelations();
    }


    /**
     * Register a new active global scope on the model.
     *
     * @return void
     */
    public static function addActiveGlobalScope()
    {
        static::addGlobalScope('active', function ($query) {
            $query->where('is_active', true);
        });
    }


    /**
     * Perform any actions required before the model boots.
     *
     * @return void
     */
    protected static function booting()
    {
        static::saved(function ($entity) {
            $entity->clearEntityTaggedCache();
        });

        static::deleted(function ($entity) {
            $entity->clearEntityTaggedCache();
        });
    }


    public function newQueryWithoutEagerRelations()
    {
        return $this->registerGlobalScopes(
            $this->newModelQuery()->withCount($this->withCount)
        );
    }


    public function clearEntityTaggedCache(): void
    {
        try {
            Cache::tags($this->getTable())->flush();
        } catch (\Throwable $exception) {
            Log::warning('Tagged cache flush failed; applying fallback invalidation.', [
                'table' => $this->getTable(),
                'message' => $exception->getMessage(),
            ]);

            $this->fallbackTaggedCacheInvalidation($this->getTable());
        }
    }


    protected function fallbackTaggedCacheInvalidation(string $table): void
    {
        if ($table === 'settings' && function_exists('supported_locale_keys')) {
            foreach (supported_locale_keys() as $locale) {
                Cache::forget(md5('settings.all:' . $locale));
            }
        }

        if (WritableStorageBootstrap::isLocalEnvironment()) {
            WritableStorageBootstrap::unlinkTaggedCacheFile($table);
        }
    }
}
