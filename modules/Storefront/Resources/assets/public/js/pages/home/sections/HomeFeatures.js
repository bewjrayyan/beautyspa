import Swiper from "swiper";
import { Navigation, Autoplay } from "swiper/modules";
import { whenVisible } from "../../../support/whenVisible";
import { runWhenIdle } from "../../../support/scheduleInit";

Alpine.data("HomeFeatures", () => ({
    init() {
        whenVisible(this.$el, () => {
            runWhenIdle(() => this.initFeaturesSlider());
        });
    },

    initFeaturesSlider() {
        const swiperEl = this.$refs.featureList.closest(".features");

        new Swiper(swiperEl, {
            modules: [Navigation, Autoplay],
            slidesPerView: 1.35,
            spaceBetween: 10,
            autoplay: {
                delay: 3500,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: this.$el.querySelector(".swiper-button-next"),
                prevEl: this.$el.querySelector(".swiper-button-prev"),
            },
            breakpoints: {
                992: {
                    slidesPerView: 4,
                    spaceBetween: 0,
                },
                780: {
                    slidesPerView: 3,
                    spaceBetween: 0,
                },
            },
        });
    },
}));
