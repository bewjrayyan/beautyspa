@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('loyalty::reports.title'))

    <li class="active">{{ trans('loyalty::reports.title') }}</li>
@endcomponent

@section('content')
    @php
        $txTypeLabels = trans('loyalty::reports.types');
        $currencySymbol = currency_symbol(setting('default_currency'));
        $totalMembers = max(1, (int) $overview['total_members']);
    @endphp

    <div class="loyalty-admin loyalty-reports">
        <header class="loyalty-page-hero loyalty-page-hero--reports">
            <div class="loyalty-page-hero__main">
                <h1 class="loyalty-page-hero__title">
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    {{ trans('loyalty::reports.title') }}
                </h1>
                <p class="loyalty-page-hero__lead">{{ trans('loyalty::reports.index.lead') }}</p>
                <p class="loyalty-page-hero__period">
                    <i class="fa fa-calendar" aria-hidden="true"></i>
                    {{ trans('loyalty::reports.index.period_label', [
                        'from' => $overview['from']->format('d M Y'),
                        'to' => $overview['to']->format('d M Y'),
                    ]) }}
                </p>
            </div>
            <div class="loyalty-page-hero__actions">
                <a href="{{ route('admin.loyalty.tiers.index') }}" class="btn btn-default loyalty-page-hero__btn">
                    <i class="fa fa-star" aria-hidden="true"></i>
                    {{ trans('loyalty::tiers.tiers') }}
                </a>
                <a href="{{ route('admin.loyalty.members.index') }}" class="btn btn-default loyalty-page-hero__btn">
                    <i class="fa fa-users" aria-hidden="true"></i>
                    {{ trans('loyalty::members.members') }}
                </a>
            </div>
        </header>

        <form method="GET" class="loyalty-reports-filter">
            <div class="loyalty-reports-filter__fields">
                <div class="form-group">
                    <label for="from">{{ trans('loyalty::reports.from') }}</label>
                    <input type="text" name="from" id="from" class="form-control datetime-picker"
                        data-default-date="{{ request('from', $overview['from']->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label for="to">{{ trans('loyalty::reports.to') }}</label>
                    <input type="text" name="to" id="to" class="form-control datetime-picker"
                        data-default-date="{{ request('to', $overview['to']->format('Y-m-d')) }}">
                </div>
                <div class="form-group loyalty-reports-filter__submit">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa fa-filter" aria-hidden="true"></i>
                        {{ trans('loyalty::reports.filter') }}
                    </button>
                </div>
            </div>
        </form>

        <div class="loyalty-page-stats loyalty-page-stats--4">
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--primary">
                    <i class="fa fa-database" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::reports.outstanding_points') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($overview['outstanding_points']) }}</strong>
                    <span class="loyalty-page-stats__hint">
                        ≈ {{ $currencySymbol }} {{ number_format($overview['liability_rm'], 2) }} {{ trans('loyalty::reports.liability') }}
                    </span>
                </div>
            </div>
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--success">
                    <i class="fa fa-users" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::reports.active_members') }}</span>
                    <strong class="loyalty-page-stats__value">
                        {{ number_format($overview['active_members']) }}
                        <span class="loyalty-page-stats__value-sub">/ {{ number_format($overview['total_members']) }}</span>
                    </strong>
                </div>
            </div>
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--earned">
                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::reports.earned_period') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($overview['earned_points']) }}</strong>
                    <span class="loyalty-page-stats__hint">{{ trans('loyalty::reports.index.pts') }}</span>
                </div>
            </div>
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--redeemed">
                    <i class="fa fa-minus-circle" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::reports.redeemed_period') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($overview['redeemed_points']) }}</strong>
                    <span class="loyalty-page-stats__hint">
                        {{ $currencySymbol }} {{ number_format($overview['redeem_discount_rm'], 2) }}
                        · {{ $overview['orders_with_redeem'] }} {{ trans('loyalty::reports.orders') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="loyalty-reports-layout">
            <div class="loyalty-page-card">
                <div class="loyalty-page-card__head">
                    <h2><i class="fa fa-pie-chart" aria-hidden="true"></i> {{ trans('loyalty::reports.tier_breakdown') }}</h2>
                </div>
                <div class="loyalty-page-card__body">
                    @forelse ($overview['tier_breakdown'] as $row)
                        @php
                            $pct = round(($row->members / $totalMembers) * 100, 1);
                        @endphp
                        <div class="loyalty-reports-tier-row">
                            <div class="loyalty-reports-tier-row__label">
                                <span class="loyalty-reports-tier-row__name">{{ $row->name }}</span>
                                <span class="loyalty-reports-tier-row__count">{{ number_format($row->members) }} {{ trans('loyalty::reports.members') }}</span>
                            </div>
                            <div class="loyalty-reports-tier-row__bar" role="presentation">
                                <span class="loyalty-reports-tier-row__fill" style="width: {{ min(100, $pct) }}%"></span>
                            </div>
                            <span class="loyalty-reports-tier-row__pct">{{ $pct }}%</span>
                        </div>
                    @empty
                        <p class="loyalty-reports-empty">{{ trans('loyalty::reports.index.tier_empty') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="loyalty-page-card">
                <div class="loyalty-page-card__head">
                    <h2><i class="fa fa-calendar-check-o" aria-hidden="true"></i> {{ trans('loyalty::reports.period_summary') }}</h2>
                </div>
                <div class="loyalty-page-card__body">
                    <ul class="loyalty-reports-summary">
                        <li>
                            <span class="loyalty-reports-summary__label">{{ trans('loyalty::reports.expired_period') }}</span>
                            <strong>{{ number_format($overview['expired_points']) }} {{ trans('loyalty::reports.index.pts') }}</strong>
                        </li>
                        <li>
                            <span class="loyalty-reports-summary__label">{{ trans('loyalty::reports.bonus_period') }}</span>
                            <strong>{{ number_format($overview['bonus_points']) }} {{ trans('loyalty::reports.index.pts') }}</strong>
                        </li>
                        <li>
                            <span class="loyalty-reports-summary__label">{{ trans('loyalty::reports.expiring_soon') }}</span>
                            <strong>
                                {{ number_format($overview['expiring_soon_points']) }} {{ trans('loyalty::reports.index.pts') }}
                                <small>(≈ {{ $currencySymbol }} {{ number_format($overview['expiring_soon_rm'], 2) }})</small>
                            </strong>
                        </li>
                        <li>
                            <span class="loyalty-reports-summary__label">{{ trans('loyalty::reports.orders_earned') }}</span>
                            <strong>
                                {{ number_format($overview['orders_with_earn']) }}
                                <small>({{ number_format($overview['order_earn_points']) }} {{ trans('loyalty::reports.index.pts') }})</small>
                            </strong>
                        </li>
                    </ul>
                    <p class="loyalty-reports-note">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                        {{ trans('loyalty::reports.point_value_note', ['value' => number_format($overview['point_value_rm'], 2)]) }}
                    </p>
                    <a href="{{ route('admin.reports.index', ['type' => 'loyalty_report']) }}" class="btn btn-default btn-sm">
                        <i class="fa fa-external-link" aria-hidden="true"></i>
                        {{ trans('loyalty::reports.full_transaction_report') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="loyalty-page-card loyalty-page-card--table loyalty-reports-tx">
            <div class="loyalty-page-card__head">
                <div>
                    <h2><i class="fa fa-history" aria-hidden="true"></i> {{ trans('loyalty::reports.recent_transactions') }}</h2>
                </div>
            </div>
            <div class="loyalty-page-card__body loyalty-reports-tx__wrap">
                <table class="table loyalty-reports-tx__table">
                    <thead>
                        <tr>
                            <th>{{ trans('loyalty::reports.date') }}</th>
                            <th>{{ trans('loyalty::reports.customer') }}</th>
                            <th>{{ trans('loyalty::reports.type') }}</th>
                            <th class="text-right">{{ trans('loyalty::reports.points') }}</th>
                            <th class="text-right">{{ trans('loyalty::reports.balance') }}</th>
                            <th>{{ trans('loyalty::reports.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $tx)
                            @php
                                $user = $tx->wallet?->user;
                                $typeLabel = $txTypeLabels[$tx->type] ?? $tx->type;
                            @endphp
                            <tr>
                                <td class="loyalty-reports-tx__date">{{ $tx->created_at?->format('d M Y, H:i') }}</td>
                                <td>
                                    @if ($user && $tx->wallet)
                                        <a href="{{ route('admin.loyalty.members.show', $tx->wallet) }}" class="loyalty-reports-tx__customer">
                                            {{ $user->full_name }}
                                            <small>{{ $user->email }}</small>
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="loyalty-member-tx__badge loyalty-member-tx__badge--{{ $tx->type }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <span class="loyalty-member-tx__points {{ $tx->points >= 0 ? 'loyalty-member-tx__points--positive' : 'loyalty-member-tx__points--negative' }}">
                                        {{ $tx->points > 0 ? '+' : '' }}{{ number_format($tx->points) }}
                                    </span>
                                </td>
                                <td class="text-right loyalty-reports-tx__balance">{{ number_format($tx->balance_after) }}</td>
                                <td class="loyalty-reports-tx__desc">{{ $tx->description ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="loyalty-reports-tx__empty">
                                    <i class="fa fa-inbox" aria-hidden="true"></i>
                                    {{ trans('loyalty::reports.index.no_transactions') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @vite(['modules/Loyalty/Resources/assets/admin/sass/main.scss'])
@endpush
