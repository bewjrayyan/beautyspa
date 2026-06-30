import countdown from "countdown";

export default function () {
    return {
        specialPriceCountdownDate: {
            days: "00",
            hours: "00",
            minutes: "00",
            seconds: "00",
        },
        specialPriceCountdownTimer: null,
        specialPriceCountdownVisible: false,
        specialPriceCountdownLabel: "",

        resolveSpecialPriceCountdownTarget() {
            if (!this.hasScheduledSpecialPrice()) {
                return null;
            }

            const now = new Date();
            const start = this.item?.special_price_start
                ? new Date(this.item.special_price_start)
                : null;
            const end = this.item?.special_price_end
                ? new Date(this.item.special_price_end)
                : null;

            if (start && now < start) {
                return { date: start, mode: "starts_in" };
            }

            if (end && now <= end && (!start || now >= start)) {
                return { date: end, mode: "ends_in" };
            }

            return null;
        },

        hasScheduledSpecialPrice() {
            return Boolean(
                this.item?.special_price_start || this.item?.special_price_end,
            );
        },

        canShowSpecialPriceCountdown() {
            if (this.hasVariants && !this.isVariantSelectionComplete) {
                return false;
            }

            return this.resolveSpecialPriceCountdownTarget() !== null;
        },

        resolveSpecialPriceCountdownLabel(mode) {
            if (mode === "starts_in") {
                return trans("storefront::product.promo_starts_in");
            }

            if (mode === "ends_in") {
                return trans("storefront::product.promo_ends_in");
            }

            return "";
        },

        initSpecialPriceCountdown() {
            this.destroySpecialPriceCountdown();

            const canShow = this.canShowSpecialPriceCountdown();
            const target = canShow
                ? this.resolveSpecialPriceCountdownTarget()
                : null;

            this.specialPriceCountdownVisible = Boolean(target);
            this.specialPriceCountdownLabel = target
                ? this.resolveSpecialPriceCountdownLabel(target.mode)
                : "";

            if (!target) {
                this.setSpecialPriceCountdownZeros();

                return;
            }

            this.specialPriceCountdownTimer = countdown(
                target.date,
                ({ days, hours, minutes, seconds }) => {
                    if (new Date() > target.date) {
                        this.destroySpecialPriceCountdown();
                        this.initSpecialPriceCountdown();

                        return;
                    }

                    this.specialPriceCountdownDate = {
                        days: this.leadingZero(days),
                        hours: this.leadingZero(hours),
                        minutes: this.leadingZero(minutes),
                        seconds: this.leadingZero(seconds),
                    };
                },
                countdown.DAYS |
                    countdown.HOURS |
                    countdown.MINUTES |
                    countdown.SECONDS,
            );
        },

        destroySpecialPriceCountdown() {
            if (this.specialPriceCountdownTimer) {
                window.clearInterval(this.specialPriceCountdownTimer);
                this.specialPriceCountdownTimer = null;
            }
        },

        leadingZero(value) {
            return value < 10 ? `0${value}` : value;
        },

        setSpecialPriceCountdownZeros() {
            this.specialPriceCountdownDate = {
                days: "00",
                hours: "00",
                minutes: "00",
                seconds: "00",
            };
        },
    };
}
