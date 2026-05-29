<?php

namespace Modules\Page\Entities;

use Modules\Support\Eloquent\TranslationModel;

class PageTranslation extends TranslationModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'body'];


    public function getBodyAttribute($body)
    {
        return fix_storage_urls_in_content($body);
    }
}
