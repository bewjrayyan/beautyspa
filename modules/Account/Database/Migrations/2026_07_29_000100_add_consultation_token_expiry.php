<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_submissions', function (Blueprint $table): void {
            $table->timestamp('public_token_expires_at')
                ->nullable()
                ->after('public_token')
                ->index();
            $table->char('public_token_hash', 64)->nullable()->after('public_token_expires_at')->unique();
            $table->text('public_token_ciphertext')->nullable()->after('public_token_hash');
        });

        DB::table('consultation_submissions')
            ->whereNotNull('public_token')
            ->whereNull('public_token_expires_at')
            ->update(['public_token_expires_at' => now()->addDays(30)]);

        DB::table('consultation_submissions')
            ->select(['id', 'public_token'])
            ->whereNotNull('public_token')
            ->orderBy('id')
            ->chunkById(100, function ($submissions): void {
                foreach ($submissions as $submission) {
                    DB::table('consultation_submissions')
                        ->where('id', $submission->id)
                        ->update([
                            'public_token_hash' => hash('sha256', $submission->public_token),
                            'public_token_ciphertext' => Crypt::encryptString($submission->public_token),
                            'public_token' => null,
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('consultation_submissions')
            ->select(['id', 'public_token_ciphertext'])
            ->whereNotNull('public_token_ciphertext')
            ->orderBy('id')
            ->chunkById(100, function ($submissions): void {
                foreach ($submissions as $submission) {
                    DB::table('consultation_submissions')
                        ->where('id', $submission->id)
                        ->update([
                            'public_token' => Crypt::decryptString($submission->public_token_ciphertext),
                        ]);
                }
            });

        Schema::table('consultation_submissions', function (Blueprint $table): void {
            $table->dropColumn([
                'public_token_expires_at',
                'public_token_hash',
                'public_token_ciphertext',
            ]);
        });
    }
};
