import Swiper from "swiper";
import { Autoplay, Navigation, Pagination, Parallax } from "swiper/modules";
import { runAfterPaint } from "../../../support/scheduleInit";

Alpine.data("Hero", () => ({
    init() {
        runAfterPaint(() => this.initHeroSlider());
    },

    loadSlideBackground(slideEl) {
        const bg = slideEl?.querySelector?.(".slider-bg-image");

        if (!bg?.dataset?.bg || bg.style.backgroundImage) {
            return;
        }

        bg.style.backgroundImage = `url(${bg.dataset.bg})`;
    },

    loadVisibleSlideBackgrounds(swiper) {
        if (!swiper?.slides?.length) {
            return;
        }

        const indices = new Set([
            swiper.activeIndex,
            swiper.activeIndex + 1,
            swiper.activeIndex - 1,
        ]);

        indices.forEach((index) => {
            if (index >= 0 && index < swiper.slides.length) {
                this.loadSlideBackground(swiper.slides[index]);
            }
        });
    },

    initHeroSlider() {
        const { speed, autoplay, autoplaySpeed, dots, arrows } =
            $(".home-slider").data();

        const swiper = new Swiper(".home-slider", {
            modules: [Autoplay, Navigation, Pagination, Parallax],
            slidesPerView: 1,
            speed,
            parallax: true,
            ...(autoplay && {
                autoplay: {
                    delay: autoplaySpeed,
                    pauseOnMouseEnter: true,
                },
            }),
            ...(arrows && {
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
            }),
            ...(dots && {
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
            }),
            on: {
                init: (instance) => this.loadVisibleSlideBackgrounds(instance),
                slideChange: (instance) => this.loadVisibleSlideBackgrounds(instance),
            },
        });

        this.loadVisibleSlideBackgrounds(swiper);
    },
}));
