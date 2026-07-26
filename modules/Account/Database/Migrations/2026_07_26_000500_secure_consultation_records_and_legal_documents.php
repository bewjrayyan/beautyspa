<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('consultation_submissions')
            && ! Schema::hasColumn('consultation_submissions', 'legal_documents_snapshot')
        ) {
            Schema::table('consultation_submissions', function (Blueprint $table) {
                $table->json('legal_documents_snapshot')->nullable()->after('consent_accepted');
            });
        }

        if (! Schema::hasTable('roles')) {
            return;
        }

        DB::table('roles')->orderBy('id')->each(function (object $role): void {
            $permissions = json_decode($role->permissions ?: '{}', true) ?: [];
            $permissions['admin.consultation_forms.index'] = $permissions['admin.products.index'] ?? null;
            $permissions['admin.consultation_forms.edit'] = $permissions['admin.products.edit'] ?? null;
            $permissions['admin.consultation_submissions.download'] = $permissions['admin.users.edit'] ?? null;

            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode(array_filter(
                    $permissions,
                    fn (mixed $value): bool => $value !== null
                )),
            ]);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            DB::table('roles')->orderBy('id')->each(function (object $role): void {
                $permissions = json_decode($role->permissions ?: '{}', true) ?: [];
                unset(
                    $permissions['admin.consultation_forms.index'],
                    $permissions['admin.consultation_forms.edit'],
                    $permissions['admin.consultation_submissions.download']
                );

                DB::table('roles')->where('id', $role->id)->update([
                    'permissions' => json_encode($permissions),
                ]);
            });
        }

        if (
            Schema::hasTable('consultation_submissions')
            && Schema::hasColumn('consultation_submissions', 'legal_documents_snapshot')
        ) {
            Schema::table('consultation_submissions', function (Blueprint $table) {
                $table->dropColumn('legal_documents_snapshot');
            });
        }
    }
};
