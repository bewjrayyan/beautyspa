import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";
import { whenVisible } from "../../../support/whenVisible";
import { runSwiperInit } from "../../../support/scheduleInit";
import { productSliderNavigation, swiperDomObservers } from "../../../support/productSliderPagination";
import { wrapProductSliderOptions } from "../../../support/productSliderControlActions";
import { productSliderStateMixin } from "../../../support/productSliderStateMixin";

Alpine.data("GoogleReviews", () => ({
    swiper: null,
    loading: false,

    ...productSliderStateMixin(function () {
        return this.swiper;
    }),

    init() {
        whenVisible(this.$el, () => this.initReviewsSlider());
    },

    initReviewsSlider() {
        const carousel = this.$refs.reviewsSlider;

        if (!carousel || carousel.classList.contains("swiper-initialized")) {
            return;
        }

        runSwiperInit(() => {
            if (!carousel || carousel.classList.contains("swiper-initialized")) {
                return;
            }

            const self = this;

            this.swiper = new Swiper(
                carousel,
                wrapProductSliderOptions(
                    {
                        modules: [Navigation, Pagination],
                        slidesPerView: 1.12,
                        spaceBetween: 12,
                        watchOverflow: true,
                        ...swiperDomObservers(),
                        ...productSliderNavigation(carousel, this.$el),
                        breakpoints: {
                            768: {
                                slidesPerView: 1.15,
                                spaceBetween: 16,
                            },
                            1200: {
                                slidesPerView: 1.35,
                                spaceBetween: 16,
                            },
                        },
                    },
                    carousel,
                    this.$el,
                    (swiper) => self.updateSliderState(swiper)
                )
            );
        });
    },
}));
