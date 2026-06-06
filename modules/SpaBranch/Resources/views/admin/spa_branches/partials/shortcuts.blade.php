@if (request()->routeIs('admin.spa_branches.create'))
    @push('shortcuts')
        <li>
            <span>Ctrl</span>
            <span>Alt</span>
            <span>N</span>
            <span>{{ trans('admin::admin.shortcuts.create_new_resource') }}</span>
        </li>
    @endpush
@elseif (request()->routeIs('admin.spa_branches.edit'))
    @push('shortcuts')
        <li>
            <span>Ctrl</span>
            <span>Alt</span>
            <span>R</span>
            <span>{{ trans('admin::admin.shortcuts.go_back_to_index') }}</span>
        </li>
    @endpush
@endif
