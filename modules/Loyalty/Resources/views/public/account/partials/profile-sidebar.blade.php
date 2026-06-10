@php
    $tierMultiplier = $loyaltyWallet->tier?->earn_multiplier ?? 1;
@endphp

<div class="account-profile-sidebar__card account-profile-sidebar__card--loyalty">
    <h2 class="account-profile-sidebar__title">
        <i class="las la-crown"></i>
        {{ trans('loyalty::account.title') }}
    </h2>

    <div class="account-profile-loyalty__card-wrap">
        @include('loyalty::admin.members.partials.membership-card', [
            'member' => $loyaltyWallet,
            'user' => $account,
            'compact' => true,
        ])
    </div>

    <ul class="account-profile-sidebar__list account-profile-loyalty__stats">
        <li>
            <span class="account-profile-sidebar__label">{{ trans('loyalty::account.current_tier') }}</span>
            <span class="account-profile-sidebar__value">{{ $loyaltyWallet->tier?->translatedName() ?? '—' }}</span>
        </li>
        <li>
            <span class="account-profile-sidebar__label">{{ trans('loyalty::account.points_balance') }}</span>
            <span class="account-profile-sidebar__value account-profile-loyalty__points">
                {{ number_format($loyaltyWallet->balance) }}
            </span>
        </li>
        <li>
            <span class="account-profile-sidebar__label">{{ trans('loyalty::account.worth') }}</span>
            <span class="account-profile-sidebar__value">RM {{ number_format($loyaltyBalanceRm, 2) }}</span>
        </li>
        <li>
            <span class="account-profile-sidebar__label">{{ trans('loyalty::account.earn_rate') }}</span>
            <span class="account-profile-sidebar__value">
                {{ trans('loyalty::account.earn_rate_value', [
                    'rate' => $loyaltyEarnRate,
                    'multiplier' => $tierMultiplier,
                ]) }}
            </span>
        </li>
    </ul>

    <a href="{{ route('account.loyalty.index') }}" class="btn btn-default btn-sm btn-block account-profile-loyalty__link">
        <i class="las la-gift"></i>
        {{ trans('loyalty::account.profile_view_rewards') }}
    </a>
</div>
