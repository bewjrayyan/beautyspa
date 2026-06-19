<div class="product-slider-controls" :class="{ 'is-loading': loading }">
    <button
        type="button"
        class="swiper-button-prev product-slider-controls__btn product-slider-controls__btn--prev"
        :disabled="loading || sliderAtStart"
        aria-label="{{ trans('storefront::layouts.prev') }}"
        @click.stop.prevent="slideProductSlider('prev')"
    >
        <span class="product-slider-controls__icon" aria-hidden="true"></span>
        <span class="product-slider-controls__label product-slider-controls__label--desktop">{{ trans('storefront::layouts.prev') }}</span>
    </button>

    <div class="product-slider-controls__meta">
        <span
            class="product-slider-controls__counter"
            x-show="sliderTotal > 0"
            x-text="sliderPositionLabel"
        ></span>
        <div class="swiper-pagination product-slider-controls__dots"></div>
    </div>

    <button
        type="button"
        class="swiper-button-next product-slider-controls__btn product-slider-controls__btn--next"
        :disabled="loading || sliderAtEnd"
        aria-label="{{ trans('storefront::layouts.next') }}"
        @click.stop.prevent="slideProductSlider('next')"
    >
        <span class="product-slider-controls__icon" aria-hidden="true"></span>
        <span class="product-slider-controls__label product-slider-controls__label--desktop">{{ trans('storefront::layouts.next') }}</span>
    </button>
</div>
