function initMobileHomePromoSoundToggles() {
    document.querySelectorAll("[data-mobile-promo-sound-toggle]").forEach((button) => {
        if (button.dataset.bound === "true") {
            return;
        }

        button.dataset.bound = "true";

        const media = button.closest(".mobile-home-promo__media");
        const video = media?.querySelector(".mobile-home-promo__video");

        if (! video) {
            return;
        }

        const syncState = () => {
            const muted = video.muted;

            button.classList.toggle("is-unmuted", ! muted);
            button.setAttribute("aria-pressed", muted ? "false" : "true");
            button.setAttribute(
                "aria-label",
                muted
                    ? button.dataset.labelUnmute
                    : button.dataset.labelMute
            );
        };

        syncState();

        button.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();

            video.muted = ! video.muted;

            if (! video.muted) {
                video.play().catch(() => {});
            }

            syncState();
        });
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initMobileHomePromoSoundToggles);
} else {
    initMobileHomePromoSoundToggles();
}
