<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('consultation_form_templates')) {
            return;
        }

        $template = DB::table('consultation_form_templates')
            ->where('name', 'Borang konsultasi utama')
            ->first();

        $existingQuestions = collect(json_decode((string) ($template?->questions ?? '[]'), true));
        $isLegacyDefault = $existingQuestions->count() <= 5
            && $existingQuestions->pluck('key')->contains('health_condition')
            && $existingQuestions->pluck('key')->contains('concerns');

        if (! $template || ! $isLegacyDefault) {
            return;
        }

        DB::table('consultation_form_templates')->where('id', $template->id)->update([
            'name' => 'Borang konsultasi perubatan utama',
            'title' => 'Borang Konsultasi Kesihatan & Rawatan',
            'intro' => 'Maklumat ini membantu beautician memahami keadaan kesihatan, keperluan dan kawasan rawatan anda dengan lebih selamat.',
            'consent_text' => 'Saya mengesahkan bahawa semua maklumat yang diberikan adalah benar dan lengkap. Saya memahami penerangan yang diberikan serta memberi persetujuan untuk konsultasi dan rawatan dijalankan tanpa paksaan daripada mana-mana pihak.',
            'questions' => json_encode($this->medicalQuestions(), JSON_UNESCAPED_UNICODE),
            'version' => max(2, ((int) $template->version) + 1),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Historical template snapshots must not be rewritten on rollback.
    }

    private function medicalQuestions(): array
    {
        return [
            ['key' => 'section_medical', 'label' => 'Keadaan Kesihatan / Medical Conditions', 'type' => 'section', 'required' => false, 'options' => []],
            [
                'key' => 'medical_conditions',
                'label' => 'Adakah anda mengalami mana-mana keadaan berikut?',
                'type' => 'checkbox',
                'required' => true,
                'options' => [
                    'Alahan / Allergies', 'Asma / Asthma', 'Kulit sensitif / Sensitive skin',
                    'Kencing manis / Diabetes', 'Kanser / Cancer', 'Kulit abnormal / Abnormal skin',
                    'Sawan / Epilepsy', 'Fibroid atau cyst', 'Masalah jantung / Heart condition',
                    'Tekanan darah tinggi atau rendah', 'Masalah peredaran darah', 'Kecederaan besar / Major injuries',
                    'AIDS / HIV', 'Kebimbangan atau serangan panik', 'Vertigo atau pening',
                    'Masalah tiroid', 'Ekzema', 'Implan logam dalam badan', 'Tiada yang berkenaan',
                ],
            ],
            ['key' => 'medical_conditions_other', 'label' => 'Keadaan kesihatan lain yang perlu kami ketahui', 'type' => 'textarea', 'required' => false, 'options' => []],
            ['key' => 'section_current', 'label' => 'Keadaan Semasa / Current Condition', 'type' => 'section', 'required' => false, 'options' => []],
            ['key' => 'pregnancy', 'label' => 'Adakah anda sedang hamil?', 'type' => 'yes_no', 'required' => true, 'options' => []],
            ['key' => 'breastfeeding', 'label' => 'Adakah anda sedang menyusukan anak?', 'type' => 'yes_no', 'required' => true, 'options' => []],
            ['key' => 'medication', 'label' => 'Adakah anda sedang mengambil ubat atau suplemen?', 'type' => 'yes_no', 'required' => true, 'options' => []],
            ['key' => 'medication_details', 'label' => 'Jika ya, nyatakan nama ubat atau suplemen', 'type' => 'textarea', 'required' => false, 'options' => []],
            ['key' => 'recent_surgery', 'label' => 'Adakah anda menjalani pembedahan baru-baru ini?', 'type' => 'yes_no', 'required' => true, 'options' => []],
            ['key' => 'family_history', 'label' => 'Sejarah penyakit keluarga yang berkaitan', 'type' => 'textarea', 'required' => false, 'options' => []],
            ['key' => 'section_body', 'label' => 'Peta Tubuh / Body Map', 'type' => 'section', 'required' => false, 'options' => []],
            [
                'key' => 'body_areas',
                'label' => 'Tandakan kawasan yang sakit, sensitif atau memerlukan perhatian',
                'type' => 'body_map',
                'required' => false,
                'options' => [
                    'Kepala / Head', 'Leher & bahu / Neck & shoulders', 'Dada / Chest',
                    'Perut / Abdomen', 'Belakang / Back', 'Pinggang / Lower back',
                    'Lengan / Arms', 'Tangan / Hands', 'Pinggul / Hips',
                    'Kaki / Legs', 'Lutut / Knees', 'Tapak kaki / Feet',
                ],
            ],
            ['key' => 'pain_details', 'label' => 'Terangkan rasa sakit, sensitiviti atau kecederaan pada kawasan tersebut', 'type' => 'textarea', 'required' => false, 'options' => []],
            ['key' => 'section_goals', 'label' => 'Keperluan Rawatan / Treatment Goals', 'type' => 'section', 'required' => false, 'options' => []],
            ['key' => 'concerns', 'label' => 'Apakah kebimbangan utama atau hasil yang anda harapkan?', 'type' => 'textarea', 'required' => true, 'options' => []],
            ['key' => 'additional_notes', 'label' => 'Maklumat tambahan untuk beautician', 'type' => 'textarea', 'required' => false, 'options' => []],
        ];
    }
};
