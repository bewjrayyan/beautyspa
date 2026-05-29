<?php

namespace Modules\Meta\Entities;

use Modules\Media\Entities\File;
use Modules\Support\Eloquent\TranslationModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaDataTranslation extends TranslationModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'meta_title',
        'meta_description',
        'og_image_id',
        'meta_robots',
    ];


    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(File::class, 'og_image_id');
    }
}
