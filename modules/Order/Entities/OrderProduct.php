<?php

namespace Modules\Order\Entities;

use Modules\Support\Money;
use Modules\Support\Eloquent\Model;
use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Collection;
use Modules\Product\Entities\ProductVariant;


class OrderProduct extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['product', 'product_variant' ,'options', 'variations'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    public function url()
    {
        return route('products.show', ['slug' => $this->product->slug]);
    }


    public function hasAnyOption()
    {
        return $this->options->isNotEmpty();
    }


    public function hasAnyVariation()
    {
        return $this->variations->isNotEmpty();
    }


    /**
     * Determine if order product has been deleted.
     *
     * @return bool
     */
    public function trashed()
    {
        return $this->product->trashed();
    }


    /**
     * Store order product's variations.
     *
     * @param Collection $variations
     *
     * @return void
     */
    public function storeVariations($variations)
    {
        $variations->each(function ($variation) {
            $orderProductVariation = $this->variations()->create([
                'order_product_id' => $this->id,
                'variation_id' => $variation->id,
                'type' => $variation->type,
                'value' => $variation->values->first()->label,
            ]);

            $orderProductVariation->storeValues($variation->values);
        });
    }


    public function variations()
    {
        return $this->hasMany(OrderProductVariation::class);
    }


    /**
     * Store order product's options.
     *
     * @param Collection $options
     *
     * @return void
     */
    public function storeOptions($options)
    {
        $options->each(function ($option) {
            $orderProductOption = $this->options()->create([
                'order_product_id' => $this->id,
                'option_id' => $option->id,
                'value' => $option->isFieldType() ? $option->values->first()->label : null,
            ]);

            $orderProductOption->storeValues($this->product, $option->values);
        });
    }


    public function options()
    {
        return $this->hasMany(OrderProductOption::class);
    }


    public function product()
    {
        return $this->belongsTo(Product::class)
            ->withoutGlobalScope('active')
            ->withTrashed();
    }

    public function product_variant()
    {
        return $this->belongsTo(ProductVariant::class)
            ->withoutGlobalScope('active')
            ->withTrashed();
    }


    /**
     * Get the order product's name.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->product->name;
    }


    /**
     * Get the order product's slug.
     *
     * @return string
     */
    public function getSlugAttribute()
    {
        return $this->product->slug;
    }


    public function getUnitPriceAttribute($unitPrice)
    {
        return Money::inDefaultCurrency($unitPrice);
    }


    public function getLineTotalAttribute($total)
    {
        return Money::inDefaultCurrency($total);
    }


    /**
     * Get the order product's SKU.
     *
     * @return string
     */
    public function getSkuAttribute()
    {
        return $this->product_variant ? $this->product_variant->sku : $this->product->sku;
    }


    public function optionPriceTotal(): float
    {
        $total = 0.0;

        foreach ($this->options as $option) {
            foreach ($option->values as $value) {
                $total += (float) ($value->pivot->price ?? 0);
            }
        }

        return $total;
    }


    public function hasPricedOptions(): bool
    {
        return $this->optionPriceTotal() > 0.009;
    }


    public function baseUnitPrice(): Money
    {
        return Money::inDefaultCurrency(
            max(0, $this->unit_price->amount() - $this->optionPriceTotal())
        );
    }


    /**
     * @return \Illuminate\Support\Collection<int, array{name: string, value: string, price: float}>
     */
    public function nameWithSelections(): string
    {
        $name = $this->name;
        $parts = [];

        if ($this->hasAnyVariation()) {
            foreach ($this->variations as $variation) {
                $label = $variation->values->first()?->label ?? $variation->value;

                if (filled($label)) {
                    $parts[] = $variation->name . ': ' . $label;
                }
            }
        }

        if ($this->hasAnyOption()) {
            foreach ($this->options as $option) {
                if ($option->isFieldType()) {
                    if (filled($option->value)) {
                        $parts[] = $option->name . ': ' . $option->value;
                    }
                } else {
                    $labels = $option->values->pluck('label')->filter()->implode(', ');

                    if ($labels !== '') {
                        $parts[] = $option->name . ': ' . $labels;
                    }
                }
            }
        }

        if ($parts !== []) {
            $name .= ' (' . implode('; ', $parts) . ')';
        }

        return $name;
    }


    public function pricedOptionLines()
    {
        return $this->options->flatMap(function ($option) {
            if ($option->isFieldType()) {
                return collect();
            }

            return $option->values->map(function ($value) use ($option) {
                return [
                    'name' => $option->name,
                    'value' => $value->label,
                    'price' => (float) ($value->pivot->price ?? 0),
                ];
            })->filter(fn (array $line) => $line['price'] > 0.009);
        })->values();
    }
}
