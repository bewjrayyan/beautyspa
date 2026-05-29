<div class="accordion-content clearfix admin-profile-layout">
    <div class="admin-profile-layout__sidebar">
        <div class="admin-profile-sidebar">
            <div class="accordion-box">
                <div class="panel-group" id="{{ $name }}">
                    @foreach ($groups as $group => $options)
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a
                                        @if (count($groups) > 1)
                                            class="{{ ($options['active'] ?? false) ? '' : 'collapsed' }} {{ $tabs->group($group)->hasError() ? 'has-error' : '' }}"
                                            data-toggle="collapse"
                                            data-parent="#{{ $name }}"
                                            href="#{{ $group }}"
                                        @endif
                                    >
                                        {{ $options['title'] }}

                                        @if ($tabs->group($group)->hasError())
                                            <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                                        @endif
                                    </a>
                                </h4>
                            </div>

                            <div id="{{ $group }}" class="panel-collapse collapse {{ ($options['active'] ?? false) ? 'in' : '' }}">
                                <div class="panel-body">
                                    <ul class="accordion-tab nav nav-tabs">
                                        {{ $tabs->group($group)->navs() }}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($profileUser ?? null)
                @include('user::admin.partials.account-info-sidebar', [
                    'accountUser' => $profileUser,
                    'showAdminFields' => $showAdminFields ?? request()->routeIs('admin.users.edit'),
                ])
            @endif

            @if (app('modules')->isEnabled('Loyalty') && ($profileUser ?? null))
                @include('user::admin.profile.partials.loyalty-sidebar', [
                    'profileUser' => $profileUser,
                    'loyaltyWallet' => $loyaltyWallet ?? null,
                ])
            @endif
        </div>
    </div>

    <div class="admin-profile-layout__main">
        <div class="accordion-box-content">
            <div class="tab-content clearfix">
                {{ $contents }}

                @include('admin::form.footer')
            </div>
        </div>
    </div>
</div>
