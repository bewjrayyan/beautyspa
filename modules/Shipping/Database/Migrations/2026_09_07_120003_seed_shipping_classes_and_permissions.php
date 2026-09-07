<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shipping_classes') && DB::table('shipping_classes')->count() === 0) {
            $defaults = [
                ['en' => 'Standard Parcel', 'ms' => 'Bungkusan Standard', 'cost' => 8],
                ['en' => 'Small Item', 'ms' => 'Item Kecil', 'cost' => 5],
                ['en' => 'Bulky / Fragile', 'ms' => 'Besar / Mudah Pecah', 'cost' => 15],
            ];

            foreach ($defaults as $row) {
                $id = DB::table('shipping_classes')->insertGetId([
                    'cost' => $row['cost'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach (['en', 'ms'] as $locale) {
                    DB::table('shipping_class_translations')->insert([
                        'shipping_class_id' => $id,
                        'locale' => $locale,
                        'name' => $row[$locale],
                    ]);
                }
            }
        }

        if (! Schema::hasTable('roles')) {
            return;
        }

        $keys = [
            'admin.shipping_classes.index',
            'admin.shipping_classes.create',
            'admin.shipping_classes.edit',
            'admin.shipping_classes.destroy',
        ];

        foreach (DB::table('roles')->get(['id', 'permissions']) as $role) {
            $permissions = json_decode($role->permissions ?: '{}', true) ?: [];
            $inherited = $permissions['admin.taxes.index']
                ?? $permissions['admin.products.edit']
                ?? $permissions['admin.settings.edit']
                ?? null;

            if ($inherited !== true) {
                continue;
            }

            foreach ($keys as $key) {
                $permissions[$key] = true;
            }

            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode($permissions),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $keys = [
            'admin.shipping_classes.index',
            'admin.shipping_classes.create',
            'admin.shipping_classes.edit',
            'admin.shipping_classes.destroy',
        ];

        foreach (DB::table('roles')->get(['id', 'permissions']) as $role) {
            $permissions = json_decode($role->permissions ?: '{}', true) ?: [];

            foreach ($keys as $key) {
                unset($permissions[$key]);
            }

            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode($permissions),
            ]);
        }
    }
};
