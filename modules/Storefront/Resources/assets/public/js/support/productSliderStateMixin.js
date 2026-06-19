export function productSliderStateMixin(getSwiper) {
    return {
        sliderIndex: 0,
        sliderTotal: 0,

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

                return;
            }

            this.sliderIndex = swiper.activeIndex;
            this.sliderTotal = swiper.slides?.length ?? 0;
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
