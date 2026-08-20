import Swiper from "swiper";
import { wrapProductSliderOptions } from "../support/productSliderControlActions";
import { productSliderStateMixin } from "../support/productSliderStateMixin";
import {
    resolveProductSliderControls,
    resetProductSliderControls,
} from "../support/productSliderPagination";
import { whenVisible } from "../support/whenVisible";
import { runSwiperInit } from "../support/scheduleInit";

export default function (tabs) {
    return {
        tabs,
        activeTab: null,
        activeTabIndex: null,
        loading: false,
        swiper: null,
        products: [],
        productsByTab: {},
        _renderGeneration: 0,
        _initialFetchDone: false,

        ...productSliderStateMixin(function () {
            return this.swiper;
        }),

        get hasAnyProduct() {
            return this.products.length;
        },

        initProductTabs() {
            this.activeTabIndex = 0;
            this.activeTab = this.tab(0);

            whenVisible(this.$el, () => {
                if (this._initialFetchDone) {
                    return;
                }

                this._initialFetchDone = true;

                const tabIndex = Number(this.activeTabIndex ?? 0);

                if (this.productsByTab[tabIndex] || this.loading) {
                    return;
                }

                this.fetchProducts(tabIndex);
            });
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
            return this.activeTabIndex === Number(index);
        },

        changeTab(index) {
            index = Number(index);

            if (this.isActiveTab(index) || this.tab(index) === undefined) {
                return;
            }

            this._renderGeneration += 1;
            this.activeTabIndex = index;
            this.activeTab = this.tab(index);

            if (this.productsByTab[index]) {
                this.renderProducts(index, this.productsByTab[index]);

                return;
            }

            this.loading = true;
            this.destroySwiper();
            this.products = [];
            this.fetchProducts(index);
        },

        classes(index) {
            const tabIndex = Number(index);
            const isActive = this.activeTabIndex === tabIndex;

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

        destroySwiper() {
            if (this.swiper && !this.swiper.destroyed) {
                this.swiper.destroy(false, false);
            }

            this.swiper = null;
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

        scheduleSwiperMount(swiperEl) {
            return new Promise((resolve) => {
                runSwiperInit(() => {
                    if (swiperEl.isConnected) {
                        this.mountSwiper(swiperEl);
                    }

                    resolve();
                });
            });
        },

        refreshSwiper(swiperEl) {
            this.destroySwiper();
            return this.scheduleSwiperMount(swiperEl);
        },

        async renderProducts(tabIndex, products) {
            tabIndex = Number(tabIndex);

            if (!this.isActiveTab(tabIndex)) {
                return;
            }

            const generation = this._renderGeneration;
            const swiperEl = this.$el.querySelector(this.selector());

            this.loading = true;
            this.destroySwiper();
            this.products = Array.isArray(products) ? [...products] : products;
            this.hideSkeletons();

            try {
                await this.$nextTick();
                await this.$nextTick();
                await this.waitForSlidesPaint();

                if (
                    generation !== this._renderGeneration ||
                    !this.isActiveTab(tabIndex)
                ) {
                    return;
                }

                if (!swiperEl) {
                    this.sliderIndex = 0;
                    this.sliderTotal = 0;

                    return;
                }

                resetProductSliderControls(
                    resolveProductSliderControls(swiperEl, this.$el).controls
                );

                if (this.products.length === 0) {
                    this.sliderIndex = 0;
                    this.sliderTotal = 0;

                    return;
                }

                await this.refreshSwiper(swiperEl);
            } finally {
                if (
                    generation === this._renderGeneration &&
                    this.isActiveTab(tabIndex)
                ) {
                    this.loading = false;
                }
            }
        },

        async fetchProducts(tabIndex = 0) {
            tabIndex = Number(tabIndex);

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
