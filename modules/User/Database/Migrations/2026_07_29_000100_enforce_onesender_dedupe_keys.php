<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('onesender_outbound_queue')
            ->select('dedupe_key')
            ->whereNotNull('dedupe_key')
            ->whereIn('status', ['pending', 'processing'])
            ->groupBy('dedupe_key')
            ->havingRaw('COUNT(*) > 1')
            ->limit(1)
            ->exists();

        if ($duplicates) {
            throw new \RuntimeException(
                'Duplicate OneSender dedupe keys require review before the unique constraint can be added.'
            );
        }

        Schema::table('onesender_outbound_queue', function (Blueprint $table): void {
            $table->string('active_dedupe_key', 191)->nullable()->after('dedupe_key');
            $table->unsignedTinyInteger('attempts')->default(0)->after('status');
        });

        DB::table('onesender_outbound_queue')
            ->whereNotNull('dedupe_key')
            ->whereIn('status', ['pending', 'processing'])
            ->update(['active_dedupe_key' => DB::raw('dedupe_key')]);

        Schema::table('onesender_outbound_queue', function (Blueprint $table): void {
            $table->unique('active_dedupe_key', 'onesender_outbound_active_dedupe_unique');
        });
    }

    public function down(): void
    {
        Schema::table('onesender_outbound_queue', function (Blueprint $table): void {
            $table->dropUnique('onesender_outbound_active_dedupe_unique');
            $table->dropColumn(['active_dedupe_key', 'attempts']);
        });
    }
};
