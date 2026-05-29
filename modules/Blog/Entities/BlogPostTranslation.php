<?php

namespace Modules\Blog\Entities;

use Modules\Support\Eloquent\TranslationModel;

class BlogPostTranslation extends TranslationModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'description'];


    public function getDescriptionAttribute($description)
    {
        return fix_storage_urls_in_content($description);
    }
}
