<?php

namespace Modules\Shipping\Console;

use Illuminate\Console\Command;
use Modules\User\Entities\Role;

class GrantShippingClassPermissionsCommand extends Command
{
    protected $signature = 'shipping:grant-admin-permissions {role? : Role ID (defaults to Admin role)}';

    protected $description = 'Grant shipping class admin permissions to a role.';

    public function handle(): int
    {
        $role = $this->resolveRole();

        $permissions = [
            'admin.shipping_classes.index' => true,
            'admin.shipping_classes.create' => true,
            'admin.shipping_classes.edit' => true,
            'admin.shipping_classes.destroy' => true,
        ];

        $role->permissions = array_merge($role->permissions ?? [], $permissions);
        $role->save();

        $this->info("Shipping class permissions granted to role #{$role->id} ({$role->name}).");

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
