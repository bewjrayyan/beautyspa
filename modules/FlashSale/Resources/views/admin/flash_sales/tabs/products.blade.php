@php
    $campaignProducts = $flashSale->relationLoaded('products')
        ? $flashSale->products->loadMissing('options')
        : $flashSale->products()->with('options')->get();

    $productsForJs = $campaignProducts->map(function ($product) {
        $catalogMoney = ($product->hasSpecialPrice() ? $product->getSpecialPrice() : $product->price)
            ->convertToCurrentCurrency();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'is_virtual' => $product->isVirtualTreatment(),
            'has_variants' => $product->hasAnyVariants(),
            'has_options' => $product->options->isNotEmpty(),
            'catalog_price_formatted' => $catalogMoney->format(),
            'pivot' => [
                'end_date' => $product->pivot->end_date,
                'price' => ['amount' => round((float) $product->pivot->price->amount(), 2)],
                'qty' => (int) $product->pivot->qty,
                'sold' => (int) $product->pivot->sold,
            ],
        ];
    })->values();

    $treatmentCount = $campaignProducts->count();
    $totalSold = $campaignProducts->sum(fn ($product) => (int) ($product->pivot->sold ?? 0));
    $unlimitedCount = $campaignProducts->filter(
        fn ($product) => $product->isVirtualTreatment() && (int) ($product->pivot->qty ?? 0) <= 0
    )->count();
    $hasItems = $treatmentCount > 0 || ! empty(old('products'));
@endphp

<div class="flash-sale-products {{ $hasItems ? 'flash-sale-products--has-items' : '' }}" id="flash-sale-products">
    <div class="flash-sale-products__toolbar">
        <div class="flash-sale-products__toolbar-text">
            <h3 class="flash-sale-products__title">{{ trans('flashsale::flash_sales.products_tab.title') }}</h3>
            <p class="flash-sale-products__lead">{{ trans('flashsale::flash_sales.products_tab.lead') }}</p>
        </div>

        <button type="button" class="add-product btn btn-primary flash-sale-products__add-btn">
            <i class="fa fa-plus" aria-hidden="true"></i>
            {{ trans('flashsale::flash_sales.form.add_product') }}
        </button>
    </div>

    <div class="flash-sale-products__stats">
        <div class="flash-sale-products__stat">
            <span class="flash-sale-products__stat-icon flash-sale-products__stat-icon--treatments">
                <i class="fa fa-heart" aria-hidden="true"></i>
            </span>
            <div>
                <span class="flash-sale-products__stat-label">{{ trans('flashsale::flash_sales.products_tab.stats.treatments') }}</span>
                <strong class="flash-sale-products__stat-value" id="flash-sale-stat-treatments">{{ $treatmentCount }}</strong>
            </div>
        </div>
        <div class="flash-sale-products__stat">
            <span class="flash-sale-products__stat-icon flash-sale-products__stat-icon--sold">
                <i class="fa fa-shopping-bag" aria-hidden="true"></i>
            </span>
            <div>
                <span class="flash-sale-products__stat-label">{{ trans('flashsale::flash_sales.products_tab.stats.sold') }}</span>
                <strong class="flash-sale-products__stat-value" id="flash-sale-stat-sold">{{ $totalSold }}</strong>
            </div>
        </div>
        <div class="flash-sale-products__stat">
            <span class="flash-sale-products__stat-icon flash-sale-products__stat-icon--unlimited">
                <i class="fa fa-unlock-alt" aria-hidden="true"></i>
            </span>
            <div>
                <span class="flash-sale-products__stat-label">{{ trans('flashsale::flash_sales.products_tab.stats.unlimited') }}</span>
                <strong class="flash-sale-products__stat-value" id="flash-sale-stat-unlimited">{{ $unlimitedCount }}</strong>
            </div>
        </div>
    </div>

    <div class="flash-sale-products__tips">
        <div class="flash-sale-products__tip flash-sale-products__tip--primary">
            <i class="fa fa-info-circle" aria-hidden="true"></i>
            <span>{{ trans('flashsale::flash_sales.form.virtual_treatments_intro') }}</span>
        </div>
        <div class="flash-sale-products__tip flash-sale-products__tip--slots">
            <i class="fa fa-ticket" aria-hidden="true"></i>
            <span>{{ trans('flashsale::flash_sales.form.qty_unlimited_virtual') }}</span>
        </div>
    </div>

    <div class="flash-sale-products__empty" id="flash-sale-empty">
        <i class="fa fa-heart" aria-hidden="true"></i>
        <h4>{{ trans('flashsale::flash_sales.products_tab.empty_title') }}</h4>
        <p>{{ trans('flashsale::flash_sales.products_tab.empty_text') }}</p>
        <button type="button" class="add-product btn btn-primary flash-sale-products__add-btn">
            <i class="fa fa-plus" aria-hidden="true"></i>
            {{ trans('flashsale::flash_sales.form.add_product') }}
        </button>
    </div>

    <div class="flash-sale-products__list" id="products-wrapper"></div>
</div>

@include('admin::partials.selectize_remote')
@include('flashsale::admin.flash_sales.templates.product')

@push('styles')
    @vite(['modules/FlashSale/Resources/assets/admin/sass/main.scss'])
@endpush

@push('globals')
    <script>
        AestheticCart.data['flash_sale.products'] = {!! old_json('products', $productsForJs) !!};
        AestheticCart.errors['flash_sale.products'] = @json($errors->get('products.*'), JSON_FORCE_OBJECT);
        AestheticCart.data['flash_sale.product_show_url'] = @json(url('admin/flash-sales/products'));
        AestheticCart.langs['product::products.table.virtual_treatment'] = @json(trans('product::products.table.virtual_treatment'));
        AestheticCart.langs['flashsale::flash_sales.products_tab.item_heading'] = @json(trans('flashsale::flash_sales.products_tab.item_heading'));
        AestheticCart.langs['flashsale::flash_sales.products_tab.sold_label'] = @json(trans('flashsale::flash_sales.products_tab.sold_label'));
    </script>
@endpush
