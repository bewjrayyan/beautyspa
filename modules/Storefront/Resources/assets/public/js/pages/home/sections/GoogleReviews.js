import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";
import { whenVisible } from "../../../support/whenVisible";
import { runWhenIdle } from "../../../support/scheduleInit";
import { productSliderNavigation } from "../../../support/productSliderPagination";

Alpine.data("GoogleReviews", () => ({
    swiper: null,

    init() {
        whenVisible(this.$el, () => this.initReviewsSlider());
    },

    initReviewsSlider() {
        const carousel = this.$refs.reviewsSlider;

        if (!carousel || carousel.classList.contains("swiper-initialized")) {
            return;
        }

        runWhenIdle(() => {
            if (!carousel || carousel.classList.contains("swiper-initialized")) {
                return;
            }

            this.swiper = new Swiper(carousel, {
                modules: [Navigation, Pagination],
                slidesPerView: 1.12,
                spaceBetween: 12,
                watchOverflow: true,
                observer: true,
                observeParents: true,
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
            });
        });
    },
}));
