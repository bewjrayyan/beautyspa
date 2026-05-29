@php
    use Modules\Admin\Support\AdminLang;

    $membersIndexUrl = route('admin.loyalty.members.index');
@endphp

<div class="dashboard-panel dashboard-members">
    <div class="grid-header dashboard-members__head">
        <h5>
            <i class="fa fa-star" aria-hidden="true"></i>
            {{ AdminLang::get('dashboard.members_section') }}
        </h5>
        <a href="{{ $membersIndexUrl }}" class="dashboard-members__view-all">
            {{ AdminLang::get('dashboard.members_view_all') }}
            <i class="fa fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>

    <form
        class="dashboard-members__search"
        method="GET"
        action="{{ $membersIndexUrl }}"
    >
        <div class="dashboard-members__search-field">
            <i class="fa fa-search" aria-hidden="true"></i>
            <input
                type="search"
                name="search"
                class="form-control input-sm"
                placeholder="{{ AdminLang::get('dashboard.members_search_placeholder') }}"
                autocomplete="off"
            >
        </div>
        <button type="submit" class="btn btn-primary btn-sm">
            {{ AdminLang::get('dashboard.members_search') }}
        </button>
    </form>

    <div class="table-responsive anchor-table">
        <table class="table dashboard-members__table">
            <thead>
                <tr>
                    <th>{{ AdminLang::get('dashboard.table.members.member') }}</th>
                    <th>{{ AdminLang::get('dashboard.table.members.tier') }}</th>
                    <th class="text-right">{{ AdminLang::get('dashboard.table.members.points') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentLoyaltyMembers as $wallet)
                    @php
                        $memberUser = $wallet->user;
                        $memberName = $memberUser
                            ? trim($memberUser->first_name . ' ' . $memberUser->last_name)
                            : '—';
                        $memberSub = $memberUser?->phone ?: $memberUser?->email ?: '—';
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('admin.loyalty.members.show', $wallet) }}" class="dashboard-members__name">
                                <strong>{{ $memberName }}</strong>
                                <small>{{ $memberSub }}</small>
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.loyalty.members.show', $wallet) }}">
                                {{ $wallet->tier?->translatedName() ?? '—' }}
                            </a>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.loyalty.members.show', $wallet) }}">
                                <strong>{{ number_format($wallet->balance) }}</strong>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="empty" colspan="3">{{ trans('admin::dashboard.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
