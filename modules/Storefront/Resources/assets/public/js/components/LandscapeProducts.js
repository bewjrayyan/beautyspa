import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";
import {
    productSliderNavigation,
    resolveProductSliderControls,
} from "../support/productSliderPagination";
import { wrapProductSliderOptions } from "../support/productSliderControlActions";
import { productSliderStateMixin } from "../support/productSliderStateMixin";
import "../components/ProductCard";

Alpine.data("LandscapeProducts", ({ url, watchState }) => ({
    products: [],
    swiper: null,
    loading: false,

    ...productSliderStateMixin(function () {
        return this.swiper;
    }),

    init() {
        this.fetchProducts();

        if (watchState) {
            this.$watch(watchState, (newValue) => {
                if (newValue) {
                    this.products = [];

                    this.$refs.landscapeProductsWrap.remove();
                }
            });
        }
    },

    hideLandscapeProductsSkeleton() {
        const skeletons = document.querySelectorAll(
            ".landscape-products .swiper-slide-skeleton"
        );

        skeletons.forEach((skeleton) => skeleton.remove());
    },

    async fetchProducts() {
        this.loading = true;

        try {
            const response = await axios.get(url);

            this.products = response.data;

            this.$nextTick(async () => {
                const swiperEl = this.$el.querySelector(".landscape-products");
                const options = {
                    modules: [Navigation, Pagination],
                    slidesPerView: 2,
                    watchOverflow: true,
                    ...productSliderNavigation(swiperEl, this.$el),
                    breakpoints: {
                        576: {
                            slidesPerView: 3,
                        },
                        830: {
                            slidesPerView: 4,
                        },
                        991: {
                            slidesPerView: 5,
                        },
                        1200: {
                            slidesPerView: 6,
                        },
                        1400: {
                            slidesPerView: 7,
                        },
                        1760: {
                            slidesPerView: 8,
                        },
                    },
                };

                const { paginationEl } = resolveProductSliderControls(
                    swiperEl,
                    this.$el
                );

                if (options.pagination && paginationEl) {
                    options.pagination.el = paginationEl;
                }

                const self = this;

                this.swiper = new Swiper(
                    swiperEl,
                    wrapProductSliderOptions(
                        options,
                        swiperEl,
                        this.$el,
                        (swiper) => self.updateSliderState(swiper)
                    )
                );
            });
        } catch (error) {
            notify(error.response.data.message);
        } finally {
            this.loading = false;
            this.hideLandscapeProductsSkeleton();
        }
    },
}));
