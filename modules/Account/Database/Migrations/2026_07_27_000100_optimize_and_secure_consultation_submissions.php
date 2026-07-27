<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Account\Casts\EncryptedArrayWithLegacyFallback;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\Account\Services\ConsultationContextService;
use Modules\Account\Services\ConsultationSignatureStorage;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('consultation_submissions')) {
            // Encrypted payloads are ciphertext, not valid JSON documents.
            DB::statement('ALTER TABLE consultation_submissions MODIFY answers LONGTEXT NULL');
            DB::statement('ALTER TABLE consultation_submissions MODIFY legal_documents_snapshot LONGTEXT NULL');

            Schema::table('consultation_submissions', function (Blueprint $table): void {
                $table->longText('context_snapshot')->nullable()->after('questions_snapshot');
                $table->string('signature_path')->nullable()->after('signature_data');
                $table->char('signature_hash', 64)->nullable()->after('signature_path');
                $table->string('pdf_path')->nullable()->after('signature_hash');
                $table->char('pdf_hash', 64)->nullable()->after('pdf_path');

                $table->index(
                    ['user_id', 'submitted_at', 'revoked_at', 'sent_at'],
                    'consultation_user_status_sent_idx'
                );
                $table->index(
                    ['template_id', 'submitted_at', 'revoked_at'],
                    'consultation_template_status_idx'
                );
                $table->index(
                    ['treatment_booking_id', 'submitted_at', 'revoked_at'],
                    'consultation_booking_status_idx'
                );
                $table->index('sent_at', 'consultation_sent_at_idx');
            });

            $signatureStorage = app(ConsultationSignatureStorage::class);

            DB::table('consultation_submissions')
                ->select(['id', 'answers', 'legal_documents_snapshot', 'signature_data'])
                ->orderBy('id')
                ->eachById(function (object $submission) use ($signatureStorage): void {
                    $updates = [];
                    $storedPath = null;

                    foreach (['answers', 'legal_documents_snapshot'] as $column) {
                        $decoded = json_decode((string) $submission->{$column}, true);

                        if (is_array($decoded)) {
                            $updates[$column] = EncryptedArrayWithLegacyFallback::encrypt($decoded);
                        }
                    }

                    if (filled($submission->signature_data)) {
                        $stored = $signatureStorage->store(
                            (int) $submission->id,
                            (string) $submission->signature_data
                        );
                        $storedPath = $stored['path'];
                        $updates['signature_data'] = null;
                        $updates['signature_path'] = $stored['path'];
                        $updates['signature_hash'] = $stored['hash'];
                    }

                    if ($updates !== []) {
                        try {
                            DB::table('consultation_submissions')
                                ->where('id', $submission->id)
                                ->update($updates);
                        } catch (Throwable $exception) {
                            $signatureStorage->delete($storedPath);

                            throw $exception;
                        }
                    }
                });

            $context = app(ConsultationContextService::class);

            ConsultationSubmission::query()
                ->whereNull('context_snapshot')
                ->orderBy('id')
                ->eachById(function (ConsultationSubmission $submission) use ($context): void {
                    DB::table('consultation_submissions')
                        ->where('id', $submission->id)
                        ->update([
                            'context_snapshot' => EncryptedArrayWithLegacyFallback::encrypt(
                                $context->forDisplay($submission)
                            ),
                        ]);
                });
        }

        if (! Schema::hasTable('consultation_access_logs')) {
            Schema::create('consultation_access_logs', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('consultation_submission_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('actor_type', 20);
                $table->string('action', 40);
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(
                    ['consultation_submission_id', 'created_at'],
                    'consultation_access_submission_created_idx'
                );
                $table->index(['user_id', 'created_at'], 'consultation_access_user_created_idx');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'phone')) {
            DB::table('users')
                ->whereNotNull('email')
                ->whereRaw('email <> LOWER(TRIM(email))')
                ->update(['email' => DB::raw('LOWER(TRIM(email))')]);

            Schema::table('users', function (Blueprint $table): void {
                $table->index('phone', 'users_phone_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_access_logs');

        if (Schema::hasTable('consultation_submissions')) {
            $signatureStorage = app(ConsultationSignatureStorage::class);

            DB::table('consultation_submissions')
                ->select([
                    'id',
                    'answers',
                    'legal_documents_snapshot',
                    'signature_path',
                    'pdf_path',
                ])
                ->orderBy('id')
                ->eachById(function (object $submission) use ($signatureStorage): void {
                    $updates = [];

                    foreach (['answers', 'legal_documents_snapshot'] as $column) {
                        $decoded = EncryptedArrayWithLegacyFallback::decrypt($submission->{$column});
                        $updates[$column] = $decoded === null
                            ? null
                            : json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }

                    if (filled($submission->signature_path)) {
                        $updates['signature_data'] = $signatureStorage->dataUri(
                            (new \Modules\Account\Entities\ConsultationSubmission())->forceFill([
                                'signature_path' => $submission->signature_path,
                            ])
                        );
                    }

                    DB::table('consultation_submissions')
                        ->where('id', $submission->id)
                        ->update($updates);

                    Storage::disk('private')->deleteDirectory(
                        'consultations/submissions/' . $submission->id
                    );
                });

            Schema::table('consultation_submissions', function (Blueprint $table): void {
                $table->dropIndex('consultation_user_status_sent_idx');
                $table->dropIndex('consultation_template_status_idx');
                $table->dropIndex('consultation_booking_status_idx');
                $table->dropIndex('consultation_sent_at_idx');
                $table->dropColumn([
                    'context_snapshot',
                    'signature_path',
                    'signature_hash',
                    'pdf_path',
                    'pdf_hash',
                ]);
            });

            DB::statement('ALTER TABLE consultation_submissions MODIFY answers JSON NULL');
            DB::statement('ALTER TABLE consultation_submissions MODIFY legal_documents_snapshot JSON NULL');
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex('users_phone_idx');
            });
        }
    }
};
