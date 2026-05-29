<?php

namespace Modules\Loyalty\Console;

use Illuminate\Console\Command;
use Modules\User\Entities\Role;

class GrantLoyaltyPermissionsCommand extends Command
{
    protected $signature = 'loyalty:grant-admin-permissions {role=1}';

    protected $description = 'Grant loyalty admin permissions to a role.';


    public function handle(): int
    {
        $role = Role::findOrFail($this->argument('role'));

        $permissions = [
            'admin.loyalty.tiers.index' => true,
            'admin.loyalty.tiers.create' => true,
            'admin.loyalty.tiers.edit' => true,
            'admin.loyalty.tiers.destroy' => true,
            'admin.loyalty.members.index' => true,
            'admin.loyalty.members.show' => true,
            'admin.loyalty.members.adjust' => true,
            'admin.loyalty.reports.index' => true,
        ];

        $role->permissions = array_merge($role->permissions ?? [], $permissions);
        $role->save();

        $this->info("Loyalty permissions granted to role #{$role->id} ({$role->name}).");

        return self::SUCCESS;
    }
}
