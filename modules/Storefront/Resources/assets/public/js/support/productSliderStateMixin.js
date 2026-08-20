import { SLIDER_COUNTER_PRODUCT_THRESHOLD } from "./productSliderPagination";

export function productSliderStateMixin(getSwiper) {
    return {
        sliderIndex: 0,
        sliderTotal: 0,
        sliderSlideCount: 0,
        sliderUseCounter: false,

        get sliderAtStart() {
            const swiper = getSwiper.call(this);

            return !swiper || swiper.destroyed || swiper.isBeginning;
        },

        get sliderAtEnd() {
            const swiper = getSwiper.call(this);

            return !swiper || swiper.destroyed || swiper.isEnd;
        },

        get sliderPositionLabel() {
            if (this.sliderTotal < 1) {
                return "—";
            }

            return `${this.sliderIndex + 1} / ${this.sliderTotal}`;
        },

        updateSliderState(swiper) {
            if (!swiper || swiper.destroyed) {
                this.sliderIndex = 0;
                this.sliderTotal = 0;
                this.sliderSlideCount = 0;
                this.sliderUseCounter = false;

                return;
            }

            const totalPages =
                swiper.snapGrid?.length ??
                swiper.pagination?.bullets?.length ??
                swiper.slides?.length ??
                0;

            const currentPage = swiper.snapIndex ?? swiper.activeIndex ?? 0;
            const slideCount = swiper.slides?.length ?? 0;

            this.sliderSlideCount = slideCount;
            this.sliderUseCounter =
                slideCount > SLIDER_COUNTER_PRODUCT_THRESHOLD;
            this.sliderTotal = totalPages;
            this.sliderIndex = Math.min(
                Math.max(currentPage, 0),
                Math.max(totalPages - 1, 0)
            );

        },

        slideProductSlider(direction) {
            const swiper = getSwiper.call(this);

            if (!swiper || swiper.destroyed || this.loading) {
                return;
            }

            if (direction === "prev" && !swiper.isBeginning) {
                swiper.slidePrev();
            }

            if (direction === "next" && !swiper.isEnd) {
                swiper.slideNext();
            }
        },
    };
}
