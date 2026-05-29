@php
    $currencySymbol = currency_symbol(setting('default_currency'));
    $canViewMember = auth()->user()?->hasAccess('admin.loyalty.members.show');
    $canViewMembers = auth()->user()?->hasAccess('admin.loyalty.members.index');
@endphp

<aside class="admin-profile-loyalty-sidebar">
    <div class="admin-profile-loyalty-sidebar__head">
        <h3>
            <i class="fa fa-star" aria-hidden="true"></i>
            {{ trans('user::users.profile_page.loyalty_title') }}
        </h3>
    </div>

    @if ($loyaltyWallet)
        <div class="admin-profile-loyalty-sidebar__card-wrap">
            @include('loyalty::admin.members.partials.membership-card', [
                'member' => $loyaltyWallet,
                'user' => $profileUser,
                'compact' => true,
            ])
        </div>

        <div class="admin-profile-loyalty-sidebar__info">
            <dl class="admin-profile-loyalty-sidebar__stats">
                <div class="admin-profile-loyalty-sidebar__stat">
                    <dt>{{ trans('user::users.profile_page.loyalty_tier') }}</dt>
                    <dd>{{ $loyaltyWallet->tier?->translatedName() ?? '—' }}</dd>
                </div>
                <div class="admin-profile-loyalty-sidebar__stat">
                    <dt>{{ trans('user::users.profile_page.loyalty_balance') }}</dt>
                    <dd>
                        {{ number_format($loyaltyWallet->balance) }}
                        <span class="admin-profile-loyalty-sidebar__unit">{{ trans('loyalty::members.points_unit') }}</span>
                    </dd>
                </div>
                <div class="admin-profile-loyalty-sidebar__stat">
                    <dt>{{ trans('user::users.profile_page.loyalty_lifetime_spend') }}</dt>
                    <dd>{{ $currencySymbol }}&nbsp;{{ number_format($loyaltyWallet->lifetime_spend, 2) }}</dd>
                </div>
                @if ($profileUser->referral_code)
                    <div class="admin-profile-loyalty-sidebar__stat">
                        <dt>{{ trans('user::users.profile_page.loyalty_referral_code') }}</dt>
                        <dd><code>{{ $profileUser->referral_code }}</code></dd>
                    </div>
                @endif
            </dl>
        </div>

        @if ($canViewMember)
            <a
                href="{{ route('admin.loyalty.members.show', $loyaltyWallet) }}"
                class="btn btn-primary btn-block admin-profile-loyalty-sidebar__btn"
            >
                <i class="fa fa-external-link" aria-hidden="true"></i>
                {{ trans('user::users.profile_page.loyalty_view_member') }}
            </a>
        @endif
    @else
        <div class="admin-profile-loyalty-sidebar__empty">
            <p>{{ trans('user::users.profile_page.loyalty_empty') }}</p>

            @if ($canViewMembers)
                <a
                    href="{{ route('admin.loyalty.members.index') }}"
                    class="btn btn-default btn-block admin-profile-loyalty-sidebar__btn"
                >
                    <i class="fa fa-users" aria-hidden="true"></i>
                    {{ trans('user::users.profile_page.loyalty_browse_members') }}
                </a>
            @endif
        </div>
    @endif
</aside>
