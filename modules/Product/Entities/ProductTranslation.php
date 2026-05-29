<?php

namespace Modules\Product\Entities;

use Modules\Support\Eloquent\TranslationModel;

class ProductTranslation extends TranslationModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'short_description'];


    public function getDescriptionAttribute($description)
    {
        return fix_storage_urls_in_content($description);
    }
}
