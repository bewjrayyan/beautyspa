<?php

namespace Modules\Beautician\Console;

use Illuminate\Console\Command;
use Modules\User\Entities\Role;

class GrantBeauticianPermissionsCommand extends Command
{
    protected $signature = 'beautician:grant-admin-permissions {role? : Role ID (defaults to Admin role)}';

    protected $description = 'Grant beautician admin permissions to a role.';

    public function handle(): int
    {
        $role = $this->resolveRole();

        $permissions = [
            'admin.beauticians.index' => true,
            'admin.beauticians.create' => true,
            'admin.beauticians.edit' => true,
            'admin.beauticians.destroy' => true,
        ];

        $role->permissions = array_merge($role->permissions ?? [], $permissions);
        $role->save();

        $this->info("Beautician permissions granted to role #{$role->id} ({$role->name}).");

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
