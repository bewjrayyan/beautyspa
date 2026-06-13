<div class="accordion-content clearfix admin-profile-layout">
    <div class="admin-profile-layout__sidebar">
        <div class="admin-profile-sidebar">
            @if (request()->routeIs('admin.users.create', 'admin.users.edit'))
                @include('user::admin.partials.profile-photo-sidebar', [
                    'accountUser' => $profileUser ?? ($user ?? null),
                ])
            @endif

            @if (request()->routeIs('admin.users.create'))
                @include('user::admin.users.partials.create-sidebar')
            @elseif (request()->routeIs('admin.users.edit'))
                @include('user::admin.partials.account-info-sidebar', [
                    'accountUser' => $profileUser ?? ($user ?? null),
                    'showAdminFields' => true,
                ])
            @elseif ($profileUser ?? null)
                @include('user::admin.partials.account-info-sidebar', [
                    'accountUser' => $profileUser,
                    'showAdminFields' => $showAdminFields ?? request()->routeIs('admin.users.edit'),
                ])
            @endif

            @if (! request()->routeIs('admin.users.create') && app('modules')->isEnabled('Loyalty') && ($profileUser ?? null))
                @include('user::admin.profile.partials.loyalty-sidebar', [
                    'profileUser' => $profileUser,
                    'loyaltyWallet' => $loyaltyWallet ?? null,
                ])
            @endif

            @if (request()->routeIs('admin.users.edit'))
                @include('user::admin.users.partials.edit-sidebar', [
                    'user' => $profileUser ?? ($user ?? null),
                ])
            @endif
        </div>
    </div>

    <div class="admin-profile-layout__main">
        <div class="accordion-box-content admin-profile-main-panel">
            <div class="admin-profile-main-tabs accordion-box">
                @foreach ($groups as $group => $options)
                    <ul class="accordion-tab nav nav-tabs admin-profile-main-tabs__list" role="tablist">
                        {{ $tabs->group($group)->navs() }}
                    </ul>
                @endforeach
            </div>

            <div class="tab-content clearfix">
                {{ $contents }}

                @include('admin::form.footer')
            </div>
        </div>
    </div>
</div>
