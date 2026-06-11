@extends('storefront::public.account.layout')

@section('title', trans('loyalty::account.title'))

@section('account_breadcrumb')
    <li class="active">{{ trans('loyalty::account.breadcrumb') }}</li>
@endsection

@section('panel')
    @php
        $earnRate = config('fleetcart.modules.loyalty.config.earn_rate_per_rm', 1);
        $tierMultiplier = $wallet->tier?->earn_multiplier ?? 1;
        $referralUrl = route('register') . '?ref=' . urlencode($referralCode);
    @endphp

    <div class="account-loyalty-show">
        @if (session('success'))
            <div class="account-loyalty-alert account-loyalty-alert--success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="account-loyalty-alert account-loyalty-alert--error">
                {{ session('error') }}
            </div>
        @endif

        @if (session('stamp_redeemed_code'))
            <div class="account-loyalty-redeem-success" id="stamp-redeem-success">
                <div class="account-loyalty-redeem-success__icon">
                    <i class="las la-check-circle"></i>
                </div>
                <div>
                    <h2 class="account-loyalty-redeem-success__title">{{ trans('loyalty::account.stamp_redeemed_title') }}</h2>
                    <p class="account-loyalty-redeem-success__lead">{{ trans('loyalty::account.stamp_redeemed_lead') }}</p>
                    <p class="account-loyalty-redeem-success__code">{{ session('stamp_redeemed_code') }}</p>
                    <p class="account-loyalty-redeem-success__hint">{{ trans('loyalty::account.stamp_redeemed_show_code') }}</p>
                </div>
            </div>
        @endif

        <header class="account-loyalty-show__hero">
            <div class="account-loyalty-show__hero-main">
                <h1 class="account-loyalty-show__title">
                    {{ trans('loyalty::account.title') }}
                </h1>

                <p class="account-loyalty-show__meta">
                    <i class="las la-crown"></i>
                    {{ trans('loyalty::account.current_tier') }}:
                    <strong>{{ $wallet->tier?->name ?? '—' }}</strong>
                </p>

                <div class="account-loyalty-show__badges">
                    <span class="badge badge-info">
                        {{ trans('loyalty::account.earn_rate') }}:
                        {{ trans('loyalty::account.earn_rate_value', [
                            'rate' => $earnRate,
                            'multiplier' => $tierMultiplier,
                        ]) }}
                    </span>
                </div>
            </div>

            <div class="account-loyalty-show__hero-points">
                <span class="account-loyalty-show__hero-points-label">
                    {{ trans('loyalty::account.points_balance') }}
                </span>
                <span class="account-loyalty-show__hero-points-value">
                    {{ number_format($wallet->balance) }}
                </span>
                <span class="account-loyalty-show__hero-points-worth">
                    {{ trans('loyalty::account.worth') }}: RM {{ number_format($balanceRm, 2) }}
                </span>
            </div>
        </header>

        <div class="account-loyalty-show__layout">
            <main class="account-loyalty-show__main">
                @if (! empty($stampCards))
                    <section class="account-loyalty-show__section account-loyalty-show__section--stamps" id="stamp-cards">
                        <h2 class="account-loyalty-show__section-title">
                            <i class="las la-ticket-alt"></i>
                            {{ trans('loyalty::account.stamp_cards') }}
                        </h2>
                        <p class="account-loyalty-show__section-lead">{{ trans('loyalty::account.stamp_cards_lead') }}</p>

                        @include('loyalty::public.partials.stamp-cards', [
                            'stampCards' => $stampCards,
                            'showRedeemActions' => true,
                        ])
                    </section>
                @endif

                <div class="account-loyalty-show__cards">
                    <div class="account-loyalty-stat-card">
                        <h2 class="account-loyalty-stat-card__title">
                            <i class="las la-medal"></i>
                            {{ trans('loyalty::account.current_tier') }}
                        </h2>
                        <p class="account-loyalty-stat-card__value">
                            {{ $wallet->tier?->name ?? '—' }}
                        </p>
                        @if ($wallet->tier?->min_lifetime_spend)
                            <p class="account-loyalty-stat-card__hint">
                                {{ trans('loyalty::account.tier_from_spend', [
                                    'amount' => number_format($wallet->tier->min_lifetime_spend, 2),
                                ]) }}
                            </p>
                        @endif
                    </div>

                    <div class="account-loyalty-stat-card">
                        <h2 class="account-loyalty-stat-card__title">
                            <i class="las la-wallet"></i>
                            {{ trans('loyalty::account.lifetime_spend') }}
                        </h2>
                        <p class="account-loyalty-stat-card__value">
                            RM {{ number_format($wallet->lifetime_spend, 2) }}
                        </p>
                    </div>

                    <div class="account-loyalty-stat-card">
                        <h2 class="account-loyalty-stat-card__title">
                            <i class="las la-gift"></i>
                            {{ trans('loyalty::account.earn_rate') }}
                        </h2>
                        <p class="account-loyalty-stat-card__value">
                            {{ trans('loyalty::account.earn_rate_value', [
                                'rate' => $earnRate,
                                'multiplier' => $tierMultiplier,
                            ]) }}
                        </p>
                    </div>
                </div>

                <section class="account-loyalty-show__section">
                    <h2 class="account-loyalty-show__section-title">
                        <i class="las la-history"></i>
                        {{ trans('loyalty::account.transactions') }}
                    </h2>

                    @if ($transactions->isEmpty())
                        <div class="account-loyalty-show__empty">
                            <i class="las la-inbox"></i>
                            <p>{{ trans('loyalty::account.no_transactions') }}</p>
                        </div>
                    @else
                        <div class="table-responsive account-loyalty-tx__table-wrap">
                            <table class="table table-borderless account-loyalty-tx__table">
                                <thead>
                                <tr>
                                    <th>{{ trans('storefront::account.date') }}</th>
                                    <th>{{ trans('loyalty::reports.type') }}</th>
                                    <th>{{ trans('loyalty::reports.points') }}</th>
                                    <th>{{ trans('loyalty::account.expires') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($transactions as $tx)
                                    <tr>
                                        <td>{{ $tx->created_at?->format('d M Y') }}</td>
                                        <td>
                                            @if (trans()->has('loyalty::reports.types.' . $tx->type))
                                                {{ trans('loyalty::reports.types.' . $tx->type) }}
                                            @else
                                                {{ $tx->type }}
                                            @endif
                                            @if ($tx->description)
                                                <span class="account-loyalty-tx__desc">{{ $tx->description }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="account-loyalty-tx__points {{ $tx->points >= 0 ? 'is-credit' : 'is-debit' }}">
                                                {{ $tx->points > 0 ? '+' : '' }}{{ number_format($tx->points) }}
                                            </span>
                                        </td>
                                        <td>{{ $tx->expires_at?->format('d M Y') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            </main>

            <aside class="account-loyalty-show__sidebar">
                @if (($stampRedemptions ?? collect())->isNotEmpty())
                    <div class="account-loyalty-sidebar__card">
                        <h2 class="account-loyalty-sidebar__title">
                            <i class="las la-history"></i>
                            {{ trans('loyalty::account.stamp_redemptions') }}
                        </h2>
                        <ul class="account-loyalty-sidebar__list account-loyalty-sidebar__list--stamps">
                            @foreach ($stampRedemptions as $redemption)
                                <li>
                                    <span class="account-loyalty-sidebar__label">{{ $redemption->program?->name }}</span>
                                    <span class="account-loyalty-sidebar__value">
                                        <code>{{ $redemption->redemption_code }}</code>
                                        <small>{{ $redemption->redeemed_at?->format('d M Y') }}</small>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($referralEnabled ?? false)
                    <div
                        class="account-loyalty-sidebar__card account-loyalty-sidebar__card--referral"
                        x-data="{
                            copied: false,
                            url: @js($referralUrl),
                            copy() {
                                navigator.clipboard.writeText(this.url).then(() => {
                                    this.copied = true;
                                    setTimeout(() => { this.copied = false }, 2000);
                                });
                            }
                        }"
                    >
                        <h2 class="account-loyalty-sidebar__title">
                            <i class="las la-share-alt"></i>
                            {{ trans('loyalty::account.referral_code') }}
                        </h2>

                        <p class="account-loyalty-sidebar__code">{{ $referralCode }}</p>
                        <p class="account-loyalty-sidebar__hint">{{ trans('loyalty::account.referral_share') }}</p>

                        <div class="account-loyalty-sidebar__link-row">
                            <input
                                type="text"
                                class="account-loyalty-sidebar__link-input"
                                readonly
                                :value="url"
                            >
                            <button type="button" class="account-loyalty-sidebar__copy-btn" @click="copy()">
                                <i class="las" :class="copied ? 'la-check' : 'la-copy'"></i>
                                <span x-text="copied ? @js(trans('loyalty::account.copied')) : @js(trans('loyalty::account.copy_link'))"></span>
                            </button>
                        </div>
                    </div>
                @endif

                <div class="account-loyalty-sidebar__card">
                    <h2 class="account-loyalty-sidebar__title">
                        <i class="las la-info-circle"></i>
                        {{ trans('loyalty::account.how_it_works') }}
                    </h2>
                    <ul class="account-loyalty-sidebar__list">
                        <li>
                            <span class="account-loyalty-sidebar__label">{{ trans('loyalty::account.points_balance') }}</span>
                            <span class="account-loyalty-sidebar__value">{{ number_format($wallet->balance) }}</span>
                        </li>
                        <li>
                            <span class="account-loyalty-sidebar__label">{{ trans('loyalty::account.worth') }}</span>
                            <span class="account-loyalty-sidebar__value">RM {{ number_format($balanceRm, 2) }}</span>
                        </li>
                        <li>
                            <span class="account-loyalty-sidebar__label">{{ trans('loyalty::account.lifetime_spend') }}</span>
                            <span class="account-loyalty-sidebar__value">RM {{ number_format($wallet->lifetime_spend, 2) }}</span>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/Loyalty/Resources/assets/public/sass/pages/account/loyalty/main.scss',
    ])
@endpush
