<?php

namespace Modules\Shipping\Entities;

use Modules\Support\Eloquent\TranslationModel;

class ShippingClassTranslation extends TranslationModel
{
    protected $fillable = ['name'];
}
