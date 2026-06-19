import { resolveProductSliderControls } from "./productSliderPagination";

export function syncProductSliderNav(swiper, swiperEl, scopeEl) {
    const { prevEl, nextEl } = resolveProductSliderControls(
        swiperEl,
        scopeEl ?? swiperEl?.parentElement
    );

    [prevEl, nextEl].forEach((button) => {
        if (!button) {
            return;
        }

        button.classList.remove(
            "swiper-button-lock",
            "swiper-button-hidden"
        );
        button.disabled = false;
    });

    if (prevEl) {
        const atStart = swiper.isBeginning;
        prevEl.classList.toggle("swiper-button-disabled", atStart);
        prevEl.toggleAttribute("aria-disabled", atStart);
        prevEl.disabled = atStart;
    }

    if (nextEl) {
        const atEnd = swiper.isEnd;
        nextEl.classList.toggle("swiper-button-disabled", atEnd);
        nextEl.toggleAttribute("aria-disabled", atEnd);
        nextEl.disabled = atEnd;
    }
}

export function wrapProductSliderOptions(
    options,
    swiperEl,
    scopeEl,
    onSync = null
) {
    const userOn = options.on ?? {};

    const sync = (swiper) => {
        syncProductSliderNav(swiper, swiperEl, scopeEl);
        onSync?.(swiper);
    };

    return {
        ...options,
        navigation: false,
        on: {
            ...userOn,
            init: (swiper) => {
                userOn.init?.(swiper);
                swiper.update();
                sync(swiper);
            },
            slideChange: (swiper) => {
                userOn.slideChange?.(swiper);
                sync(swiper);
            },
            reachBeginning: (swiper) => {
                userOn.reachBeginning?.(swiper);
                sync(swiper);
            },
            reachEnd: (swiper) => {
                userOn.reachEnd?.(swiper);
                sync(swiper);
            },
            fromEdge: (swiper) => {
                userOn.fromEdge?.(swiper);
                sync(swiper);
            },
            resize: (swiper) => {
                userOn.resize?.(swiper);
                sync(swiper);
            },
        },
    };
}
