import { Navigation, Pagination } from "swiper/modules";
import ProductTabsMixin from "../../../mixins/ProductTabsMixin";
import { whenVisible } from "../../../support/whenVisible";
import { productSliderNavigation } from "../../../support/productSliderPagination";
import "../../../components/ProductCard";

Alpine.data("ProductTabsOne", (tabs) => ({
    ...ProductTabsMixin(tabs),

    init() {
        whenVisible(this.$el, () => this.changeTab(0));
    },

    url(tabIndex) {
        return AestheticCart.url(
            `/storefront/tab-products/sections/${1}/tabs/${tabIndex + 1}`
        );
    },

    selector() {
        return ".landscape-left-tab-products";
    },

    swiperOptions(swiperEl) {
        return {
            modules: [Navigation, Pagination],
            slidesPerView: 2,
            observer: true,
            observeParents: true,
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
    },
}));
