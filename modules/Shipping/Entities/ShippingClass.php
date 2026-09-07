<?php

namespace Modules\Shipping\Entities;

use Modules\Support\Money;
use Modules\Admin\Ui\AdminTable;
use Modules\Support\Eloquent\Model;
use Modules\Product\Entities\Product;
use Modules\Support\Cache\TaggedCache;
use Modules\Support\Eloquent\Translatable;
use Modules\Shipping\Admin\ShippingClassTable;

class ShippingClass extends Model
{
    use Translatable;

    public $translatedAttributes = ['name'];

    protected $with = ['translations'];

    protected $fillable = ['cost', 'is_active'];

    protected $casts = [
        'cost' => 'float',
        'is_active' => 'boolean',
    ];


    protected static function booting()
    {
        parent::booting();

        static::addActiveGlobalScope();
    }


    public static function list()
    {
        return TaggedCache::rememberForever('shipping_classes', md5('shipping_classes.list:' . locale()), function () {
            return static::all()
                ->sortBy('name')
                ->mapWithKeys(function (self $shippingClass) {
                    $cost = Money::inDefaultCurrency($shippingClass->cost)->format();

                    return [$shippingClass->id => "{$shippingClass->name} ({$cost})"];
                });
        });
    }


    public function products()
    {
        return $this->hasMany(Product::class);
    }


    public function table(): AdminTable
    {
        return new ShippingClassTable($this->newQuery()->withoutGlobalScope('active'));
    }
}
