@extends('storefront::public.layout')

@section('title', $product->name)

@section('body_class', 'product-show-page')

@section('breadcrumb')
    @if (!$categoryBreadcrumb)
        <li><a href="{{ storefront_route('products.index') }}">{{ products_listing_title() }}</a></li>
    @endif

    {!! $categoryBreadcrumb !!}

    <li class="active">{{ $product->name }}</li>
@endsection

@section('content')
    <section
        x-data="ProductShow({
            product: {{ $product }},

            @if ($product->variant)
                variant: {{ $product->variant }},
            @endif

            reviewCount: {{ $review->count ?? 0 }},
            avgRating: {{ $review->avg_rating ?? 0 }},
            flashSalePrice: '{{ $flashSalePrice }}',
            reviewerName: {{ \Illuminate\Support\Js::from(trim((auth()->user()?->full_name ?: auth()->user()?->email) ?? '')) }},
            whatsAppShareMessage: {{ \Illuminate\Support\Js::from(setting('storefront_product_share_whatsapp_message', '')) }}
        })"
        class="product-details-wrap"
    >
        @include('storefront::public.products.show.mobile_toolbar')

        <div class="container">
            <div class="product-details-top">
                <div class="d-flex flex-column flex-lg-row flex-lg-nowrap ">
                    @include('storefront::public.products.show.gallery')

                    @include('storefront::public.products.show.details', ['item' => $product->variant ?? $product])

                    @if (setting('storefront_features_section_enabled') || setting('storefront_product_consultation_enabled', true))
                        @include('storefront::public.products.show.right_sidebar')
                    @endif
                </div>
            </div>

            <div class="product-details-bottom flex-column-reverse flex-lg-row">
                @include('storefront::public.products.show.left_sidebar')

                <div class="product-details-bottom-inner">
                    <div class="product-details-tab clearfix">
                        <div class="product-details-tab-overflow">
                            <ul class="nav nav-tabs tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a href="#description" data-bs-toggle="tab" class="nav-link active">
                                        {{ trans('storefront::product.description') }}
                                    </a>
                                </li>

                                @if ($product->hasAnyAttribute())
                                    <li class="nav-item" role="presentation">
                                        <a href="#specification" data-bs-toggle="tab" class="nav-link">
                                            {{ trans('storefront::product.specification') }}
                                        </a>
                                    </li>
                                @endif

                                @if (setting('reviews_enabled'))
                                    <li class="nav-item" role="presentation">
                                        <a
                                            href="#reviews"
                                            data-bs-toggle="tab"
                                            class="nav-link"
                                            x-text="trans('storefront::product.reviews', { count: totalReviews })"
                                        >
                                            {{ trans('storefront::product.reviews', ['count' => $product->reviews->count() ]) }}
                                        </a>
                                    </li>
                                @endif
                            </ul>

                            <hr>
                        </div>

                        <div class="tab-content">
                            @include('storefront::public.products.show.tab_description')
                            @include('storefront::public.products.show.tab_specification')
                            @include('storefront::public.products.show.tab_reviews')
                        </div>
                    </div>

                    @if ($relatedProducts->isNotEmpty())
                        @include('storefront::public.products.show.related_products')
                    @endif
                </div>
            </div>
        </div>

        <aside class="product-mobile-dock d-lg-none" aria-label="{{ trans('storefront::product.add_to_cart') }}">
            <div class="product-mobile-dock__top">
                <div class="product-mobile-dock__price">
                    <span class="product-mobile-dock__label">{{ trans('storefront::product.total') }}</span>

                    <div class="product-mobile-dock__amounts">
                        <template x-if="hasSpecialPrice">
                            <span class="product-mobile-dock__special" x-text="formatCurrency(specialPrice)"></span>
                        </template>

                        <span
                            class="product-mobile-dock__amount"
                            :class="{ 'product-mobile-dock__amount--strike': hasSpecialPrice }"
                            x-text="formatCurrency(regularPrice)"
                        ></span>
                    </div>
                </div>

                <div class="product-mobile-dock__qty" aria-label="{{ trans('storefront::product.quantity') }}">
                    <button
                        type="button"
                        class="product-mobile-dock__qty-btn"
                        aria-label="{{ trans('storefront::product.quantity') }}"
                        :disabled="isQtyDecreaseDisabled"
                        @click="updateQuantity(cartItemForm.qty - 1)"
                    >
                        <i class="las la-minus"></i>
                    </button>

                    <input
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        class="product-mobile-dock__qty-input"
                        :value="cartItemForm.qty"
                        autocomplete="off"
                        min="1"
                        :max="maxQuantity"
                        :disabled="isAddToCartDisabled"
                        @focus="$event.target.select()"
                        @input="updateQuantity(Number($event.target.value))"
                    >

                    <button
                        type="button"
                        class="product-mobile-dock__qty-btn"
                        aria-label="{{ trans('storefront::product.quantity') }}"
                        :disabled="isQtyIncreaseDisabled"
                        @click="updateQuantity(cartItemForm.qty + 1)"
                    >
                        <i class="las la-plus"></i>
                    </button>
                </div>
            </div>

            <button
                type="button"
                class="btn btn-primary product-mobile-dock__cta"
                :class="{ 'btn-loading': addingToCart }"
                :disabled="isAddToCartDisabled"
                @click="$refs.productCartForm?.requestSubmit()"
            >
                <i class="las la-shopping-cart"></i>
                <span x-text="isActiveItem ? '{{ trans('storefront::product.add_to_cart') }}' : '{{ trans('storefront::product.unavailable') }}'"></span>
            </button>
        </aside>

        @include('storefront::public.products.show.variant_sheet')
    </section>
@endsection

@push('globals')
    {!! $productSchemaMarkup->toScript() !!}

    <script>
        AestheticCart.langs['storefront::product.left_in_stock'] = '{{ trans('storefront::product.left_in_stock') }}';
        AestheticCart.langs['storefront::product.reviews'] = '{{ trans("storefront::product.reviews") }}';
        AestheticCart.langs['storefront::product.review_submitted'] = '{{ trans("storefront::product.review_submitted") }}';
    </script>

    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/products/show/main.scss',
        'modules/Storefront/Resources/assets/public/js/pages/products/show/main.js',
        'modules/Storefront/Resources/assets/public/js/vendors/flatpickr.js'
    ])
@endpush
