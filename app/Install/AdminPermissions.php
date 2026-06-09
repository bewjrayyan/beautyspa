<?php

namespace AestheticCart\Install;

class AdminPermissions
{
    /**
     * @return array<string, bool>
     */
    public static function allGranted(): array
    {
        $permissions = self::legacyPermissions();

        foreach (glob(base_path('modules/*/Config/permissions.php')) ?: [] as $file) {
            $config = require $file;

            if (! is_array($config)) {
                continue;
            }

            foreach ($config as $namespace => $actions) {
                if (! is_array($actions)) {
                    continue;
                }

                foreach (array_keys($actions) as $action) {
                    $permissions["{$namespace}.{$action}"] = true;
                }
            }
        }

        return $permissions;
    }

    /**
     * @return array<string, bool>
     */
    private static function legacyPermissions(): array
    {
        return (new AdminAccountLegacyPermissions())->all();
    }
}
