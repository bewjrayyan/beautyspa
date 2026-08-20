import { Grid, Navigation, Pagination } from "swiper/modules";
import ProductTabsMixin from "../../../mixins/ProductTabsMixin";
import { productSliderNavigation, swiperDomObservers } from "../../../support/productSliderPagination";
import "../../../components/ProductCard";

Alpine.data("GridProducts", (tabs) => ({
    ...ProductTabsMixin(tabs),

    init() {
        this.initProductTabs();
    },

    url(tabIndex) {
        return AestheticCart.url(`/storefront/product-grid/tabs/${this.tabSlot(tabIndex)}`);
    },

    selector() {
        return ".grid-products";
    },

    swiperOptions(swiperEl) {
        return {
            modules: [Grid, Navigation, Pagination],
            slidesPerView: 2,
            spaceBetween: 12,
            watchOverflow: true,
            ...swiperDomObservers(),
            grid: {
                rows: 1,
            },
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
                    grid: {
                        rows: 2,
                    },
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
