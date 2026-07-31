<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('consultation_submissions')
            && ! Schema::hasColumn('consultation_submissions', 'legal_hold_at')) {
            Schema::table('consultation_submissions', function (Blueprint $table): void {
                $table->timestamp('legal_hold_at')->nullable()->after('revoked_at');
                $table->unsignedInteger('legal_hold_by')->nullable()->after('legal_hold_at');
                $table->string('legal_hold_reason', 500)->nullable()->after('legal_hold_by');
                $table->index(
                    ['legal_hold_at', 'submitted_at', 'public_token_expires_at'],
                    'consultation_retention_candidates_idx'
                );
            });
        }

        if (! Schema::hasTable('privacy_retention_runs')) {
            Schema::create('privacy_retention_runs', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('mode', 20);
                $table->string('status', 20);
                $table->json('counts')->nullable();
                $table->string('error', 1000)->nullable();
                $table->timestamp('started_at');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'started_at'], 'privacy_retention_status_started_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_retention_runs');

        if (Schema::hasTable('consultation_submissions')
            && Schema::hasColumn('consultation_submissions', 'legal_hold_at')) {
            Schema::table('consultation_submissions', function (Blueprint $table): void {
                $table->dropIndex('consultation_retention_candidates_idx');
                $table->dropColumn(['legal_hold_at', 'legal_hold_by', 'legal_hold_reason']);
            });
        }
    }
};
