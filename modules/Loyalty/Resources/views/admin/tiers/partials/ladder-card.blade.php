@php
    $benefitLines = $tier->benefitLines(2);
    $memberPct = $totalMembers > 0
        ? round(($tier->wallets_count / $totalMembers) * 100, 1)
        : 0;
@endphp

<a
    href="{{ route('admin.loyalty.tiers.edit', $tier) }}"
    class="loyalty-tier-card loyalty-tier-card--{{ $tier->slugThemeClass() }} {{ $tier->is_active ? '' : 'loyalty-tier-card--inactive' }}"
>
    <span class="loyalty-tier-card__step" aria-hidden="true">{{ $step }}</span>

    <span class="loyalty-tier-card__badge">
        <i class="fa fa-star" aria-hidden="true"></i>
        {{ $tier->earn_multiplier }}×
    </span>

    <h3 class="loyalty-tier-card__name">{{ $tier->translatedName() }}</h3>

    <p class="loyalty-tier-card__threshold">
        <i class="fa fa-level-up" aria-hidden="true"></i>
        {{ trans('loyalty::tiers.index.from_spend', [
            'amount' => $currencySymbol . ' ' . number_format($tier->min_lifetime_spend, 0),
        ]) }}
    </p>

    <ul class="loyalty-tier-card__meta">
        <li>
            <span>{{ trans('loyalty::reports.members') }}</span>
            <strong>{{ trans('loyalty::tiers.index.members_count', ['count' => number_format($tier->wallets_count)]) }}</strong>
        </li>
        <li>
            <span>{{ trans('loyalty::tiers.index.share_label') }}</span>
            <strong>{{ $memberPct }}%</strong>
        </li>
    </ul>

    @if ($benefitLines !== [])
        <ul class="loyalty-tier-card__benefits">
            @foreach ($benefitLines as $benefit)
                <li>{{ $benefit }}</li>
            @endforeach
        </ul>
    @endif

    @unless ($tier->is_active)
        <span class="loyalty-tier-card__status">{{ trans('loyalty::tiers.index.inactive') }}</span>
    @endunless
</a>
