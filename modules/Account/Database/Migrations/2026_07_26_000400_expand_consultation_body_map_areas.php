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

        foreach (DB::table('consultation_form_templates')->get() as $template) {
            $questions = json_decode((string) $template->questions, true);

            if (! is_array($questions) || ! collect($questions)->contains('key', 'body_areas')) {
                continue;
            }

            $bodyQuestion = collect($questions)->firstWhere('key', 'body_areas');
            $alreadyExpanded = in_array(
                'Tapak & jari kaki / Soles & toes',
                $bodyQuestion['options'] ?? [],
                true
            );
            $questions = $this->withExpandedBodyAreas($questions);
            $version = $alreadyExpanded ? (int) $template->version : ((int) $template->version) + 1;

            DB::table('consultation_form_templates')->where('id', $template->id)->update([
                'questions' => json_encode($questions, JSON_UNESCAPED_UNICODE),
                'version' => $version,
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('consultation_submissions')) {
                DB::table('consultation_submissions')
                    ->where('template_id', $template->id)
                    ->whereNull('submitted_at')
                    ->whereNull('revoked_at')
                    ->update([
                        'questions_snapshot' => json_encode($questions, JSON_UNESCAPED_UNICODE),
                        'template_version' => $version,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Completed forms and pending snapshots may contain selected areas; retain them for audit safety.
    }

    private function withExpandedBodyAreas(array $questions): array
    {
        return collect($questions)->map(function (array $question): array {
            if (($question['key'] ?? null) === 'body_areas') {
                $question['label'] = 'Tandakan semua kawasan yang sakit, sensitif atau memerlukan perhatian / Mark all affected areas';
                $question['options'] = $this->bodyAreas();
            }

            return $question;
        })->values()->all();
    }

    private function bodyAreas(): array
    {
        return [
            'Kepala / Head',
            'Muka / Face',
            'Leher / Neck',
            'Bahu / Shoulders',
            'Dada & payudara / Chest & breasts',
            'Perut / Abdomen',
            'Belakang atas / Upper back',
            'Pinggang & belakang bawah / Waist & lower back',
            'Lengan atas / Upper arms',
            'Siku / Elbows',
            'Lengan bawah / Forearms',
            'Pergelangan tangan / Wrists',
            'Tangan & jari / Hands & fingers',
            'Pinggul & pelvis / Hips & pelvis',
            'Punggung / Buttocks',
            'Pangkal paha / Groin',
            'Paha / Thighs',
            'Lutut / Knees',
            'Betis & tulang kering / Calves & shins',
            'Buku lali / Ankles',
            'Tumit / Heels',
            'Bahagian atas kaki / Top of feet',
            'Tapak & jari kaki / Soles & toes',
        ];
    }
};
