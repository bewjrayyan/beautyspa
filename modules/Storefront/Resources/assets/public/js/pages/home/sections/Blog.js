import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";
import { whenVisible } from "../../../support/whenVisible";
import { runWhenIdle } from "../../../support/scheduleInit";
import { productSliderNavigation } from "../../../support/productSliderPagination";

Alpine.data("Blog", () => ({
    swiper: null,

    init() {
        whenVisible(this.$el, () => this.initBlogPostsSlider());
    },

    initBlogPostsSlider() {
        const swiperEl = this.$refs.blogSlider;

        if (!swiperEl || swiperEl.classList.contains("swiper-initialized")) {
            return;
        }

        runWhenIdle(() => {
            if (!swiperEl || swiperEl.classList.contains("swiper-initialized")) {
                return;
            }

            this.swiper = new Swiper(swiperEl, {
                modules: [Navigation, Pagination],
                slidesPerView: 1.12,
                spaceBetween: 12,
                watchOverflow: true,
                observer: true,
                observeParents: true,
                ...productSliderNavigation(swiperEl, this.$el),
                breakpoints: {
                    576: {
                        slidesPerView: 1.35,
                        spaceBetween: 14,
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 16,
                    },
                    991: {
                        slidesPerView: 2.2,
                        spaceBetween: 16,
                    },
                    1200: {
                        slidesPerView: 3,
                        spaceBetween: 20,
                    },
                    1600: {
                        slidesPerView: 4,
                        spaceBetween: 20,
                    },
                },
            });
        });
    },
}));
