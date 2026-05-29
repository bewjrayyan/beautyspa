@php
    $totalPermissions = 0;

    foreach ($permissions as $modulePermissions) {
        foreach ($modulePermissions as $groupPermissions) {
            $totalPermissions += count($groupPermissions);
        }
    }
@endphp

<div class="permissions-manager">
    <div class="permissions-toolbar">
        <div class="permissions-toolbar__search">
            <i class="fa fa-search" aria-hidden="true"></i>
            <input
                type="search"
                class="form-control permissions-search-input"
                placeholder="{{ trans('user::roles.permissions_ui.search_placeholder') }}"
                autocomplete="off"
            >
        </div>

        <div class="permissions-toolbar__actions">
            <button type="button" class="permissions-btn permissions-btn--ghost permissions-expand-all">
                {{ trans('user::roles.permissions_ui.expand_all') }}
            </button>
            <button type="button" class="permissions-btn permissions-btn--ghost permissions-collapse-all">
                {{ trans('user::roles.permissions_ui.collapse_all') }}
            </button>
            <span class="permissions-toolbar__divider" aria-hidden="true"></span>
            <div class="permission-parent-actions permissions-bulk">
                <button type="button" class="permissions-btn permissions-btn--allow allow-all">
                    {{ trans('user::roles.permissions.allow_all') }}
                </button>
                <button type="button" class="permissions-btn permissions-btn--deny deny-all">
                    {{ trans('user::roles.permissions.deny_all') }}
                </button>
                <button type="button" class="permissions-btn permissions-btn--inherit inherit-all">
                    {{ trans('user::roles.permissions.inherit_all') }}
                </button>
            </div>
        </div>
    </div>

    <p class="permissions-lead">
        {{ trans('user::roles.permissions_ui.lead', ['count' => $totalPermissions]) }}
    </p>

    <div class="permissions-table-head" aria-hidden="true">
        <span class="permissions-table-head__label">{{ trans('user::roles.permissions_ui.column_permission') }}</span>
        <span class="permissions-table-head__choices">
            <span>{{ trans('user::roles.permissions.inherit') }}</span>
            <span>{{ trans('user::roles.permissions.deny') }}</span>
            <span>{{ trans('user::roles.permissions.allow') }}</span>
        </span>
    </div>

    <div class="permissions-modules">
        @foreach ($permissions as $module => $modulePermissions)
            @php
                $modulePermissionCount = 0;

                foreach ($modulePermissions as $groupPermissions) {
                    $modulePermissionCount += count($groupPermissions);
                }

                $moduleSlug = \Illuminate\Support\Str::slug($module);
            @endphp

            <article
                class="permission-module is-open"
                data-module="{{ $moduleSlug }}"
                data-module-label="{{ strtolower(permission_module_label($module)) }}"
            >
                <header class="permission-module__header">
                    <button
                        type="button"
                        class="permission-module__toggle"
                        aria-expanded="true"
                        aria-controls="permission-module-{{ $moduleSlug }}"
                    >
                        <i class="fa fa-chevron-right" aria-hidden="true"></i>
                    </button>

                    <div class="permission-module__title-wrap">
                        <h3 class="permission-module__title">{{ permission_module_label($module) }}</h3>
                        <span class="permission-module__meta">
                            {{ trans('user::roles.permissions_ui.permission_count', ['count' => $modulePermissionCount]) }}
                        </span>
                    </div>

                    <span class="permission-module__badge">{{ $modulePermissionCount }}</span>
                </header>

                <div class="permission-module__body" id="permission-module-{{ $moduleSlug }}">
                    @foreach ($modulePermissions as $group => $groupPermissions)
                        <section class="permission-group" data-group="{{ $group }}">
                            <div class="permission-group__header">
                                <h4 class="permission-group__title">{{ permission_group_label($group) }}</h4>

                                <div class="permission-group-actions permissions-bulk">
                                    <button type="button" class="permissions-btn permissions-btn--sm permissions-btn--allow allow-all">
                                        {{ trans('user::roles.permissions.allow_all') }}
                                    </button>
                                    <button type="button" class="permissions-btn permissions-btn--sm permissions-btn--deny deny-all">
                                        {{ trans('user::roles.permissions.deny_all') }}
                                    </button>
                                    <button type="button" class="permissions-btn permissions-btn--sm permissions-btn--inherit inherit-all">
                                        {{ trans('user::roles.permissions.inherit_all') }}
                                    </button>
                                </div>
                            </div>

                            <div class="permission-group__rows">
                                @foreach ($groupPermissions as $permissionAction => $permissionLabel)
                                    @include('user::admin.partials.permissions.actions')
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </article>
        @endforeach

        <p class="permissions-empty" hidden>
            {{ trans('user::roles.permissions_ui.no_results') }}
        </p>
    </div>
</div>
