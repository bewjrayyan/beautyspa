import Swiper from "swiper";
import {
    resolveProductSliderControls,
    resetProductSliderControls,
} from "../support/productSliderPagination";
import { runWhenIdle } from "../support/scheduleInit";

export default function (tabs) {
    return {
        tabs,
        activeTab: null,
        loading: false,
        swiper: null,
        products: [],

        get hasAnyProduct() {
            return this.products.length;
        },

        tab(index) {
            return this.tabs[index].name || this.tabs[index];
        },

        async changeTab(index) {
            if (
                this.activeTab === this.tab(index) ||
                this.tab(index) === undefined
            ) {
                return;
            }

            this.activeTab = this.tab(index);

            this.fetchProducts(index);
        },

        classes(index) {
            return {
                active: this.activeTab === this.tab(index) && !this.loading,
                loading: this.activeTab === this.tab(index) && this.loading,
            };
        },

        hideSkeletons() {
            const swiperEl = this.$el?.querySelector(this.selector());

            if (!swiperEl) {
                return;
            }

            swiperEl
                .querySelectorAll(".swiper-slide-skeleton")
                .forEach((skeleton) => skeleton.remove());
        },

        bindProductSliderModules(swiperEl, options) {
            const { controls, paginationEl, prevEl, nextEl } =
                resolveProductSliderControls(swiperEl, this.$el);

            if (options.navigation) {
                options.navigation.prevEl = prevEl;
                options.navigation.nextEl = nextEl;
            }

            if (options.pagination && paginationEl) {
                options.pagination.el = paginationEl;
            }

            return controls;
        },

        async fetchProducts(tabIndex = 0) {
            this.loading = true;

            try {
                const response = await axios.get(this.url(tabIndex));
                const swiperEl = this.$el.querySelector(this.selector());
                const controls = swiperEl
                    ? resolveProductSliderControls(swiperEl, this.$el).controls
                    : null;

                if (this.swiper) {
                    this.swiper.destroy(false, false);
                    this.swiper = null;
                }

                resetProductSliderControls(controls);

                this.products = response.data;
                this.hideSkeletons();

                await this.$nextTick();

                if (this.products.length === 0 || !swiperEl) {
                    return;
                }

                runWhenIdle(() => {
                    if (this.products.length === 0 || !swiperEl) {
                        return;
                    }

                    const options = this.swiperOptions(swiperEl);
                    this.bindProductSliderModules(swiperEl, options);

                    this.swiper = new Swiper(swiperEl, options);
                });
            } catch (error) {
                // handle error
            } finally {
                this.loading = false;
            }
        },
    };
}
