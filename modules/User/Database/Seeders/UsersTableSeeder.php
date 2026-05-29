<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;
use Cartalyst\Sentinel\Laravel\Facades\Activation;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::find(1);

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'BeautySpa',
            'email' => 'admin@beautyspa.local',
            'password' => bcrypt(123456),
        ]);

        $activation = Activation::create($admin);
        Activation::complete($admin, $activation->code);

        $adminRole->users()->attach($admin);
    }
}
