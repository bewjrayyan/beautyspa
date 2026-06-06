@if (($latestProducts ?? collect())->isNotEmpty())
    <aside class="page-sidebar page-sidebar--latest-products">
        <div class="vertical-products vertical-products--static">
            <div class="vertical-products-header">
                <h5 class="section-title">{{ trans('storefront::products.latest_products') }}</h5>
            </div>

            <div class="vertical-products-list">
                @foreach ($latestProducts as $latestProduct)
                    <div x-data="ProductCard({{ json_encode($latestProduct) }})" class="vertical-product-card">
                        <a :href="productUrl" class="product-image">
                            <img
                                :src="baseImage"
                                :class="{ 'image-placeholder': !hasBaseImage }"
                                :alt="productName"
                                loading="lazy"
                            />

                            <div class="product-image-layer"></div>
                        </a>

                        <div class="product-info">
                            <a :href="productUrl" class="product-name">
                                <span x-text="productName"></span>
                            </a>

                            @include('storefront::public.partials.product_rating')

                            <div class="product-price" x-html="productPrice"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </aside>
@endif
