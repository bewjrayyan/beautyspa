<div class="product-details-info position-relative flex-grow-1"> 
    <div class="details-info-top">
        <div class="product-mobile-meta d-lg-none">
            @if ($typeLabel = product_type_label($product))
                <span class="product-mobile-meta__chip product-mobile-meta__chip--type">{{ $typeLabel }}</span>
            @else
                <template x-cloak x-if="isInStock">
                    <span class="product-mobile-meta__chip product-mobile-meta__chip--stock">
                        <template x-if="doesManageStock">
                            <span x-text="trans('storefront::product.left_in_stock', { count: item.qty })"></span>
                        </template>
                        <template x-if="!doesManageStock">
                            <span>{{ trans('storefront::product.in_stock') }}</span>
                        </template>
                    </span>
                </template>

                <template x-if="!isInStock">
                    <span class="product-mobile-meta__chip product-mobile-meta__chip--out">{{ trans('storefront::product.out_of_stock') }}</span>
                </template>
            @endif

            <span class="product-mobile-meta__chip">
                {{ trans('storefront::product.people_viewed_treatment', ['count' => $product->viewed]) }}
            </span>
        </div>

        <h1 class="product-name" x-ref="productTitle">{{ $product->name }}</h1>

        <div class="product-view-count d-none d-lg-flex">
            <span class="product-view-count-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M15.58 12C15.58 13.98 13.98 15.58 12 15.58C10.02 15.58 8.42004 13.98 8.42004 12C8.42004 10.02 10.02 8.42004 12 8.42004C13.98 8.42004 15.58 10.02 15.58 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 20.27C15.53 20.27 18.82 18.19 21.11 14.59C22.01 13.18 22.01 10.81 21.11 9.39997C18.82 5.79997 15.53 3.71997 12 3.71997C8.46997 3.71997 5.17997 5.79997 2.88997 9.39997C1.98997 10.81 1.98997 13.18 2.88997 14.59C5.17997 18.19 8.46997 20.27 12 20.27Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>

            <span class="product-view-count-text">
                {{ trans('storefront::product.people_viewed_treatment', ['count' => $product->viewed]) }}
            </span>
        </div>

        @if ($typeLabel = product_type_label($product))
            <div class="product-type-label d-none d-lg-block">{{ $typeLabel }}</div>
        @else
        <template x-cloak x-if="isInStock">
            <div class="d-none d-lg-block">
                <template x-if="doesManageStock">
                    <div
                        class="availability in-stock"
                        x-text="trans('storefront::product.left_in_stock', { count: item.qty })"
                    >
                    </div>
                </template>
                
                <template x-if="!doesManageStock">
                    <div class="availability in-stock">
                        {{ trans('storefront::product.in_stock') }}
                    </div>
                </template>
            </div>
        </template>
        
        <template x-if="!isInStock">
            <div class="availability out-of-stock d-none d-lg-block">
                {{ trans('storefront::product.out_of_stock') }}
            </div>
        </template>
        @endif

        <div class="brief-description">
            {!! clean_html($product->short_description) !!}
        </div>

        <div class="details-info-top-actions">
            <button
                class="btn btn-wishlist"
                :class="{ 'added': inWishlist }"
                @click="syncWishlist"
            >
                <template x-if="inWishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M16.44 3.1001C14.63 3.1001 13.01 3.9801 12 5.3301C10.99 3.9801 9.37 3.1001 7.56 3.1001C4.49 3.1001 2 5.6001 2 8.6901C2 9.8801 2.19 10.9801 2.52 12.0001C4.1 17.0001 8.97 19.9901 11.38 20.8101C11.72 20.9301 12.28 20.9301 12.62 20.8101C15.03 19.9901 19.9 17.0001 21.48 12.0001C21.81 10.9801 22 9.8801 22 8.6901C22 5.6001 19.51 3.1001 16.44 3.1001Z" fill="#292D32"/>
                    </svg>
                </template>
                
                <template x-if="!inWishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M12.62 20.81C12.28 20.93 11.72 20.93 11.38 20.81C8.48 19.82 2 15.69 2 8.68998C2 5.59998 4.49 3.09998 7.56 3.09998C9.38 3.09998 10.99 3.97998 12 5.33998C13.01 3.97998 14.63 3.09998 16.44 3.09998C19.51 3.09998 22 5.59998 22 8.68998C22 15.69 15.52 19.82 12.62 20.81Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </template>

                {{ trans('storefront::product.wishlist') }}
            </button>

            <button
                class="btn btn-compare"
                :class="{ 'added': inCompareList }"
                @click="syncCompareList"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M3.58008 5.15991H17.4201C19.0801 5.15991 20.4201 6.49991 20.4201 8.15991V11.4799" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M6.74008 2L3.58008 5.15997L6.74008 8.32001" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M20.4201 18.84H6.58008C4.92008 18.84 3.58008 17.5 3.58008 15.84V12.52" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M17.26 21.9999L20.42 18.84L17.26 15.6799" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                
                {{ trans('storefront::product.compare') }}
            </button>
        </div>
    </div>

    <div class="details-info-middle">
        @if ($product->variants->isNotEmpty())
            <template x-if="isVariantSelectionComplete">
                <div class="product-price">
                    <template x-if="hasSpecialPrice">
                        <span class="special-price" x-text="formatCurrency(specialPrice)"></span>
                    </template>

                    <span class="previous-price" x-text="formatCurrency(regularPrice)"></span>
                </div>
            </template>

            <template x-if="!isVariantSelectionComplete">
                <div class="product-price">
                    <span class="previous-price">{{ $product->formatted_price_range ?? $product->formatted_price }}</span>
                </div>
            </template>
        @else
            <div class="product-price">
                <template x-if="hasSpecialPrice">
                    <span class="special-price" x-text="formatCurrency(specialPrice)"></span>
                </template>

                <span class="previous-price" x-text="formatCurrency(regularPrice)">
                    {{ $item->hasSpecialPrice() ? $item->special_price->format() : $item->price->format() }}
                </span>
            </div>
        @endif

        <form
            x-ref="productCartForm"
            @input="errors.clear($event.target.name)"
            @submit.prevent="addToCart"
        >
            @if ($product->variants->isNotEmpty())
                <div class="product-variants">
                    @include('storefront::public.products.show.variations')
                </div>
            @endif
            
            @if ($product->options->isNotEmpty())
                <div class="product-variants">
                    @foreach ($product->options as $option)
                        @includeIf("storefront::public.products.show.custom_options.{$option->type}")
                    @endforeach
                </div>
            @endif

            <div class="details-info-middle-actions">
                <div class="number-picker-lg">
                    <label for="qty">{{ trans('storefront::product.quantity') }}</label>

                    <div class="input-group-quantity">
                        <input
                            x-ref="inputQuantity"
                            type="text"
                            :value="cartItemForm.qty"
                            autocomplete="off"
                            min="1"
                            :max="maxQuantity"
                            id="qty"
                            class="form-control input-number input-quantity"
                            :disabled="isAddToCartDisabled"
                            @focus="$event.target.select()"
                            @input="updateQuantity(Number($event.target.value))"
                            @keydown.up="updateQuantity(cartItemForm.qty + 1)"
                            @keydown.down="updateQuantity(cartItemForm.qty - 1)"
                        >

                        <span class="btn-wrapper">
                            <button
                                type="button"
                                aria-label="quantity"
                                class="btn btn-number btn-plus"
                                :disabled="isQtyIncreaseDisabled"
                                @click="updateQuantity(cartItemForm.qty + 1)"
                            >
                                +
                            </button>

                            <button
                                type="button"
                                aria-label="quantity"
                                class="btn btn-number btn-minus"
                                :disabled="isQtyDecreaseDisabled"
                                @click="updateQuantity(cartItemForm.qty - 1)"
                            >
                                -
                            </button>
                        </span>
                    </div>
                </div>

                <button
                    type="submit"
                    x-ref="addToCartBtn"
                    class="btn btn-primary btn-add-to-cart"
                    :class="{'btn-loading': addingToCart }"
                    :disabled="isAddToCartDisabled"
                    x-text="isActiveItem ? '{{ trans('storefront::product.add_to_cart') }}' : '{{ trans('storefront::product.unavailable') }}'"
                >
                    {{ trans($item->is_active ? 'storefront::product.add_to_cart' : 'storefront::product.unavailable') }}
                </button>
            </div>
        </form>
    </div>

    <div class="details-info-bottom">
        <ul class="list-inline additional-info">
            @if (! $product->is_virtual)
                <template x-cloak x-if="item.sku && !product.is_virtual">
                    <li class="sku">
                        <label>{{ trans('storefront::product.sku') }}</label>

                        <span x-text="item.sku">{{ $item->sku }}</span>
                    </li>
                </template>
            @endif

            @if ($product->categories->isNotEmpty())
                <li>
                    <label>{{ trans('storefront::product.categories') }}</label>

                    @foreach ($product->categories as $category)
                        <a href="{{ $category->url() }}">{{ $category->name }}</a>{{ $loop->last ? '' : ',' }}
                    @endforeach
                </li>
            @endif

            @if ($product->tags->isNotEmpty())
                <li class="product-tags-list">
                    <label>{{ trans('storefront::product.tags') }}</label>

                    <span class="product-tags">
                        @foreach ($product->tags as $tag)
                            <a
                                href="{{ $tag->url() }}"
                                class="product-tag-badge product-tag-badge--{{ abs(crc32($tag->slug)) % 8 }}"
                            >
                                {{ $tag->name }}
                            </a>
                        @endforeach
                    </span>
                </li>
            @endif
        </ul>

        @include('storefront::public.products.show.social_share')
    </div>
</div>
