import Swiper from "swiper";
import { wrapProductSliderOptions } from "../support/productSliderControlActions";
import { productSliderStateMixin } from "../support/productSliderStateMixin";
import {
    resolveProductSliderControls,
    resetProductSliderControls,
} from "../support/productSliderPagination";
import { whenVisible } from "../support/whenVisible";
import { runAfterPaint, runSwiperInit } from "../support/scheduleInit";

export default function (tabs) {
    // Store outside Alpine reactivity — assigning Swiper to `this.swiper` lets Alpine
    // proxy the instance and break slide sizing after tab product updates.
    let swiperInstance = null;

    return {
        tabs,
        activeTab: null,
        activeTabIndex: null,
        loading: false,
        products: [],
        productsByTab: {},
        _renderGeneration: 0,
        _initialFetchDone: false,

        ...productSliderStateMixin(function () {
            return swiperInstance;
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

        waitForProductSlides(swiperEl, expectedCount, timeoutMs = 1500) {
            const expected = Math.max(0, Number(expectedCount) || 0);

            if (!swiperEl || expected === 0) {
                return this.waitForSlidesPaint();
            }

            const slideCount = () =>
                swiperEl.querySelectorAll(
                    ".swiper-slide:not(.swiper-slide-skeleton)"
                ).length;

            if (slideCount() >= expected) {
                return this.waitForSlidesPaint();
            }

            return new Promise((resolve) => {
                const startedAt = Date.now();

                const check = () => {
                    if (
                        slideCount() >= expected ||
                        Date.now() - startedAt >= timeoutMs
                    ) {
                        requestAnimationFrame(() => {
                            requestAnimationFrame(resolve);
                        });
                        return;
                    }

                    requestAnimationFrame(check);
                };

                check();
            });
        },

        cleanSwiperDom(swiperEl) {
            if (!swiperEl) {
                return;
            }

            swiperEl.classList.remove(
                "swiper-initialized",
                "swiper-horizontal",
                "swiper-vertical",
                "swiper-backface-hidden"
            );
            swiperEl.classList.add("is-swiper-pending");

            if (swiperEl.swiper) {
                try {
                    if (!swiperEl.swiper.destroyed) {
                        swiperEl.swiper.destroy(true, false);
                    }
                } catch (e) {
                    // ignore
                }

                delete swiperEl.swiper;
            }

            const wrapper = swiperEl.querySelector(".swiper-wrapper");

            if (wrapper) {
                wrapper.removeAttribute("style");
            }

            swiperEl.querySelectorAll(".swiper-slide").forEach((slide) => {
                slide.removeAttribute("style");
            });
        },

        destroySwiper() {
            if (swiperInstance && !swiperInstance.destroyed) {
                try {
                    swiperInstance.destroy(true, false);
                } catch (e) {
                    // ignore
                }
            }

            swiperInstance = null;
            this.cleanSwiperDom(this.$el?.querySelector(this.selector()));
        },

        mountSwiper(swiperEl) {
            if (!swiperEl?.isConnected) {
                return null;
            }

            this.cleanSwiperDom(swiperEl);

            const options = this.swiperOptions(swiperEl);

            this.bindProductSliderModules(swiperEl, options);

            const self = this;

            swiperInstance = new Swiper(
                swiperEl,
                wrapProductSliderOptions(
                    options,
                    swiperEl,
                    this.$el,
                    (swiper) => self.updateSliderState(swiper)
                )
            );

            swiperEl.classList.remove("is-swiper-pending");

            return swiperInstance;
        },

        refreshOrMountSwiper(swiperEl) {
            if (!swiperEl?.isConnected) {
                return;
            }

            if (swiperInstance && !swiperInstance.destroyed && swiperEl.swiper === swiperInstance) {
                try {
                    swiperInstance.update();
                    swiperInstance.slideTo(0, 0);
                    this.updateSliderState(swiperInstance);
                    swiperEl.classList.remove("is-swiper-pending");

                    return;
                } catch (e) {
                    swiperInstance = null;
                }
            }

            this.mountSwiper(swiperEl);
        },

        scheduleSwiperMount(swiperEl) {
            return new Promise((resolve) => {
                runAfterPaint(() => {
                    if (swiperEl?.isConnected) {
                        this.refreshOrMountSwiper(swiperEl);
                    }

                    resolve();
                });
            });
        },

        async renderProducts(tabIndex, products) {
            tabIndex = Number(tabIndex);

            if (!this.isActiveTab(tabIndex)) {
                return;
            }

            const generation = this._renderGeneration;

            this.loading = true;
            this.products = Array.isArray(products) ? [...products] : products;
            this.hideSkeletons();

            try {
                await this.$nextTick();
                await this.$nextTick();

                let swiperEl = this.$el.querySelector(this.selector());

                await this.waitForProductSlides(swiperEl, this.products.length);

                if (
                    generation !== this._renderGeneration ||
                    !this.isActiveTab(tabIndex)
                ) {
                    return;
                }

                swiperEl = this.$el.querySelector(this.selector()) || swiperEl;

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

                // Prefer update() over destroy/remount so Alpine x-for and Swiper
                // do not fight when switching Shop by Category tabs.
                this.refreshOrMountSwiper(swiperEl);

                await this.waitForSlidesPaint();

                if (
                    generation === this._renderGeneration &&
                    this.isActiveTab(tabIndex) &&
                    this.products.length > 0 &&
                    (!swiperEl.swiper || swiperEl.swiper.destroyed)
                ) {
                    this.mountSwiper(this.$el.querySelector(this.selector()) || swiperEl);
                }
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
