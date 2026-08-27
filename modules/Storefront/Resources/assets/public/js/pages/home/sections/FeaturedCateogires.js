import { Navigation, Pagination } from "swiper/modules";
import ProductTabsMixin from "../../../mixins/ProductTabsMixin";
import { productSliderNavigation } from "../../../support/productSliderPagination";
import "../../../components/ProductCard";

Alpine.data("FeaturedCategories", (tabs) => ({
    ...ProductTabsMixin(tabs),

    init() {
        this.initProductTabs();
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
            slidesPerView: 1.35,
            spaceBetween: 12,
            watchOverflow: true,
            // Observer fights Alpine x-for when switching category tabs and can leave
            // a destroyed instance with full-width cards.
            observer: false,
            observeParents: false,
            ...productSliderNavigation(swiperEl, this.$el),
            breakpoints: {
                576: {
                    slidesPerView: 1.35,
                    spaceBetween: 14,
                },
                768: {
                    slidesPerView: 4.3,
                    spaceBetween: 10,
                },
                992: {
                    slidesPerView: 4.3,
                    spaceBetween: 14,
                },
                1200: {
                    slidesPerView: 4.3,
                    spaceBetween: 20,
                },
            },
        };
    },
}));
