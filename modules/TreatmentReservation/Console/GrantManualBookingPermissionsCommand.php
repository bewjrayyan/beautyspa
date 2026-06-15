<?php

namespace Modules\TreatmentReservation\Console;

use Illuminate\Console\Command;
use Modules\User\Entities\Role;

class GrantManualBookingPermissionsCommand extends Command
{
    protected $signature = 'treatment-reservation:grant-admin-permissions
                            {role? : Role ID (defaults to Admin role)}
                            {--portal : Grant beautician portal create permission only}';

    protected $description = 'Grant treatment reservation admin permissions to a role.';

    protected $aliases = [
        'treatment-reservation:grant-create-permission',
    ];


    public function handle(): int
    {
        $role = $this->resolveRole();

        $permissions = $this->option('portal')
            ? ['admin.treatment_reservations.portal.create' => true]
            : [
                'admin.treatment_reservations.index' => true,
                'admin.treatment_reservations.create' => true,
                'admin.treatment_reservations.edit' => true,
            ];

        $role->permissions = array_merge($role->permissions ?? [], $permissions);
        $role->save();

        $this->info('Granted treatment reservation permissions to role #' . $role->id . ' (' . $role->name . '):');
        $this->line(implode(', ', array_keys($permissions)));

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
