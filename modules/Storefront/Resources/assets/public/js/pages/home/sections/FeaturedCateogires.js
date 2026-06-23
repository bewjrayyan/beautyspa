import { Navigation, Pagination } from "swiper/modules";
import ProductTabsMixin from "../../../mixins/ProductTabsMixin";
import { whenVisible } from "../../../support/whenVisible";
import { productSliderNavigation, swiperDomObservers } from "../../../support/productSliderPagination";
import "../../../components/ProductCard";

Alpine.data("FeaturedCategories", (tabs) => ({
    ...ProductTabsMixin(tabs),

    init() {
        whenVisible(this.$el, () => this.changeTab(0));
    },

    url(tabIndex) {
        return AestheticCart.url(
            `/storefront/featured-categories/${tabIndex + 1}/products`
        );
    },

    selector() {
        return ".featured-category-products";
    },

    swiperOptions(swiperEl) {
        return {
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
                1760: {
                    slidesPerView: 5,
                    spaceBetween: 20,
                },
            },
        };
    },
}));
