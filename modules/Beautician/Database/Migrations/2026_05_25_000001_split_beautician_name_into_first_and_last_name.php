<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('beauticians', 'first_name')) {
            Schema::table('beauticians', function (Blueprint $table) {
                $table->string('first_name')->nullable()->after('id');
                $table->string('last_name')->nullable()->after('first_name');
            });
        }

        if (Schema::hasColumn('beauticians', 'name')) {
            DB::table('beauticians')
                ->orderBy('id')
                ->lazyById()
                ->each(function (object $beautician) {
                    $parts = preg_split('/\s+/', trim((string) $beautician->name), 2) ?: ['', ''];

                    DB::table('beauticians')
                        ->where('id', $beautician->id)
                        ->update([
                            'first_name' => $parts[0] !== '' ? $parts[0] : 'Beautician',
                            'last_name' => $parts[1] ?? '',
                        ]);
                });

            Schema::table('beauticians', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }

        DB::table('beauticians')
            ->whereNull('first_name')
            ->orWhere('first_name', '')
            ->update(['first_name' => 'Beautician']);
    }


    public function down(): void
    {
        if (! Schema::hasColumn('beauticians', 'name')) {
            Schema::table('beauticians', function (Blueprint $table) {
                $table->string('name')->nullable()->after('id');
            });

            DB::table('beauticians')
                ->orderBy('id')
                ->lazyById()
                ->each(function (object $beautician) {
                    $fullName = trim(trim((string) $beautician->first_name) . ' ' . trim((string) $beautician->last_name));

                    DB::table('beauticians')
                        ->where('id', $beautician->id)
                        ->update(['name' => $fullName !== '' ? $fullName : 'Beautician']);
                });
        }

        if (Schema::hasColumn('beauticians', 'first_name')) {
            Schema::table('beauticians', function (Blueprint $table) {
                $table->dropColumn(['first_name', 'last_name']);
            });
        }
    }
};
