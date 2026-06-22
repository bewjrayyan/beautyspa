@php
    $cardClass = $cardClass ?? 'loyalty-stamp-card';
    $showRedeemActions = $showRedeemActions ?? false;
    $checkIconClass = $checkIconClass ?? 'las la-check';
    $clockIconClass = $clockIconClass ?? 'las la-clock';
    $giftIconClass = $giftIconClass ?? 'las la-gift';
@endphp

<article @class([
    $cardClass,
    $cardClass . '--complete' => $card['is_complete'] ?? false,
    $cardClass . '--not-started' => $card['not_started'] ?? false,
    $cardClass . '--expired' => $card['is_expired'] ?? false,
])>
    <div class="{{ $cardClass }}__header">
        <p class="{{ $cardClass }}__title">{{ $card['name'] }}</p>
        @if (! empty($card['reward_description']))
            <p class="{{ $cardClass }}__reward">{{ $card['reward_description'] }}</p>
        @endif
    </div>

    <div class="{{ $cardClass }}__stamps" aria-hidden="true">
        @for ($i = 1; $i <= $card['stamps_required']; $i++)
            <span @class([
                $cardClass . '__stamp',
                $cardClass . '__stamp--filled' => $i <= $card['stamps_earned'],
            ])>
                @if ($i <= $card['stamps_earned'])
                    <i class="{{ $checkIconClass }}" aria-hidden="true"></i>
                @endif
            </span>
        @endfor
    </div>

    <div class="{{ $cardClass }}__footer">
        <span class="{{ $cardClass }}__progress">
            @if ($card['can_redeem'] ?? false)
                {{ trans('loyalty::order_rewards.stamps_complete') }}
            @elseif (($card['not_started'] ?? false) && ($card['stamps_earned'] ?? 0) === 0)
                {{ trans('loyalty::account.stamp_not_started', ['required' => $card['stamps_required']]) }}
            @else
                {{ trans('loyalty::order_rewards.stamps_progress', [
                    'earned' => $card['stamps_earned'],
                    'required' => $card['stamps_required'],
                ]) }}
            @endif
        </span>

        @if ($card['is_expired'] ?? false)
            <span class="{{ $cardClass }}__expiry {{ $cardClass }}__expiry--expired">
                <i class="{{ $clockIconClass }}" aria-hidden="true"></i>
                {{ trans('loyalty::order_rewards.expired') }}
            </span>
        @elseif (! ($card['can_redeem'] ?? false) && ($card['days_until_expiry'] ?? null) !== null)
            <span class="{{ $cardClass }}__expiry">
                <i class="{{ $clockIconClass }}" aria-hidden="true"></i>
                @if ($card['days_until_expiry'] === 0)
                    {{ trans('loyalty::order_rewards.expires_today') }}
                @elseif ($card['days_until_expiry'] === 1)
                    {{ trans('loyalty::order_rewards.expires_tomorrow') }}
                @else
                    {{ trans('loyalty::order_rewards.expires_in_days', ['days' => $card['days_until_expiry']]) }}
                @endif
            </span>
        @endif
    </div>

    @if ($showRedeemActions && ($card['can_redeem'] ?? false) && ! empty($card['wallet_id']))
        <div class="{{ $cardClass }}__actions">
            <form
                method="POST"
                action="{{ route('account.loyalty.stamp_cards.redeem', $card['wallet_id']) }}"
                class="{{ $cardClass }}__redeem-form"
                onsubmit="return confirm(@js(trans('loyalty::account.stamp_redeem_confirm')));"
            >
                @csrf
                <button type="submit" class="btn btn-primary btn-sm {{ $cardClass }}__redeem-btn">
                    <i class="{{ $giftIconClass }}" aria-hidden="true"></i>
                    {{ trans('loyalty::account.stamp_redeem_button') }}
                </button>
            </form>
            <p class="{{ $cardClass }}__redeem-hint">{{ trans('loyalty::account.stamp_redeem_hint') }}</p>
        </div>
    @elseif (! $showRedeemActions && ($card['can_redeem'] ?? false))
        <div class="{{ $cardClass }}__actions">
            <a href="{{ route('account.loyalty.index') }}#stamp-cards" class="{{ $cardClass }}__redeem-link">
                <i class="{{ $giftIconClass }}" aria-hidden="true"></i>
                {{ trans('loyalty::account.stamp_redeem_in_account') }}
            </a>
        </div>
    @endif
</article>
