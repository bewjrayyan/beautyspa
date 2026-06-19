import Swiper from "swiper";
import { Navigation } from "swiper/modules";
import { chunk } from "lodash";
import { whenVisible } from "../../../../support/whenVisible";
import { runWhenIdle } from "../../../../support/scheduleInit";
import "../../../../components/ProductCard";

Alpine.data("VerticalProducts", (columnNumber) => ({
    chunk,
    products: [],
    productsLoaded: false,

    get hasAnyProduct() {
        return this.products.length !== 0;
    },

    init() {
        whenVisible(this.$el, () => {
            if (!this.productsLoaded) {
                this.productsLoaded = true;
                this.fetchProducts();
            }
        });
    },

    async fetchProducts() {
        const response = await axios.get(
            AestheticCart.url(`/storefront/vertical-products/${columnNumber}`)
        );

        this.products = response.data;

        this.$nextTick(() => {
            runWhenIdle(() => {
                new Swiper(this.$refs.verticalProducts, this.swiperOptions());
            });
        });
    },

    swiperOptions() {
        return {
            modules: [Navigation],
            slidesPerView: 1,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        };
    },
}));
