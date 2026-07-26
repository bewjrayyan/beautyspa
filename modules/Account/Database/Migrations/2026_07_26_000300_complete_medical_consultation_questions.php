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

        $templates = DB::table('consultation_form_templates')->get();

        foreach ($templates as $template) {
            $questions = collect(json_decode((string) $template->questions, true));

            if (! $questions->contains('key', 'medical_conditions') || ! $questions->contains('key', 'body_areas')) {
                continue;
            }

            $updatedQuestions = $questions->map(function (array $question): array {
                if ($question['key'] === 'medical_conditions') {
                    $question['label'] = 'Adakah anda mengalami mana-mana keadaan berikut? / Do you have any of these conditions?';
                    $question['options'] = $this->medicalConditions();
                }

                if ($question['key'] === 'medication') {
                    $question['label'] = 'Adakah anda mengambil sebarang ubat? / Are you on any medication?';
                }

                if ($question['key'] === 'medication_details') {
                    $question['label'] = 'Jika ya, nyatakan nama ubat atau suplemen / If yes, list the medication or supplements';
                }

                if ($question['key'] === 'family_history') {
                    $question['label'] = 'Adakah terdapat sejarah penyakit keluarga? / Is there a history of family illness?';
                }

                if ($question['key'] === 'recent_surgery') {
                    $question['label'] = 'Adakah anda menjalani pembedahan baru-baru ini? / Have you had any recent surgery?';
                }

                return $question;
            })->values()->all();

            if (! collect($updatedQuestions)->contains('key', 'recent_surgery_details')) {
                $recentSurgeryIndex = collect($updatedQuestions)->search(
                    fn (array $question): bool => $question['key'] === 'recent_surgery'
                );

                if ($recentSurgeryIndex !== false) {
                    array_splice($updatedQuestions, $recentSurgeryIndex + 1, 0, [[
                        'key' => 'recent_surgery_details',
                        'label' => 'Jika ya, nyatakan jenis dan tarikh pembedahan / If yes, state the surgery and date',
                        'type' => 'textarea',
                        'required' => false,
                        'options' => [],
                    ]]);
                }
            }

            DB::table('consultation_form_templates')->where('id', $template->id)->update([
                'questions' => json_encode($updatedQuestions, JSON_UNESCAPED_UNICODE),
                'version' => max(4, ((int) $template->version) + 1),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Submitted forms retain immutable question snapshots and must not be rewritten.
    }

    private function medicalConditions(): array
    {
        return [
            'Alahan / Allergies',
            'Asma / Asthma',
            'Kencing manis / Diabetes',
            'Kanser / Cancer',
            'Kulit abnormal / Abnormal skin',
            'Sawan / Epilepsy',
            'Fibroid atau cyst / Fibroid or cyst',
            'Masalah jantung / Heart condition',
            'Tekanan darah tinggi atau rendah / High or low blood pressure',
            'Masalah peredaran darah / Circulatory problems',
            'Kecederaan besar / Major injuries',
            'AIDS / HIV',
            'Kebimbangan / Anxiety',
            'Takut ruang sempit / Claustrophobia',
            'Serangan panik / Panic attacks',
            'Pening, pitam atau vertigo / Dizziness, fainting or vertigo',
            'Hyper atau hypo thyroid / Hyper or hypothyroid',
            'Besi atau implan logam dalam badan / Metal implant in the body',
            'Kulit sensitif atau ekzema / Sensitive skin or eczema',
            'Pembedahan terdahulu / Previous surgery',
            'Slip disc',
            'Tiada yang berkenaan / None of the above',
        ];
    }
};
