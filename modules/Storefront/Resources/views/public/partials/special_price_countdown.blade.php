<div class="special-price-countdown special-price-countdown--aesthetic countdown">
    <div class="special-price-countdown__grid">
        <div class="special-price-countdown__unit">
            <span class="special-price-countdown__digit" x-text="specialPriceCountdownDate.days"></span>
            <span class="special-price-countdown__unit-label">{{ trans('storefront::product.countdown_days') }}</span>
        </div>

        <span class="special-price-countdown__divider" aria-hidden="true"></span>

        <div class="special-price-countdown__unit">
            <span class="special-price-countdown__digit" x-text="specialPriceCountdownDate.hours"></span>
            <span class="special-price-countdown__unit-label">{{ trans('storefront::product.countdown_hours') }}</span>
        </div>

        <span class="special-price-countdown__divider" aria-hidden="true"></span>

        <div class="special-price-countdown__unit">
            <span class="special-price-countdown__digit" x-text="specialPriceCountdownDate.minutes"></span>
            <span class="special-price-countdown__unit-label">{{ trans('storefront::product.countdown_minutes') }}</span>
        </div>

        <span class="special-price-countdown__divider" aria-hidden="true"></span>

        <div class="special-price-countdown__unit special-price-countdown__unit--seconds">
            <span class="special-price-countdown__digit" x-text="specialPriceCountdownDate.seconds"></span>
            <span class="special-price-countdown__unit-label">{{ trans('storefront::product.countdown_seconds') }}</span>
        </div>
    </div>
</div>
