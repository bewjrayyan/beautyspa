@php
    $productBackUrl = $product->categories->first()?->url() ?? storefront_route('products.index');

    $flattenCategories = function ($categories) use (&$flattenCategories) {
        $items = collect();

        foreach ($categories as $category) {
            $items->push($category);

            if ($category->items->isNotEmpty()) {
                $items = $items->merge($flattenCategories($category->items));
            }
        }

        return $items;
    };

    $mobileBreadcrumbCategories = $flattenCategories($product->categories->nest());
@endphp

<div class="product-mobile-toolbar d-lg-none">
    <div class="product-mobile-toolbar__start">
        <button
            type="button"
            class="product-mobile-toolbar__btn"
            aria-label="{{ trans('storefront::product.back') }}"
            @click="goBack('{{ $productBackUrl }}')"
        >
            <i class="las la-arrow-left"></i>
        </button>

        <nav class="product-mobile-toolbar__breadcrumb" aria-label="{{ trans('storefront::layouts.breadcrumb') }}">
            <ol>
                @if ($mobileBreadcrumbCategories->isEmpty())
                    <li>
                        <a href="{{ storefront_route('products.index') }}">{{ products_listing_title() }}</a>
                    </li>
                @else
                    @foreach ($mobileBreadcrumbCategories as $category)
                        <li>
                            <a href="{{ $category->url() }}">{{ $category->name }}</a>
                        </li>
                    @endforeach
                @endif

                <li class="is-current" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>
    </div>

    <div class="product-mobile-toolbar__end">
        <button
            type="button"
            class="product-mobile-toolbar__btn"
            :class="{ 'product-mobile-toolbar__btn--active': inWishlist }"
            aria-label="{{ trans('storefront::product.wishlist') }}"
            @click="syncWishlist"
        >
            <i class="las la-heart"></i>
        </button>
    </div>
</div>
