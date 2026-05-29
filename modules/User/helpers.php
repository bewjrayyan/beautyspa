<?php

if (! function_exists('permission_module_label')) {
    /**
     * Module title on the role permissions tab (keys may contain dots; avoid trans dot nesting).
     */
    function permission_module_label(string $module): string
    {
        $modules = trans('user::permissions.modules');

        if (is_array($modules) && array_key_exists($module, $modules)) {
            return $modules[$module];
        }

        return $module;
    }
}

if (! function_exists('permission_group_label')) {
    /**
     * Permission group title (e.g. admin.gift_voucher_submissions).
     */
    function permission_group_label(string $group): string
    {
        $groups = trans('user::permissions.groups');

        if (is_array($groups) && array_key_exists($group, $groups)) {
            return $groups[$group];
        }

        return $group;
    }
}

if (!function_exists('permission_value')) {
    /**
     * Get the integer representation value of the permission.
     *
     * @param array $permissions
     * @param string $permission
     *
     * @return int
     */
    function permission_value(array $permissions, $permission)
    {
        $value = array_get($permissions, $permission);

        if (is_null($value)) {
            return 0;
        } else if ($value) {
            return 1;
        } else if (!$value) {
            return -1;
        }
    }
}
