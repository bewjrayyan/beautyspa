<?php

use Illuminate\Database\Migrations\Migration;
use Modules\User\Entities\Role;

return new class extends Migration
{
    public function up(): void
    {
        if (Role::whereTranslation('name', 'Beautician')->exists()) {
            return;
        }

        Role::create([
            'name' => 'Beautician',
            'permissions' => [],
        ]);
    }


    public function down(): void
    {
        Role::whereTranslation('name', 'Beautician')->delete();
    }
};
