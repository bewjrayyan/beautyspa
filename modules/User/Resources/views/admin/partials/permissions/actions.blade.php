<div class="permission-row">
    <span class="permission-label">{{ trans($permissionLabel) }}</span>

    <div class="permission-segment" role="radiogroup" aria-label="{{ trans($permissionLabel) }}">
        @php
            $permissionValue = old('permissions')["{$group}.{$permissionAction}"] ?? (
                is_null($entity)
                    ? 0
                    : permission_value($entity->permissions ?: [], "{$group}.{$permissionAction}")
            );
        @endphp

        <input
            type="radio"
            value="0"
            id="{{ "{$group}-{$permissionAction}" }}-inherit"
            name="permissions[{{ "{$group}.{$permissionAction}" }}]"
            class="permission-inherit"
            {{ (int) $permissionValue === 0 ? 'checked' : '' }}
        >
        <label
            for="{{ "{$group}-{$permissionAction}" }}-inherit"
            class="permission-segment__option permission-segment__option--inherit"
        >
            <span class="permission-segment__text">{{ trans('user::roles.permissions.inherit') }}</span>
        </label>

        <input
            type="radio"
            value="-1"
            id="{{ "{$group}-{$permissionAction}" }}-deny"
            name="permissions[{{ "{$group}.{$permissionAction}" }}]"
            class="permission-deny"
            {{ (int) $permissionValue === -1 ? 'checked' : '' }}
        >
        <label
            for="{{ "{$group}-{$permissionAction}" }}-deny"
            class="permission-segment__option permission-segment__option--deny"
        >
            <span class="permission-segment__text">{{ trans('user::roles.permissions.deny') }}</span>
        </label>

        <input
            type="radio"
            value="1"
            id="{{ "{$group}-{$permissionAction}" }}-allow"
            name="permissions[{{ "{$group}.{$permissionAction}" }}]"
            class="permission-allow"
            {{ (int) $permissionValue === 1 ? 'checked' : '' }}
        >
        <label
            for="{{ "{$group}-{$permissionAction}" }}-allow"
            class="permission-segment__option permission-segment__option--allow"
        >
            <span class="permission-segment__text">{{ trans('user::roles.permissions.allow') }}</span>
        </label>
    </div>
</div>
