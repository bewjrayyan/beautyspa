<section
    x-data="
        LandscapeProducts({
            url: '{{ $url }}',
            watchState: '{{ $watchState }}'
        })
    "
    class="landscape-products-wrap"
    x-ref="landscapeProductsWrap"
>
    <div class="container">
        <div class="landscape-products-inner">
            <div class="products-header">
                <h3 class="section-title">{{ $title }}</h3>
            </div>
        
            <div class="landscape-products products-slider swiper">
                <div class="swiper-wrapper">
                    @foreach (range(0, 7) as $skeleton)
                        <div class="swiper-slide swiper-slide-skeleton">
                            @include('storefront::public.partials.product_card_skeleton')
                        </div>
                    @endforeach

                    <template x-for="(product, index) in products" :key="index">
                        <div class="swiper-slide">
                            @include('storefront::public.partials.product_card')
                        </div>
                    </template>
                </div>
            </div>

            @include('storefront::public.partials.product_slider_controls')
        </div>
    </div>
</section>
