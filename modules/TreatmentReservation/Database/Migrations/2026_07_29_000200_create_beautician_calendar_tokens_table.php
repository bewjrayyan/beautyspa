<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beautician_calendar_tokens', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('beautician_id')->unique();
            $table->char('token_hash', 64)->unique();
            $table->text('token_ciphertext');
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->foreign('beautician_id')
                ->references('id')->on('beauticians')
                ->cascadeOnDelete();
        });

        DB::table('beauticians')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($beauticians): void {
                $now = now();
                $rows = [];

                foreach ($beauticians as $beautician) {
                    $legacyToken = substr(
                        hash_hmac('sha256', (string) $beautician->id, (string) config('app.key')),
                        0,
                        32
                    );
                    $rows[] = [
                        'beautician_id' => $beautician->id,
                        'token_hash' => hash('sha256', $legacyToken),
                        'token_ciphertext' => Crypt::encryptString($legacyToken),
                        'expires_at' => $now->copy()->addYear(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('beautician_calendar_tokens')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('beautician_calendar_tokens');
    }
};
