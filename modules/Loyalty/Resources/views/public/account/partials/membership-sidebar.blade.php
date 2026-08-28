@php
    $tierMultiplier = $wallet->tier?->earn_multiplier ?? 1;
@endphp

<div class="account-loyalty-sidebar__card account-loyalty-sidebar__card--membership">
    <h2 class="account-loyalty-sidebar__title">
        <i class="las la-crown"></i>
        {{ trans('loyalty::account.title') }}
    </h2>

    <div class="account-loyalty-membership__card-wrap">
        @include('loyalty::admin.members.partials.membership-card', [
            'member' => $wallet,
            'user' => $account,
            'compact' => true,
        ])
    </div>

    <ul class="account-loyalty-sidebar__list account-loyalty-membership__stats">
        <li>
            <span class="account-loyalty-sidebar__label">{{ trans('loyalty::account.current_tier') }}</span>
            <span class="account-loyalty-sidebar__value">{{ $wallet->tier?->translatedName() ?? '—' }}</span>
        </li>
        <li>
            <span class="account-loyalty-sidebar__label">{{ trans('loyalty::account.points_balance') }}</span>
            <span class="account-loyalty-sidebar__value account-loyalty-membership__points">
                {{ number_format($wallet->balance) }}
            </span>
        </li>
        <li>
            <span class="account-loyalty-sidebar__label">{{ trans('loyalty::account.worth') }}</span>
            <span class="account-loyalty-sidebar__value">RM {{ number_format($balanceRm, 2) }}</span>
        </li>
        <li>
            <span class="account-loyalty-sidebar__label">{{ trans('loyalty::account.earn_rate') }}</span>
            <span class="account-loyalty-sidebar__value">
                {{ trans('loyalty::account.earn_rate_value', [
                    'rate' => $earnRate,
                    'multiplier' => $tierMultiplier,
                ]) }}
            </span>
        </li>
    </ul>
</div>
