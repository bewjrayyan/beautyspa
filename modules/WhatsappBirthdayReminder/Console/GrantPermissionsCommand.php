<?php

namespace Modules\WhatsappBirthdayReminder\Console;

use Illuminate\Console\Command;
use Modules\User\Entities\Role;

class GrantPermissionsCommand extends Command
{
    protected $signature = 'whatsapp-birthday:grant-admin-permissions {role? : Role ID (defaults to Admin role)}';

    protected $description = 'Grant WhatsApp Birthday Reminder admin permissions to a role.';


    public function handle(): int
    {
        $role = $this->resolveRole();

        $permissions = [
            'admin.whatsapp_birthday.index' => true,
            'admin.whatsapp_birthday.settings' => true,
            'admin.whatsapp_birthday.send' => true,
        ];

        $role->permissions = array_merge($role->permissions ?? [], $permissions);
        $role->save();

        $this->info("WhatsApp Birthday Reminder permissions granted to role #{$role->id} ({$role->name}).");

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
