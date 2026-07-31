<div class="cosmetik-showcase">
    <div class="cosmetik-showcase-content">
        <span class="cosmetik-showcase-eyebrow">
            {{ trans('storefront::products.cosmetik.eyebrow') }}
        </span>

        <h1>{{ trans('storefront::products.cosmetik.title') }}</h1>

        <p>{{ trans('storefront::products.cosmetik.description') }}</p>

        @if (($subCategories ?? collect())->isNotEmpty())
            <div class="cosmetik-category-links" aria-label="{{ trans('storefront::products.cosmetik.shop_by_category') }}">
                @foreach ($subCategories as $subCategory)
                    @if ($subCategory->products_count > 0)
                        <a href="{{ $subCategory->url() }}">
                            <span>{{ $subCategory->name }}</span>
                            <i class="las la-arrow-right" aria-hidden="true"></i>
                        </a>
                    @else
                        <span class="is-coming-soon">
                            <span>{{ $subCategory->name }}</span>
                            <small>{{ trans('storefront::products.cosmetik.coming_soon') }}</small>
                        </span>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <div class="cosmetik-showcase-art" aria-hidden="true">
        <span class="cosmetik-orb cosmetik-orb-one"></span>
        <span class="cosmetik-orb cosmetik-orb-two"></span>
        <i class="las la-spa"></i>
    </div>
</div>
