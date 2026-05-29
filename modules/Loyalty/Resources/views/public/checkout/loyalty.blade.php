@if (auth()->check() && app('modules')->isEnabled('Loyalty'))
    <div class="loyalty-wrap loyalty-wrap--modern">
        <label class="checkout-promo-label">
            {{ trans('loyalty::checkout.rewards') }}
        </label>

        <p class="loyalty-balance-text">
            {{ trans('loyalty::checkout.balance', [
                'points' => number_format($loyaltyBalance ?? 0),
                'worth' => number_format($loyaltyWorthRm ?? 0, 2),
            ]) }}
        </p>

        @if (($loyaltyBalance ?? 0) <= 0)
            <p class="loyalty-empty-text">
                {{ trans('loyalty::checkout.no_points_available') }}
            </p>
        @endif

        <template x-if="!$store.cart.hasLoyalty && {{ (int) ($loyaltyBalance ?? 0) }} > 0">
            <div>
                <div class="checkout-promo-row checkout-promo-row--loyalty">
                    <input
                        type="number"
                        min="0"
                        class="form-control checkout-promo-input"
                        placeholder="{{ trans('loyalty::checkout.points_to_use') }}"
                        x-model.number="loyaltyPoints"
                        @input="loyaltyError = null"
                    >

                    <button
                        type="button"
                        class="btn checkout-promo-btn-outline"
                        @click.prevent="useMaxLoyaltyPoints"
                    >
                        {{ trans('loyalty::checkout.use_max') }}
                    </button>

                    <button
                        type="button"
                        class="btn checkout-promo-btn checkout-promo-btn--loyalty"
                        :class="{ 'btn-loading': applyingLoyalty }"
                        @click.prevent="applyLoyalty"
                    >
                        {{ trans('loyalty::checkout.apply') }}
                    </button>
                </div>

                <template x-if="loyaltyError">
                    <span class="error-message" x-text="loyaltyError"></span>
                </template>
            </div>
        </template>

        <template x-if="$store.cart.hasLoyalty">
            <div class="loyalty-applied-row">
                <span class="color-primary" x-text="loyaltyAppliedLabel"></span>

                <button
                    type="button"
                    class="btn btn-link btn-sm"
                    @click.prevent="removeLoyalty"
                >
                    {{ trans('loyalty::checkout.remove') }}
                </button>
            </div>
        </template>
    </div>
@endif
