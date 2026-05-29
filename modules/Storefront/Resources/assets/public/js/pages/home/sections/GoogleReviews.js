import Swiper from "swiper";
import { Navigation } from "swiper/modules";

Alpine.data("GoogleReviews", () => ({
    init() {
        const carousel = this.$el.querySelector(".google-reviews-carousel");

        if (!carousel) {
            return;
        }

        new Swiper(carousel, {
            modules: [Navigation],
            slidesPerView: 1,
            spaceBetween: 16,
            navigation: {
                nextEl: carousel.querySelector(".swiper-button-next"),
                prevEl: carousel.querySelector(".swiper-button-prev"),
            },
            breakpoints: {
                768: {
                    slidesPerView: 1.15,
                },
                1200: {
                    slidesPerView: 1.35,
                },
            },
        });
    },
}));
