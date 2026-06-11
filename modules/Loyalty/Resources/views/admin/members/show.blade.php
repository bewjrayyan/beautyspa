@extends('admin::layout')

@section('title', trans('loyalty::members.member'))

@section('content_header')
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.loyalty.members.index') }}">{{ trans('loyalty::members.members') }}</a></li>
        <li class="active">{{ $member->user?->full_name ?? trans('loyalty::members.member') }}</li>
    </ol>
@endsection

@section('content')
    @php
        $user = $member->user;
        $currencySymbol = currency_symbol(setting('default_currency'));
        $txTypeLabels = trans('loyalty::members.types');
    @endphp

    <div class="loyalty-member-show">
        <header class="loyalty-member-hero">
            <div class="loyalty-member-hero__main">
                @if ($user)
                    <div class="loyalty-member-hero__avatar">
                        @include('user::admin.partials.avatar', [
                            'user' => $user,
                            'class' => 'profile-first-letter',
                        ])
                    </div>
                    <div>
                        <h1 class="loyalty-member-hero__name">{{ $user->full_name }}</h1>
                        <p class="loyalty-member-hero__email">{{ $user->email }}</p>
                        <div class="loyalty-member-hero__meta">
                            @if ($member->tier)
                                <span class="loyalty-member-hero__tier">
                                    <i class="fa fa-star" aria-hidden="true"></i>
                                    {{ $member->tier->translatedName() }}
                                </span>
                            @endif
                            <span class="loyalty-member-hero__wallet-id">
                                {{ trans('loyalty::members.show.wallet_id', ['id' => $member->id]) }}
                            </span>
                        </div>
                    </div>
                @else
                    <div>
                        <h1 class="loyalty-member-hero__name">{{ trans('loyalty::members.member') }}</h1>
                        <p class="loyalty-member-hero__email text-muted">{{ trans('loyalty::members.show.no_customer') }}</p>
                    </div>
                @endif
            </div>

            <div class="loyalty-member-hero__actions">
                @if ($user && auth()->user()?->hasAccess('admin.users.edit'))
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-default btn-sm loyalty-member-hero__link">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        {{ trans('loyalty::members.show.view_customer') }}
                    </a>
                @endif
                <a href="{{ route('admin.loyalty.members.index') }}" class="btn btn-default btn-sm loyalty-member-hero__link">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    {{ trans('loyalty::members.show.back') }}
                </a>
            </div>
        </header>

        <div class="loyalty-member-stats">
            <div class="loyalty-member-stats__stat">
                <span class="loyalty-member-stats__icon loyalty-member-stats__icon--spend">
                    <i class="fa fa-money" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-member-stats__label">{{ trans('loyalty::members.table.lifetime_spend') }}</span>
                    <strong class="loyalty-member-stats__value">{{ $currencySymbol }} {{ number_format($member->lifetime_spend, 2) }}</strong>
                </div>
            </div>
            <div class="loyalty-member-stats__stat">
                <span class="loyalty-member-stats__icon loyalty-member-stats__icon--earned">
                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-member-stats__label">{{ trans('loyalty::members.show.total_earned') }}</span>
                    <strong class="loyalty-member-stats__value">{{ number_format($stats['earned']) }}</strong>
                </div>
            </div>
            <div class="loyalty-member-stats__stat">
                <span class="loyalty-member-stats__icon loyalty-member-stats__icon--redeemed">
                    <i class="fa fa-minus-circle" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-member-stats__label">{{ trans('loyalty::members.show.total_redeemed') }}</span>
                    <strong class="loyalty-member-stats__value">{{ number_format($stats['redeemed']) }}</strong>
                </div>
            </div>
            <div class="loyalty-member-stats__stat">
                <span class="loyalty-member-stats__icon loyalty-member-stats__icon--count">
                    <i class="fa fa-list" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-member-stats__label">{{ trans('loyalty::members.show.tx_count') }}</span>
                    <strong class="loyalty-member-stats__value">{{ number_format($stats['count']) }}</strong>
                </div>
            </div>
        </div>

        <div class="loyalty-member-layout">
            <aside class="loyalty-member-layout__sidebar">
                @include('loyalty::admin.members.partials.membership-card', [
                    'member' => $member,
                    'user' => $user,
                ])

                <div class="loyalty-member-card">
                    <div class="loyalty-member-card__head">
                        <h3><i class="fa fa-info-circle" aria-hidden="true"></i> {{ trans('loyalty::members.show.details_title') }}</h3>
                    </div>
                    <div class="loyalty-member-card__body">
                        <dl class="loyalty-member-card__info-list">
                            <li>
                                <dt>{{ trans('loyalty::members.table.tier') }}</dt>
                                <dd>{{ $member->tier?->name ?? '—' }}</dd>
                            </li>
                            <li>
                                <dt>{{ trans('loyalty::members.show.tier_since') }}</dt>
                                <dd>
                                    @if ($member->tier_assigned_at)
                                        {{ $member->tier_assigned_at->timezone(config('app.timezone'))->format('d M Y') }}
                                    @else
                                        —
                                    @endif
                                </dd>
                            </li>
                            <li>
                                <dt>{{ trans('loyalty::members.show.member_since') }}</dt>
                                <dd>{{ $member->created_at?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</dd>
                            </li>
                            @if ($member->tier?->earn_multiplier)
                                <li>
                                    <dt>{{ trans('loyalty::members.show.earn_multiplier') }}</dt>
                                    <dd>{{ $member->tier->earn_multiplier }}×</dd>
                                </li>
                            @endif
                        </dl>
                    </div>
                </div>

                @hasAccess('admin.loyalty.members.adjust')
                    <div class="loyalty-member-card loyalty-member-card--adjust">
                        <div class="loyalty-member-card__head">
                            <h3><i class="fa fa-sliders" aria-hidden="true"></i> {{ trans('loyalty::members.adjust.title') }}</h3>
                            <p>{{ trans('loyalty::members.show.adjust_lead') }}</p>
                        </div>
                        <div class="loyalty-member-card__balance">
                            <span class="loyalty-member-card__balance-icon" aria-hidden="true">
                                <i class="fa fa-database"></i>
                            </span>
                            <div>
                                <span class="loyalty-member-card__balance-label">
                                    {{ trans('loyalty::members.table.balance') }}
                                </span>
                                <strong class="loyalty-member-card__balance-value">
                                    {{ number_format($member->balance) }}
                                </strong>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.loyalty.members.adjust', $member) }}">
                            {{ csrf_field() }}
                            <div class="loyalty-member-card__body">
                                <div class="form-group">
                                    <label for="points">{{ trans('loyalty::members.adjust.points') }}</label>
                                    <input
                                        type="number"
                                        name="points"
                                        id="points"
                                        class="form-control"
                                        placeholder="0"
                                        required
                                    >
                                </div>
                                <div class="form-group">
                                    <label for="description">{{ trans('loyalty::members.adjust.description') }}</label>
                                    <input
                                        type="text"
                                        name="description"
                                        id="description"
                                        class="form-control"
                                        placeholder="{{ trans('loyalty::members.show.adjust_placeholder') }}"
                                        required
                                    >
                                </div>
                            </div>
                            <div class="loyalty-member-card__footer">
                                <button type="submit" class="btn btn-primary" data-loading>
                                    <i class="fa fa-check" aria-hidden="true"></i>
                                    {{ trans('loyalty::members.adjust.submit') }}
                                </button>
                            </div>
                        </form>
                    </div>
                @endHasAccess
            </aside>

            <div class="loyalty-member-layout__main">
                @include('loyalty::admin.members.partials.stamp-cards', [
                    'stampData' => $stampData,
                ])

                @include('loyalty::admin.members.partials.purchase-analytics', [
                    'purchaseAnalytics' => $purchaseAnalytics,
                ])

                @include('loyalty::admin.members.partials.orders-history', [
                    'memberOrders' => $memberOrders,
                ])

                <div class="loyalty-member-card loyalty-member-tx">
                    <div class="loyalty-member-card__head">
                        <h3><i class="fa fa-history" aria-hidden="true"></i> {{ trans('loyalty::members.transactions') }}</h3>
                        <p>{{ trans('loyalty::members.show.transactions_lead') }}</p>
                    </div>
                    <div class="loyalty-member-card__body loyalty-member-tx__table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ trans('loyalty::reports.date') }}</th>
                                    <th>{{ trans('loyalty::reports.type') }}</th>
                                    <th class="text-right">{{ trans('loyalty::reports.points') }}</th>
                                    <th class="text-right">{{ trans('loyalty::reports.balance') }}</th>
                                    <th>{{ trans('loyalty::reports.description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($member->transactions as $tx)
                                    <tr>
                                        <td class="text-nowrap">
                                            {{ $tx->created_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}
                                        </td>
                                        <td>
                                            <span class="loyalty-member-tx__badge loyalty-member-tx__badge--{{ $tx->type }}">
                                                {{ is_array($txTypeLabels) ? ($txTypeLabels[$tx->type] ?? ucfirst($tx->type)) : ucfirst($tx->type) }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <span class="loyalty-member-tx__points {{ $tx->points >= 0 ? 'loyalty-member-tx__points--positive' : 'loyalty-member-tx__points--negative' }}">
                                                {{ $tx->points > 0 ? '+' : '' }}{{ number_format($tx->points) }}
                                            </span>
                                        </td>
                                        <td class="text-right">{{ number_format($tx->balance_after) }}</td>
                                        <td class="loyalty-member-tx__desc">{{ $tx->description ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="loyalty-member-tx__empty">
                                            <i class="fa fa-inbox" aria-hidden="true"></i>
                                            {{ trans('loyalty::members.show.no_transactions') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/Loyalty/Resources/assets/admin/sass/main.scss',
        'modules/Loyalty/Resources/assets/admin/js/main.js',
    ])
@endpush
