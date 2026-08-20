<div
    class="product-slider-controls"
    :class="{ 'is-loading': loading, 'use-counter': sliderUseCounter }"
>
    <button
        type="button"
        class="swiper-button-prev product-slider-controls__btn product-slider-controls__btn--prev"
        :disabled="loading || sliderAtStart"
        aria-label="{{ trans('storefront::layouts.prev') }}"
        @click.stop.prevent="slideProductSlider('prev')"
    >
        <i class="las la-angle-left product-slider-controls__icon" aria-hidden="true"></i>
    </button>

    <div class="product-slider-controls__meta">
        <span
            class="product-slider-controls__counter"
            x-show="sliderUseCounter && sliderTotal > 1"
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
        <i class="las la-angle-right product-slider-controls__icon" aria-hidden="true"></i>
    </button>
</div>
