<?php

namespace AestheticCart\Console\Commands;

use AestheticCart\Install\AdminPermissions;
use Illuminate\Console\Command;
use Modules\User\Entities\Role;

class SyncAdminModulePermissionsCommand extends Command
{
    protected $signature = 'admin:sync-module-permissions {role? : Role ID (defaults to Admin role)}';

    protected $description = 'Merge all module admin permissions into a role (use after adding new modules).';

    public function handle(): int
    {
        $role = $this->resolveRole();
        $granted = AdminPermissions::allGranted();
        $before = count(array_filter($role->permissions ?? []));
        $role->permissions = array_merge($role->permissions ?? [], $granted);
        $role->save();
        $after = count(array_filter($role->permissions ?? []));

        $this->info("Role #{$role->id} ({$role->name}): permissions updated ({$before} → {$after} granted keys).");

        return self::SUCCESS;
    }

    private function resolveRole(): Role
    {
        $roleId = $this->argument('role');

        if ($roleId !== null && $roleId !== '') {
            return Role::findOrFail($roleId);
        }

        return Role::whereTranslation('name', 'Admin')->first()
            ?? Role::query()->orderBy('id')->firstOrFail();
    }
}
