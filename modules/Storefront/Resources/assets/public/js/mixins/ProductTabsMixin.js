import Swiper from "swiper";
import { wrapProductSliderOptions, syncProductSliderNav } from "../support/productSliderControlActions";
import { productSliderStateMixin } from "../support/productSliderStateMixin";
import {
    resolveProductSliderControls,
    resetProductSliderControls,
} from "../support/productSliderPagination";

export default function (tabs) {
    return {
        tabs,
        activeTab: null,
        loading: false,
        swiper: null,
        products: [],
        productsByTab: {},
        _renderGeneration: 0,

        ...productSliderStateMixin(function () {
            return this.swiper;
        }),

        get hasAnyProduct() {
            return this.products.length;
        },

        tab(index) {
            const entry = this.tabs[index];

            if (entry == null) {
                return undefined;
            }

            if (typeof entry === "object") {
                return entry.title ?? entry.name;
            }

            return entry;
        },

        tabSlot(index) {
            const entry = this.tabs[index];

            if (entry != null && typeof entry === "object" && entry.slot != null) {
                return entry.slot;
            }

            return Number(index) + 1;
        },

        isActiveTab(index) {
            return this.activeTab === this.tab(index);
        },

        changeTab(index) {
            if (this.isActiveTab(index) || this.tab(index) === undefined) {
                return;
            }

            this._renderGeneration += 1;
            this.activeTab = this.tab(index);

            if (this.productsByTab[index]) {
                this.renderProducts(index, this.productsByTab[index]);

                return;
            }

            this.loading = true;
            this.products = [];
            this.fetchProducts(index);
        },

        classes(index) {
            const isActive = this.isActiveTab(index);

            return {
                active: isActive,
                loading: isActive && this.loading,
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
            const { paginationEl } = resolveProductSliderControls(
                swiperEl,
                this.$el
            );

            if (options.pagination && paginationEl) {
                options.pagination.el = paginationEl;
            }
        },

        waitForSlidesPaint() {
            return new Promise((resolve) => {
                requestAnimationFrame(() => {
                    requestAnimationFrame(resolve);
                });
            });
        },

        mountSwiper(swiperEl) {
            const options = this.swiperOptions(swiperEl);

            this.bindProductSliderModules(swiperEl, options);

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
        },

        refreshSwiper(swiperEl) {
            if (!this.swiper || this.swiper.destroyed) {
                this.mountSwiper(swiperEl);

                return;
            }

            this.swiper.updateSlides();
            this.swiper.slideTo(0, 0);
            this.swiper.update();
            syncProductSliderNav(this.swiper, swiperEl, this.$el);
            this.updateSliderState(this.swiper);
        },

        async renderProducts(tabIndex, products) {
            if (!this.isActiveTab(tabIndex)) {
                return;
            }

            const generation = this._renderGeneration;
            const swiperEl = this.$el.querySelector(this.selector());

            this.loading = true;
            this.products = Array.isArray(products) ? [...products] : products;
            this.hideSkeletons();

            await this.$nextTick();
            await this.$nextTick();
            await this.waitForSlidesPaint();

            if (generation !== this._renderGeneration || !this.isActiveTab(tabIndex)) {
                this.loading = false;

                return;
            }

            if (!swiperEl || this.products.length === 0) {
                this.sliderIndex = 0;
                this.sliderTotal = 0;
                this.loading = false;

                return;
            }

            resetProductSliderControls(
                resolveProductSliderControls(swiperEl, this.$el).controls
            );

            if (!this.swiper || this.swiper.destroyed) {
                this.mountSwiper(swiperEl);
            } else {
                this.refreshSwiper(swiperEl);
            }

            this.loading = false;
        },

        async fetchProducts(tabIndex = 0) {
            if (this.productsByTab[tabIndex]) {
                await this.renderProducts(tabIndex, this.productsByTab[tabIndex]);

                return;
            }

            this.loading = true;

            try {
                const response = await axios.get(this.url(tabIndex));

                this.productsByTab[tabIndex] = response.data;

                if (!this.isActiveTab(tabIndex)) {
                    return;
                }

                await this.renderProducts(tabIndex, response.data);
            } catch (error) {
                if (this.isActiveTab(tabIndex)) {
                    this.loading = false;
                }
            }
        },
    };
}
