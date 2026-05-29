<?php

namespace Modules\Meta\Eloquent;

use Modules\Meta\Entities\MetaData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasMetaData
{
    /**
     * The "booting" method of the trait.
     *
     * @return void
     */
    public static function bootHasMetaData()
    {
        static::saved(function ($entity) {
            $entity->saveMetaData(request('meta', []));
        });
    }


    /**
     * Save metadata for the entity.
     *
     * @param array $data
     *
     * @return Model
     */
    public function saveMetaData($data = [])
    {
        if ($data === [] || $data === null) {
            return;
        }

        if (array_key_exists('og_image_id', $data)) {
            $data['og_image_id'] = $data['og_image_id'] === '' || $data['og_image_id'] === null
                ? null
                : (int) $data['og_image_id'];
        }

        if (empty($data['meta_robots'])) {
            $data['meta_robots'] = 'index, follow';
        }

        $meta = $this->meta()->firstOrNew([]);

        $meta->translateOrNew(locale())->fill($data);
        $meta->save();
    }


    /**
     * Get the meta for the entity.
     *
     * @return MorphToMany
     */
    public function meta()
    {
        return $this->morphOne(MetaData::class, 'entity')->withDefault();
    }
}
