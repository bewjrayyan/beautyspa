import Swiper from "swiper";
import { Autoplay, Navigation, Pagination, Parallax } from "swiper/modules";
import { runAfterPaint } from "../../../support/scheduleInit";

function readSliderOptions(sliderEl) {
    const dataset = sliderEl?.dataset ?? {};

    const toBool = (value, fallback = false) => {
        if (value === undefined || value === null || value === "") {
            return fallback;
        }

        return value === true || value === "true" || value === "1" || value === 1;
    };

    const toNumber = (value, fallback) => {
        const parsed = Number(value);

        return Number.isFinite(parsed) ? parsed : fallback;
    };

    return {
        speed: toNumber(dataset.speed, 300),
        autoplay: toBool(dataset.autoplay, false),
        autoplaySpeed: toNumber(dataset.autoplaySpeed, 5000),
        dots: toBool(dataset.dots, false),
        arrows: toBool(dataset.arrows, false),
    };
}

Alpine.data("Hero", () => ({
    init() {
        // Paint first, then init — faster than waiting for requestIdleCallback.
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
        const sliderEl = this.$el?.querySelector?.(".home-slider");

        if (!sliderEl || sliderEl.swiper) {
            return;
        }

        const {
            speed,
            autoplay,
            autoplaySpeed,
            dots,
            arrows,
        } = readSliderOptions(sliderEl);

        const swiper = new Swiper(sliderEl, {
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
