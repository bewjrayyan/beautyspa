<?php

namespace Modules\Lead\Console;

use Illuminate\Console\Command;
use Modules\User\Entities\Role;

class GrantLeadPermissionsCommand extends Command
{
    protected $signature = 'lead:grant-admin-permissions {role? : Role ID (defaults to Admin role)}';

    protected $description = 'Grant Lead / Central Management admin permissions to a role.';

    public function handle(): int
    {
        $role = $this->resolveRole();

        $permissions = [
            'admin.leads.index' => true,
            'admin.leads.show' => true,
            'admin.leads.create' => true,
            'admin.leads.edit' => true,
            'admin.leads.destroy' => true,
        ];

        $role->permissions = array_merge($role->permissions ?? [], $permissions);
        $role->save();

        $this->info("Lead workspace permissions granted to role #{$role->id} ({$role->name}).");

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
