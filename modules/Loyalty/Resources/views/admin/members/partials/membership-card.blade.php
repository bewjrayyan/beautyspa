@php
    $loyaltyConfig = app(\Modules\Loyalty\Services\LoyaltyConfig::class);
    $tierSlug = $member->tier?->slug ?? 'default';
    $tierName = $member->tier?->translatedName() ?? '—';
    $memberName = strtoupper($user?->full_name ?? trans('loyalty::members.member'));
    $cardNumber = str_pad((string) $member->id, 16, '0', STR_PAD_LEFT);
    $cardNumberFormatted = implode(' ', str_split($cardNumber, 4));
    $referralCode = $user?->referral_code;
    $tierBenefits = $member->tier?->benefits ?? [];
    $earnRate = $loyaltyConfig->earnRatePerRm();
    $tierMultiplier = $member->tier?->earn_multiplier ?? 1;
    $pointValue = $loyaltyConfig->pointValueRm();
    $expireMonths = $loyaltyConfig->pointsExpireMonths();
    $memberSince = $member->created_at?->format('m/y') ?? '—';
    $compact = $compact ?? false;
    $cardWatermarkUrl = \Modules\Loyalty\Support\MembershipCardBranding::watermarkUrl();
@endphp

<div class="loyalty-member-wallet-cards {{ $compact ? 'loyalty-member-wallet-cards--compact' : '' }}">
    @unless ($compact)
        <div class="loyalty-member-wallet-cards__head">
            <h3>
                <i class="fa fa-credit-card" aria-hidden="true"></i>
                {{ trans('loyalty::members.show.card_title') }}
            </h3>
        </div>
    @endunless

    <div class="loyalty-member-wallet-cards__stack">
        <div class="loyalty-member-wallet-cards__item">
            @unless ($compact)
                <span class="loyalty-member-wallet-cards__label">{{ trans('loyalty::members.show.card_front') }}</span>
            @endunless

            <article
                class="loyalty-membership-card loyalty-membership-card--front loyalty-membership-card--{{ $tierSlug }}"
                aria-label="{{ trans('loyalty::members.show.card_front') }}"
            >
                <div class="loyalty-membership-card__pattern" aria-hidden="true"></div>
                <div class="loyalty-membership-card__shine" aria-hidden="true"></div>

                @if ($cardWatermarkUrl)
                    <div class="loyalty-membership-card__watermark" aria-hidden="true">
                        <img src="{{ $cardWatermarkUrl }}" alt="">
                    </div>
                @endif

                <header class="loyalty-membership-card__top">
                    <span class="loyalty-membership-card__brand">{{ setting('store_name') }}</span>
                    <span class="loyalty-membership-card__tier-badge">{{ $tierName }}</span>
                </header>

                <div class="loyalty-membership-card__chip-row">
                    <span class="loyalty-membership-card__chip" aria-hidden="true"></span>
                    <span class="loyalty-membership-card__contactless" aria-hidden="true">
                        <i class="fa fa-wifi" aria-hidden="true"></i>
                    </span>
                </div>

                <p class="loyalty-membership-card__number">{{ $cardNumberFormatted }}</p>

                <div class="loyalty-membership-card__holder-row">
                    <div class="loyalty-membership-card__holder">
                        <span class="loyalty-membership-card__field-label">
                            {{ trans('loyalty::members.show.card_cardholder') }}
                        </span>
                        <span class="loyalty-membership-card__name">{{ $memberName }}</span>
                    </div>

                    <div class="loyalty-membership-card__points-block">
                        <span class="loyalty-membership-card__field-label">
                            {{ trans('loyalty::members.show.card_points') }}
                        </span>
                        <strong class="loyalty-membership-card__points-value">
                            {{ number_format($member->balance) }}
                        </strong>
                    </div>
                </div>

                <footer class="loyalty-membership-card__footer">
                    <div class="loyalty-membership-card__valid">
                        <span class="loyalty-membership-card__field-label">
                            {{ trans('loyalty::members.show.card_member_since') }}
                        </span>
                        <span class="loyalty-membership-card__valid-value">{{ $memberSince }}</span>
                    </div>
                    <span class="loyalty-membership-card__network-mark">
                        {{ trans('loyalty::members.show.card_rewards') }}
                    </span>
                </footer>
            </article>
        </div>

        @unless ($compact)
        <div class="loyalty-member-wallet-cards__item">
            <span class="loyalty-member-wallet-cards__label">{{ trans('loyalty::members.show.card_back') }}</span>

            <article
                class="loyalty-membership-card loyalty-membership-card--back loyalty-membership-card--{{ $tierSlug }}"
                aria-label="{{ trans('loyalty::members.show.card_back') }}"
            >
                <div class="loyalty-membership-card__pattern" aria-hidden="true"></div>
                <div class="loyalty-membership-card__shine" aria-hidden="true"></div>
                <div class="loyalty-membership-card__back-overlay" aria-hidden="true"></div>

                <header class="loyalty-membership-card__back-header">
                    <span class="loyalty-membership-card__back-brand">
                        {{ trans('loyalty::members.show.card_terms_store', ['store' => setting('store_name')]) }}
                    </span>
                    <span class="loyalty-membership-card__back-tier">{{ $tierName }}</span>
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
                        <span class="loyalty-membership-card__stat-sub">×{{ $tierMultiplier }}</span>
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
                            <i class="fa fa-clock-o"></i>
                        </span>
                        <span class="loyalty-membership-card__stat-label">
                            {{ trans('loyalty::members.show.card_stat_valid') }}
                        </span>
                        <span class="loyalty-membership-card__stat-value">{{ $expireMonths }}</span>
                        <span class="loyalty-membership-card__stat-sub">{{ trans('loyalty::members.show.card_stat_valid_unit') }}</span>
                    </div>
                </div>

                @if ($referralCode)
                    <div class="loyalty-membership-card__referral-block">
                        <div class="loyalty-membership-card__referral-text">
                            <span class="loyalty-membership-card__field-label">
                                {{ trans('loyalty::members.show.card_referral') }}
                            </span>
                            <span class="loyalty-membership-card__referral-hint">
                                {{ trans('loyalty::members.show.card_referral_hint') }}
                            </span>
                        </div>
                        <code class="loyalty-membership-card__referral-code">{{ $referralCode }}</code>
                    </div>
                @endif

                @if (! empty($tierBenefits))
                    <div class="loyalty-membership-card__perks">
                        <span class="loyalty-membership-card__perks-title">
                            <i class="fa fa-check-circle" aria-hidden="true"></i>
                            {{ trans('loyalty::members.show.card_benefits_title') }}
                        </span>
                        <ul class="loyalty-membership-card__benefits">
                            @foreach (array_slice($tierBenefits, 0, 2) as $benefit)
                                <li>{{ $benefit }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p class="loyalty-membership-card__terms">
                    {{ trans('loyalty::members.show.card_terms') }}
                </p>
            </article>
        </div>
        @endunless
    </div>
</div>
