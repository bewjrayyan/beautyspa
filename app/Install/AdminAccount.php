<?php

namespace AestheticCart\Install;

use Modules\User\Entities\Role;
use Modules\User\Entities\User;
use Cartalyst\Sentinel\Laravel\Facades\Activation;

class AdminAccount
{
    public function setup($request): void
    {
        $adminUser = $this->createAdminUser($request);
        $this->activateAdminUser($adminUser);

        $adminRole = $this->createAdminRole();
        $adminUser->roles()->attach($adminRole);
    }


    private function createAdminUser($request)
    {
        return User::create([
            'first_name' => $request['admin_first_name'],
            'last_name' => $request['admin_last_name'],
            'email' => $request['admin_email'],
            'phone' => $request['admin_phone'],
            'password' => bcrypt($request['admin_password']),
        ]);
    }


    private function activateAdminUser($adminUser): void
    {
        $activation = Activation::create($adminUser);
        Activation::complete($adminUser, $activation->code);
    }


    private function createAdminRole()
    {
        return Role::create([
            'name' => 'Admin',
            'permissions' => $this->getAdminRolePermissions(),
        ]);
    }


    private function getAdminRolePermissions(): array
    {
        return AdminPermissions::allGranted();
    }
}
