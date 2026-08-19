<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Facades\Http;
use Modules\TreatmentReservation\Entities\TreatmentPublicHoliday;

class MalaysiaHolidayImportService
{
    private const BASE_URL = 'https://malaysia-holiday.dydxsoft.my/api/v1/holidays';

    public function importYear(int $year, ?string $state = null): int
    {
        $query = ['year' => $year];

        if ($state) {
            $query['state'] = $state;
        }

        $response = Http::timeout(30)->acceptJson()->get(self::BASE_URL, $query);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Failed to import Malaysia holidays: ' . ($response->body() ?: $response->status())
            );
        }

        /** @var array{data?: array<int, array<string, mixed>>} $payload */
        $payload = $response->json();
        $items = $payload['data'] ?? [];

        if (! is_array($items) || $items === []) {
            return 0;
        }

        $source = 'malaysia-holiday-api:v1';

        $saved = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $date = (string) ($item['date'] ?? '');
            $name = (string) ($item['name'] ?? '');

            if ($date === '' || $name === '') {
                continue;
            }

            $dayName = isset($item['day_name']) ? (string) $item['day_name'] : null;
            $stateCodes = $item['state_codes'] ?? null;
            $isSubjectToChange = (bool) ($item['is_subject_to_change'] ?? false);

            if (is_array($stateCodes)) {
                $stateCodes = array_values(array_map(static fn ($v) => (string) $v, $stateCodes));
            } else {
                $stateCodes = null;
            }

            $color = $this->colorForHolidayName($name);

            // Ensure importer behaves like a true master-data override:
            // keep only 1 record per `date + source`, so custom edits won't create duplicates.
            TreatmentPublicHoliday::query()
                ->where('date', $date)
                ->where('source', $source)
                ->delete();

            TreatmentPublicHoliday::updateOrCreate(
                [
                    'date' => $date,
                    'source' => $source,
                ],
                [
                    'name' => $name,
                    'day_name' => $dayName,
                    'state_codes' => $stateCodes,
                    'is_subject_to_change' => $isSubjectToChange,
                    'color' => $color,
                ],
            );

            $saved++;
        }

        return $saved;
    }

    private function colorForHolidayName(string $name): string
    {
        $n = mb_strtolower($name);

        return match (true) {
            // National / federation
            str_contains($n, 'merdeka')
                || str_contains($n, 'malaysia day')
                || str_contains($n, 'kebangsaan')
                || str_contains($n, 'hari malaysia')
                || str_contains($n, 'hari kebangsaan')
                || str_contains($n, 'national') => '#dc2626',

            // Chinese New Year
            str_contains($n, 'tahun baharu cina') || str_contains($n, 'chinese new year') => '#ef4444',

            // Labour
            str_contains($n, 'hari pekerja') || str_contains($n, 'labour') || str_contains($n, 'worker') => '#f97316',

            // Islamic / Muharram / Ramadan / Raya
            str_contains($n, 'raya') || str_contains($n, 'qurban') => '#7c3aed',
            str_contains($n, 'muharram') || str_contains($n, 'awal muharram') || str_contains($n, 'maul') => '#475569',
            str_contains($n, 'ramadan') => '#475569',
            str_contains($n, 'nabi') || str_contains($n, 'prophet') => '#2563eb',
            str_contains($n, 'good friday') => '#64748b',

            // Thaipusam / Deepavali / Christmas
            str_contains($n, 'thaipusam') => '#0ea5e9',
            str_contains($n, 'deepavali') => '#f59e0b',
            str_contains($n, 'christmas') || str_contains($n, 'krismas') => '#16a34a',

            default => '#3b82f6',
        };
    }
}

