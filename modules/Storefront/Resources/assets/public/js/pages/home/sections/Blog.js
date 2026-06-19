import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";
import { whenVisible } from "../../../support/whenVisible";
import { runSwiperInit } from "../../../support/scheduleInit";
import { productSliderNavigation, swiperDomObservers } from "../../../support/productSliderPagination";
import { wrapProductSliderOptions } from "../../../support/productSliderControlActions";
import { productSliderStateMixin } from "../../../support/productSliderStateMixin";

Alpine.data("Blog", () => ({
    swiper: null,
    loading: false,

    ...productSliderStateMixin(function () {
        return this.swiper;
    }),

    init() {
        whenVisible(this.$el, () => this.initBlogPostsSlider());
    },

    initBlogPostsSlider() {
        const swiperEl = this.$refs.blogSlider;

        if (!swiperEl || swiperEl.classList.contains("swiper-initialized")) {
            return;
        }

        runSwiperInit(() => {
            if (!swiperEl || swiperEl.classList.contains("swiper-initialized")) {
                return;
            }

            const self = this;

            this.swiper = new Swiper(
                swiperEl,
                wrapProductSliderOptions(
                    {
                        modules: [Navigation, Pagination],
                        slidesPerView: 1.12,
                        spaceBetween: 12,
                        watchOverflow: true,
                        ...swiperDomObservers(),
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
                    },
                    swiperEl,
                    this.$el,
                    (swiper) => self.updateSliderState(swiper)
                )
            );
        });
    },
}));
