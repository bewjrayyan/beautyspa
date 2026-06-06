<?php

namespace Modules\SpaBranch\Console;

use Illuminate\Console\Command;
use Modules\User\Entities\Role;

class GrantSpaBranchPermissionsCommand extends Command
{
    protected $signature = 'spabranch:grant-admin-permissions {role=1}';

    protected $description = 'Grant spa branch admin permissions to a role.';


    public function handle(): int
    {
        $role = Role::findOrFail($this->argument('role'));

        $permissions = [
            'admin.spa_branches.index' => true,
            'admin.spa_branches.create' => true,
            'admin.spa_branches.edit' => true,
            'admin.spa_branches.destroy' => true,
        ];

        $role->permissions = array_merge($role->permissions ?? [], $permissions);
        $role->save();

        $this->info("Spa branch permissions granted to role #{$role->id} ({$role->name}).");

        return self::SUCCESS;
    }
}
