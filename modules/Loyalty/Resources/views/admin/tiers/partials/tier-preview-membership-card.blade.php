@php
    $tierTheme = in_array($tier->slug, ['silver', 'gold', 'platinum'], true) ? $tier->slug : 'default';
    $tierDisplayName = $isEdit ? $tier->translatedName() : ($tier->name ?: $lt('tiers.form.new_tier_title'));
    $multiplier = number_format((float) old('earn_multiplier', $tier->earn_multiplier ?? 1), 2);
    $minSpend = number_format((float) old('min_lifetime_spend', $tier->min_lifetime_spend ?? 0), 2);
    $sortOrder = old('sort_order', $tier->sort_order ?? 0);
    $isActive = (bool) old('is_active', $tier->is_active ?? true);
    $inactiveClass = $isActive ? '' : 'loyalty-membership-card--inactive';
    $loyaltyConfig = app(\Modules\Loyalty\Services\LoyaltyConfig::class);
    $earnRate = $loyaltyConfig->earnRatePerRm();
    $pointValue = $loyaltyConfig->pointValueRm();
@endphp

<div class="loyalty-tier-form__wallet-preview">
    <div class="loyalty-tier-form__wallet-item">
        <span class="loyalty-tier-form__wallet-label">
            {{ trans('loyalty::members.show.card_front') }}
        </span>

        <article
            class="loyalty-membership-card loyalty-membership-card--front loyalty-membership-card--{{ $tierTheme }} {{ $inactiveClass }}"
            id="tier-preview-card"
            data-tier-preview-card
            aria-label="{{ trans('loyalty::members.show.card_front') }}"
        >
            <div class="loyalty-membership-card__pattern" aria-hidden="true"></div>
            <div class="loyalty-membership-card__shine" aria-hidden="true"></div>

            <header class="loyalty-membership-card__top">
                <span class="loyalty-membership-card__brand">{{ setting('store_name') }}</span>
                <span class="loyalty-membership-card__tier-badge" data-preview="name">{{ $tierDisplayName }}</span>
            </header>

            <div class="loyalty-membership-card__chip-row">
                <span class="loyalty-membership-card__chip" aria-hidden="true"></span>
                <span class="loyalty-membership-card__contactless" aria-hidden="true">
                    <i class="fa fa-wifi" aria-hidden="true"></i>
                </span>
            </div>

            <p class="loyalty-membership-card__number">{{ $lt('tiers.form.preview_sample_number') }}</p>

            <div class="loyalty-membership-card__holder-row">
                <div class="loyalty-membership-card__holder">
                    <span class="loyalty-membership-card__field-label">
                        {{ trans('loyalty::members.show.card_cardholder') }}
                    </span>
                    <span class="loyalty-membership-card__name">{{ $lt('tiers.form.preview_sample_holder') }}</span>
                </div>

                <div class="loyalty-membership-card__points-block">
                    <span class="loyalty-membership-card__field-label">
                        {{ $lt('tiers.table.multiplier') }}
                    </span>
                    <strong class="loyalty-membership-card__points-value" id="tier-preview-multiplier" data-preview="multiplier">
                        {{ $multiplier }}×
                    </strong>
                </div>
            </div>

            <footer class="loyalty-membership-card__footer">
                <div class="loyalty-membership-card__valid">
                    <span class="loyalty-membership-card__field-label">
                        {{ $lt('tiers.table.min_spend') }}
                    </span>
                    <span class="loyalty-membership-card__valid-value" id="tier-preview-min-spend">
                        {{ $currencySymbol }} {{ $minSpend }}
                    </span>
                </div>
                <span class="loyalty-membership-card__network-mark">
                    {{ trans('loyalty::members.show.card_rewards') }}
                </span>
            </footer>
        </article>
    </div>

    <div class="loyalty-tier-form__wallet-item">
        <span class="loyalty-tier-form__wallet-label">
            {{ trans('loyalty::members.show.card_back') }}
        </span>

        <article
            class="loyalty-membership-card loyalty-membership-card--back loyalty-membership-card--{{ $tierTheme }} {{ $inactiveClass }}"
            data-tier-preview-card
            aria-label="{{ trans('loyalty::members.show.card_back') }}"
        >
            <div class="loyalty-membership-card__pattern" aria-hidden="true"></div>
            <div class="loyalty-membership-card__shine" aria-hidden="true"></div>
            <div class="loyalty-membership-card__back-overlay" aria-hidden="true"></div>

            <header class="loyalty-membership-card__back-header">
                <span class="loyalty-membership-card__back-brand">
                    {{ trans('loyalty::members.show.card_terms_store', ['store' => setting('store_name')]) }}
                </span>
                <span class="loyalty-membership-card__back-tier" data-preview="name">{{ $tierDisplayName }}</span>
            </header>

            <div class="loyalty-membership-card__back-stats">
                <div class="loyalty-membership-card__stat">
                    <span class="loyalty-membership-card__stat-icon" aria-hidden="true">
                        <i class="fa fa-star"></i>
                    </span>
                    <span class="loyalty-membership-card__stat-label">
                        {{ trans('loyalty::members.show.card_stat_earn') }}
                    </span>
                    <span class="loyalty-membership-card__stat-value">
                        {{ $earnRate }}<small>/RM</small>
                    </span>
                    <span class="loyalty-membership-card__stat-sub" data-preview="multiplier">×{{ $multiplier }}</span>
                </div>
                <div class="loyalty-membership-card__stat">
                    <span class="loyalty-membership-card__stat-icon" aria-hidden="true">
                        <i class="fa fa-gift"></i>
                    </span>
                    <span class="loyalty-membership-card__stat-label">
                        {{ trans('loyalty::members.show.card_stat_redeem') }}
                    </span>
                    <span class="loyalty-membership-card__stat-value">
                        {{ number_format($pointValue, 2) }}
                    </span>
                    <span class="loyalty-membership-card__stat-sub">RM/pt</span>
                </div>
                <div class="loyalty-membership-card__stat">
                    <span class="loyalty-membership-card__stat-icon" aria-hidden="true">
                        <i class="fa fa-sort-numeric-asc"></i>
                    </span>
                    <span class="loyalty-membership-card__stat-label">
                        {{ $lt('tiers.form.sort_order') }}
                    </span>
                    <span class="loyalty-membership-card__stat-value" id="tier-preview-sort">{{ $sortOrder }}</span>
                </div>
            </div>

            <div class="loyalty-membership-card__perks">
                <span class="loyalty-membership-card__perks-title">
                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                    {{ trans('loyalty::members.show.card_benefits_title') }}
                </span>
                <ul class="loyalty-membership-card__benefits" id="tier-preview-benefits">
                    @forelse ($benefitsLines as $line)
                        <li>{{ $line }}</li>
                    @empty
                        <li class="loyalty-tier-form__benefits-empty">{{ $lt('tiers.form.benefits_empty') }}</li>
                    @endforelse
                </ul>
            </div>

            <p class="loyalty-membership-card__terms">
                {{ trans('loyalty::members.show.card_terms') }}
            </p>
        </article>
    </div>

    @if ($isEdit)
        <p class="loyalty-tier-form__card-meta">
            {{ __('loyalty::tiers.index.members_count', ['count' => number_format($tier->wallets_count ?? 0)]) }}
        </p>
    @endif

    <p class="loyalty-tier-form__card-dimensions">
        {{ $lt('tiers.form.preview_card_dimensions') }}
    </p>
</div>
