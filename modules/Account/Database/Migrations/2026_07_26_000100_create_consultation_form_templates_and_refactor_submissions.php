<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_form_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('title');
            $table->text('intro')->nullable();
            $table->text('consent_text');
            $table->json('questions');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $legacy = Schema::hasColumn('products', 'consultation_title')
            ? DB::table('products')
                ->where('consultation_enabled', true)
                ->whereNotNull('consultation_title')
                ->orderBy('id')
                ->first()
            : null;

        DB::table('consultation_form_templates')->insert([
            'name' => 'Borang konsultasi utama',
            'title' => $legacy?->consultation_title ?: 'Borang Konsultasi Rawatan',
            'intro' => $legacy?->consultation_intro
                ?: 'Sila lengkapkan maklumat ini sebelum sesi rawatan anda.',
            'consent_text' => $legacy?->consultation_consent
                ?: 'Saya mengesahkan maklumat yang diberikan adalah benar dan bersetuju menerima konsultasi serta rawatan yang diterangkan kepada saya.',
            'questions' => $legacy?->consultation_questions ?: json_encode([
                [
                    'key' => 'health_condition',
                    'label' => 'Adakah anda mempunyai sebarang masalah kesihatan atau sedang menerima rawatan perubatan?',
                    'type' => 'textarea',
                    'required' => true,
                    'options' => [],
                ],
                [
                    'key' => 'allergies',
                    'label' => 'Adakah anda mempunyai sebarang alahan?',
                    'type' => 'textarea',
                    'required' => true,
                    'options' => [],
                ],
                [
                    'key' => 'medication',
                    'label' => 'Adakah anda sedang mengambil ubat atau suplemen?',
                    'type' => 'textarea',
                    'required' => false,
                    'options' => [],
                ],
                [
                    'key' => 'pregnancy',
                    'label' => 'Adakah anda hamil atau menyusukan anak?',
                    'type' => 'yes_no',
                    'required' => true,
                    'options' => [],
                ],
                [
                    'key' => 'concerns',
                    'label' => 'Apakah kebimbangan atau hasil yang anda harapkan daripada rawatan ini?',
                    'type' => 'textarea',
                    'required' => true,
                    'options' => [],
                ],
            ], JSON_UNESCAPED_UNICODE),
            'version' => max(1, (int) ($legacy?->consultation_version ?? 1)),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('consultation_submissions', function (Blueprint $table) {
            $table->unsignedBigInteger('template_id')->nullable()->after('id')->index();
            $table->unsignedInteger('treatment_booking_id')->nullable()->after('order_product_id')->index();
            $table->unsignedInteger('beautician_id')->nullable()->after('treatment_booking_id')->index();
            $table->unsignedInteger('sent_by_user_id')->nullable()->after('beautician_id')->index();
            $table->string('public_token', 80)->nullable()->after('sent_by_user_id')->unique();
            $table->string('customer_name')->nullable()->after('public_token');
            $table->string('customer_email')->nullable()->after('customer_name')->index();
            $table->string('customer_phone', 40)->nullable()->after('customer_email')->index();
            $table->timestamp('sent_at')->nullable()->after('customer_phone');
            $table->timestamp('opened_at')->nullable()->after('sent_at');
            $table->timestamp('revoked_at')->nullable()->after('opened_at');
        });

        DB::statement('ALTER TABLE consultation_submissions MODIFY user_id INT UNSIGNED NULL');
        DB::statement('ALTER TABLE consultation_submissions MODIFY product_id INT UNSIGNED NULL');
        DB::statement('ALTER TABLE consultation_submissions MODIFY answers JSON NULL');
        DB::statement('ALTER TABLE consultation_submissions MODIFY signature_data LONGTEXT NULL');
        DB::statement('ALTER TABLE consultation_submissions MODIFY submitted_at TIMESTAMP NULL DEFAULT NULL');

        $templateId = DB::table('consultation_form_templates')->value('id');

        DB::table('consultation_submissions')
            ->whereNull('template_id')
            ->orderBy('id')
            ->eachById(function ($submission) use ($templateId) {
                DB::table('consultation_submissions')
                    ->where('id', $submission->id)
                    ->update([
                        'template_id' => $templateId,
                        'public_token' => Str::random(64),
                        'sent_at' => $submission->created_at,
                    ]);
            });
    }

    public function down(): void
    {
        DB::table('consultation_submissions')->whereNull('submitted_at')->delete();

        Schema::table('consultation_submissions', function (Blueprint $table) {
            $table->dropUnique('consultation_submissions_public_token_unique');
            $table->dropIndex('consultation_submissions_template_id_index');
            $table->dropIndex('consultation_submissions_treatment_booking_id_index');
            $table->dropIndex('consultation_submissions_beautician_id_index');
            $table->dropIndex('consultation_submissions_sent_by_user_id_index');
            $table->dropIndex('consultation_submissions_customer_email_index');
            $table->dropIndex('consultation_submissions_customer_phone_index');
            $table->dropColumn([
                'template_id',
                'treatment_booking_id',
                'beautician_id',
                'sent_by_user_id',
                'public_token',
                'customer_name',
                'customer_email',
                'customer_phone',
                'sent_at',
                'opened_at',
                'revoked_at',
            ]);
        });

        Schema::dropIfExists('consultation_form_templates');
    }
};
