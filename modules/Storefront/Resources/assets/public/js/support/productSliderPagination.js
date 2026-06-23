export function productSliderPagination(paginationEl) {
    if (!paginationEl) {
        return undefined;
    }

    return {
        el: paginationEl,
        dynamicBullets: false,
        clickable: true,
    };
}

export function resolveProductSliderControls(swiperEl, scopeEl) {
    const scope =
        scopeEl ??
        swiperEl?.closest(".tab-content, .landscape-products-inner") ??
        swiperEl?.parentElement;

    const controls = scope?.querySelector(".product-slider-controls");

    return {
        controls,
        paginationEl: controls?.querySelector(".swiper-pagination") ?? null,
        prevEl: controls?.querySelector(".swiper-button-prev") ?? null,
        nextEl: controls?.querySelector(".swiper-button-next") ?? null,
    };
}

export function resetProductSliderControls(controls) {
    if (!controls) {
        return;
    }

    const paginationEl = controls.querySelector(".swiper-pagination");
    const prevEl = controls.querySelector(".swiper-button-prev");
    const nextEl = controls.querySelector(".swiper-button-next");

    if (paginationEl) {
        paginationEl.innerHTML = "";
        paginationEl.className = "swiper-pagination";
        paginationEl.removeAttribute("style");
    }

    [prevEl, nextEl].forEach((button) => {
        if (!button) {
            return;
        }

        button.classList.remove(
            "swiper-button-lock",
            "swiper-button-disabled",
            "swiper-button-hidden"
        );
        button.removeAttribute("aria-disabled");
    });
}

export function productSliderNavigation(swiperEl, scopeEl) {
    const { paginationEl } = resolveProductSliderControls(swiperEl, scopeEl);

    return {
        pagination: productSliderPagination(paginationEl),
    };
}

export function swiperDomObservers() {
    if (window.matchMedia("(max-width: 991px)").matches) {
        return {};
    }

    return {
        observer: true,
        observeParents: true,
    };
}
