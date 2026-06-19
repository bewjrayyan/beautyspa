import { Pagination } from "swiper/modules";
import ProductTabsMixin from "../../../mixins/ProductTabsMixin";
import { whenVisible } from "../../../support/whenVisible";
import "../../../components/ProductCard";

Alpine.data("ProductTabsTwo", (tabs) => ({
    ...ProductTabsMixin(tabs),

    init() {
        whenVisible(this.$el, () => this.changeTab(0));
    },

    url(tabIndex) {
        return AestheticCart.url(
            `/storefront/tab-products/sections/${2}/tabs/${this.tabSlot(tabIndex)}`
        );
    },

    selector() {
        return ".landscape-right-tab-products";
    },

    swiperOptions() {
        return {
            modules: [Pagination],
            slidesPerView: 2,
            pagination: {
                el: ".swiper-pagination",
                dynamicBullets: true,
                clickable: true,
            },
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
