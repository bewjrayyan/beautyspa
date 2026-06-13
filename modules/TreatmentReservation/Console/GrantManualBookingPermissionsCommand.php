<?php

namespace Modules\TreatmentReservation\Console;

use Illuminate\Console\Command;
use Modules\User\Entities\Role;

class GrantManualBookingPermissionsCommand extends Command
{
    protected $signature = 'treatment-reservation:grant-create-permission
                            {role? : Role ID (defaults to Admin or Beautician role)}
                            {--portal : Grant beautician portal create permission}';

    protected $description = 'Grant manual appointment create permission to a role.';

    public function handle(): int
    {
        $role = $this->resolveRole((bool) $this->option('portal'));

        $permission = $this->option('portal')
            ? 'admin.treatment_reservations.portal.create'
            : 'admin.treatment_reservations.create';

        $role->permissions = array_merge($role->permissions ?? [], [
            $permission => true,
        ]);
        $role->save();

        $this->info("Granted {$permission} to role #{$role->id} ({$role->name}).");

        return self::SUCCESS;
    }


    private function resolveRole(bool $portal): Role
    {
        $roleId = $this->argument('role');

        if ($roleId !== null && $roleId !== '') {
            return Role::findOrFail($roleId);
        }

        $defaultName = $portal ? 'Beautician' : 'Admin';

        return Role::whereTranslation('name', $defaultName)->first()
            ?? Role::query()->orderBy('id')->firstOrFail();
    }
}
