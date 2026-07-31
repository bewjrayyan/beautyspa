<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admin_operation_audits')) {
            Schema::create('admin_operation_audits', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('action', 80);
                $table->string('target_type', 80);
                $table->string('target_id', 191)->nullable();
                $table->json('metadata')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['action', 'created_at'], 'admin_ops_action_created_idx');
                $table->index(['user_id', 'created_at'], 'admin_ops_user_created_idx');
            });
        }

        if (! Schema::hasTable('operation_heartbeats')) {
            Schema::create('operation_heartbeats', function (Blueprint $table): void {
                $table->string('name', 80)->primary();
                $table->timestamp('last_seen_at');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->orderBy('id')->each(function (object $role): void {
                $permissions = json_decode($role->permissions ?: '{}', true) ?: [];
                $inherited = $permissions['admin.settings.edit'] ?? null;

                foreach ([
                    'admin.operations.view',
                    'admin.operations.manage_queue',
                    'admin.operations.manage_retention',
                ] as $permission) {
                    $permissions[$permission] = $inherited;
                }

                DB::table('roles')->where('id', $role->id)->update([
                    'permissions' => json_encode(array_filter(
                        $permissions,
                        fn (mixed $value): bool => $value !== null
                    )),
                ]);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            DB::table('roles')->orderBy('id')->each(function (object $role): void {
                $permissions = json_decode($role->permissions ?: '{}', true) ?: [];
                unset(
                    $permissions['admin.operations.view'],
                    $permissions['admin.operations.manage_queue'],
                    $permissions['admin.operations.manage_retention']
                );

                DB::table('roles')->where('id', $role->id)->update([
                    'permissions' => json_encode($permissions),
                ]);
            });
        }

        Schema::dropIfExists('operation_heartbeats');
        Schema::dropIfExists('admin_operation_audits');
    }
};
