<section x-data="ProductTabsOne({{ $productTabsOne->toJson() }})" class="landscape-tab-products-wrap">
    <div class="container">
        <div class="landscape-left-tab-products-inner">
            <div class="tab-products-header">
                <div class="tab-products-header-overflow">
                    <ul class="tabs">
                        @foreach($productTabsOne as $key => $tab)
                            <li
                                class="tab-item"
                                :class="classes({{ $key }})"
                                @click="changeTab({{ $key }})"
                                title="{{ $tab['title'] }}"
                            >
                                @include('storefront::public.partials.product_tab_label', ['label' => $tab['title']])
                            </li>
                        @endforeach
                    </ul>
    
                    <hr>
                </div>

                <a href="{{ storefront_route('products.index') }}" class="tab-products-view-all">
                    {{ trans('storefront::storefront.view_all') }}
                </a>
            </div>
    
            <div class="tab-content">
                <div class="landscape-left-tab-products products-slider swiper"> 
                    <div class="swiper-wrapper">
                        @foreach (range(0, 7) as $skeleton)
                            <div class="swiper-slide swiper-slide-skeleton">
                                @include('storefront::public.partials.product_card_skeleton')
                            </div>
                        @endforeach
                        
                        <template
                            x-for="product in products"
                            :key="`${activeTab}-${product.id}`"
                        >
                            <div class="swiper-slide">
                                @include('storefront::public.partials.product_card')
                            </div>
                        </template>
                    </div>
                </div>

                @include('storefront::public.partials.product_slider_controls')
            </div>
        </div>
    </div>
</section>
