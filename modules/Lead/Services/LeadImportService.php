<?php

declare(strict_types=1);

namespace Modules\Lead\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Lead\Entities\Lead;
use Modules\Lead\Entities\LeadImport;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

final class LeadImportService
{
    public const DETECTION_READY = 'READY';

    public const DETECTION_DUPLICATE = 'DUPLICATE';

    public const DETECTION_EXISTING = 'EXISTING';

    public const DETECTION_INVALID = 'INVALID';

    public function __construct(
        private readonly LeadWorkspaceService $workspace,
    ) {
    }

    /**
     * @param  array{
     *     method:string,
     *     paste?:string|null,
     *     file?:UploadedFile|null,
     *     rows?:list<array{name?:string,phone?:string,email?:string|null,source?:string|null}>,
     *     source?:string|null,
     *     spa_branch_id?:int|null,
     *     beautician_id?:int|null
     * }  $input
     * @return array{rows:list<array<string,mixed>>,summary:array<string,int>,method:string,file_name:?string,source:string,spa_branch_id:?int,beautician_id:?int}
     */
    public function preview(array $input): array
    {
        $method = $this->normalizeMethod((string) ($input['method'] ?? LeadImport::METHOD_PASTE));
        $source = trim((string) ($input['source'] ?? 'import')) ?: 'import';
        $branchId = $this->positiveIntOrNull($input['spa_branch_id'] ?? null);
        $beauticianId = $this->positiveIntOrNull($input['beautician_id'] ?? null);
        $fileName = null;

        $rawRows = [];
        if (! empty($input['rows']) && is_array($input['rows'])) {
            $rawRows = $input['rows'];
        } elseif ($method === LeadImport::METHOD_PASTE) {
            $rawRows = $this->parsePaste((string) ($input['paste'] ?? ''));
        } elseif (($input['file'] ?? null) instanceof UploadedFile) {
            $file = $input['file'];
            $fileName = $file->getClientOriginalName();
            $rawRows = $this->parseSpreadsheet($file);
        }

        $rows = $this->classifyRows($rawRows, $source);

        return [
            'rows' => $rows,
            'summary' => $this->summarizeRows($rows),
            'method' => $method,
            'file_name' => $fileName,
            'source' => $source,
            'spa_branch_id' => $branchId,
            'beautician_id' => $beauticianId,
        ];
    }

    /**
     * @param  array{
     *     method:string,
     *     rows:list<array{name:string,phone:string,email?:string|null,source?:string|null,detection?:string|null,import?:bool|null}>,
     *     source?:string|null,
     *     file_name?:string|null,
     *     spa_branch_id?:int|null,
     *     beautician_id?:int|null,
     *     uploaded_by?:int|null
     * }  $input
     * @return array{batch:array<string,mixed>,imported:int,skipped:int}
     */
    public function confirm(array $input): array
    {
        $method = $this->normalizeMethod((string) ($input['method'] ?? LeadImport::METHOD_PASTE));
        $source = trim((string) ($input['source'] ?? 'import')) ?: 'import';
        $branchId = $this->positiveIntOrNull($input['spa_branch_id'] ?? null);
        $beauticianId = $this->positiveIntOrNull($input['beautician_id'] ?? null);
        $uploadedBy = $this->positiveIntOrNull($input['uploaded_by'] ?? null);
        $fileName = isset($input['file_name']) ? trim((string) $input['file_name']) : null;
        $fileName = $fileName !== '' ? $fileName : null;

        $classified = $this->classifyRows($input['rows'] ?? [], $source);
        $toImport = [];
        foreach ($classified as $i => $row) {
            $want = $input['rows'][$i]['import'] ?? null;
            $shouldImport = $want === null
                ? in_array($row['detection'], [self::DETECTION_READY, self::DETECTION_EXISTING], true)
                : (bool) $want;

            if ($shouldImport && $row['detection'] !== self::DETECTION_INVALID && $row['phone_norm'] !== '') {
                $toImport[] = $row;
            }
        }

        $summary = $this->summarizeRows($classified);

        return DB::transaction(function () use (
            $method,
            $source,
            $branchId,
            $beauticianId,
            $uploadedBy,
            $fileName,
            $classified,
            $toImport,
            $summary
        ) {
            $batch = new LeadImport([
                'batch_code' => $this->nextBatchCode(),
                'method' => $method,
                'file_name' => $fileName,
                'uploaded_by' => $uploadedBy,
                'spa_branch_id' => $branchId,
                'beautician_id' => $beauticianId,
                'source' => $source,
                'raw_count' => $summary['total'],
                'ready_count' => $summary['ready'],
                'unique_count' => $summary['ready'] + $summary['existing'],
                'duplicate_count' => $summary['duplicate'],
                'existing_count' => $summary['existing'],
                'invalid_count' => $summary['invalid'],
                'imported_count' => 0,
                'status' => LeadImport::STATUS_COMPLETED,
            ]);
            $batch->save();

            $imported = 0;
            $seenPhones = [];
            foreach ($toImport as $row) {
                $phone = (string) $row['phone_norm'];
                if ($phone === '' || isset($seenPhones[$phone])) {
                    continue;
                }
                if (Lead::query()->where('phone', $phone)->exists()) {
                    continue;
                }
                $seenPhones[$phone] = true;

                $this->workspace->create([
                    'name' => (string) $row['name'],
                    'phone' => $phone,
                    'email' => $row['email'] !== '' ? (string) $row['email'] : null,
                    'source' => (string) ($row['source'] ?: $source),
                    'status' => Lead::STATUS_NEW,
                    'spa_branch_id' => $branchId,
                    'beautician_id' => $beauticianId,
                    'lead_import_id' => (int) $batch->id,
                ]);
                $imported++;
            }

            $batch->imported_count = $imported;
            $batch->save();

            return [
                'batch' => $this->toArray($batch->fresh(['uploader']) ?? $batch),
                'imported' => $imported,
                'skipped' => max(0, count($classified) - $imported),
            ];
        });
    }

    public function paginate(int $perPage = 25): LengthAwarePaginator
    {
        $perPage = max(1, min(100, $perPage));

        return LeadImport::query()
            ->with(['uploader:id,first_name,last_name'])
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array{
     *     total_imports:int,
     *     raw:int,
     *     unique:int,
     *     duplicates:int,
     *     existing:int,
     *     invalid:int,
     *     imported:int
     * }
     */
    public function historySummary(): array
    {
        $agg = LeadImport::query()
            ->selectRaw('COUNT(*) as total_imports')
            ->selectRaw('COALESCE(SUM(raw_count),0) as raw')
            ->selectRaw('COALESCE(SUM(unique_count),0) as unique_count')
            ->selectRaw('COALESCE(SUM(duplicate_count),0) as duplicates')
            ->selectRaw('COALESCE(SUM(existing_count),0) as existing')
            ->selectRaw('COALESCE(SUM(invalid_count),0) as invalid')
            ->selectRaw('COALESCE(SUM(imported_count),0) as imported')
            ->first();

        return [
            'total_imports' => (int) ($agg->total_imports ?? 0),
            'raw' => (int) ($agg->raw ?? 0),
            'unique' => (int) ($agg->unique_count ?? 0),
            'duplicates' => (int) ($agg->duplicates ?? 0),
            'existing' => (int) ($agg->existing ?? 0),
            'invalid' => (int) ($agg->invalid ?? 0),
            'imported' => (int) ($agg->imported ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(LeadImport $batch): array
    {
        $uploader = $batch->uploader;

        return [
            'id' => (int) $batch->id,
            'batch_code' => (string) $batch->batch_code,
            'date' => $batch->created_at?->format('d M Y') ?? '',
            'by' => $uploader
                ? trim((string) ($uploader->full_name ?? ($uploader->first_name . ' ' . $uploader->last_name)))
                : '—',
            'method' => $batch->method_label,
            'method_key' => (string) $batch->method,
            'file' => $batch->file_name ?: '—',
            'raw' => (int) $batch->raw_count,
            'unique' => (int) $batch->unique_count,
            'duplicate' => (int) $batch->duplicate_count,
            'existing' => (int) $batch->existing_count,
            'invalid' => (int) $batch->invalid_count,
            'imported' => (int) $batch->imported_count,
            'status' => $batch->status_label,
            'status_key' => (string) $batch->status,
            'created_at' => $batch->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return list<array{name:string,phone:string,email:?string,source:?string}>
     */
    public function parsePaste(string $text): array
    {
        $lines = preg_split("/\r\n|\n|\r/", $text) ?: [];
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^(name|nama)\b/i', $line) && preg_match('/phone|telefon|tel\b/i', $line)) {
                continue;
            }

            $parts = preg_split('/\s*[|\t;]\s*/', $line) ?: [];
            if (count($parts) < 2) {
                $parts = str_getcsv($line);
            }
            $parts = array_values(array_map(static fn ($p) => trim((string) $p), $parts));

            if (count($parts) < 2) {
                continue;
            }

            [$name, $phone, $email, $source] = $this->mapLooseColumns($parts);
            $rows[] = [
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'source' => $source,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{name:string,phone:string,email:?string,source:?string}>
     */
    public function parseSpreadsheet(UploadedFile $file): array
    {
        $sheets = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
            public function array(array $array): array
            {
                return $array;
            }
        }, $file);
        $sheet = $sheets[0] ?? [];
        if ($sheet === []) {
            return [];
        }

        $headerMap = null;
        $rows = [];

        foreach ($sheet as $index => $raw) {
            $cells = array_values(array_map(static function ($v) {
                if (is_float($v) || is_int($v)) {
                    return preg_replace('/\.0+$/', '', (string) $v) ?? (string) $v;
                }

                return trim((string) $v);
            }, is_array($raw) ? $raw : []));

            if ($this->rowIsEmpty($cells)) {
                continue;
            }

            if ($headerMap === null && $index === 0 && $this->looksLikeHeader($cells)) {
                $headerMap = $this->headerMap($cells);
                continue;
            }

            if ($headerMap !== null) {
                $name = $cells[$headerMap['name'] ?? -1] ?? '';
                $phone = $cells[$headerMap['phone'] ?? -1] ?? '';
                $email = isset($headerMap['email']) ? ($cells[$headerMap['email']] ?? null) : null;
                $source = isset($headerMap['source']) ? ($cells[$headerMap['source']] ?? null) : null;
            } else {
                [$name, $phone, $email, $source] = $this->mapLooseColumns($cells);
            }

            $rows[] = [
                'name' => (string) $name,
                'phone' => (string) $phone,
                'email' => $email !== null && $email !== '' ? (string) $email : null,
                'source' => $source !== null && $source !== '' ? (string) $source : null,
            ];
        }

        return $rows;
    }

    /**
     * @param  list<array{name?:string,phone?:string,email?:string|null,source?:string|null}>  $rawRows
     * @return list<array{row:int,name:string,phone_orig:string,phone_norm:string,phone_e164:string,email:string,source:string,detection:string,action:string}>
     */
    private function classifyRows(array $rawRows, string $defaultSource): array
    {
        $phonesInBatch = [];
        $out = [];
        $rowNum = 0;

        foreach ($rawRows as $raw) {
            $rowNum++;
            $name = trim((string) ($raw['name'] ?? ''));
            $phoneOrig = trim((string) ($raw['phone'] ?? ''));
            $email = trim((string) ($raw['email'] ?? ''));
            $source = trim((string) ($raw['source'] ?? '')) ?: $defaultSource;
            $phoneNorm = PhoneNumber::normalize($phoneOrig);
            $phoneE164 = PhoneNumber::toE164($phoneNorm);

            $detection = self::DETECTION_READY;
            if ($name === '' || $phoneNorm === '' || ! $this->isValidPhone($phoneNorm)) {
                $detection = self::DETECTION_INVALID;
            } elseif (isset($phonesInBatch[$phoneNorm]) || Lead::query()->where('phone', $phoneNorm)->exists()) {
                $detection = self::DETECTION_DUPLICATE;
            } elseif (User::findByPhone($phoneNorm) !== null) {
                $detection = self::DETECTION_EXISTING;
            }

            if ($detection === self::DETECTION_READY || $detection === self::DETECTION_EXISTING) {
                $phonesInBatch[$phoneNorm] = true;
            }

            $out[] = [
                'row' => $rowNum,
                'name' => $name,
                'phone_orig' => $phoneOrig !== '' ? $phoneOrig : '—',
                'phone_norm' => $phoneNorm,
                'phone_e164' => $phoneE164 !== '' ? $phoneE164 : '—',
                'email' => $email,
                'source' => $source,
                'detection' => $detection,
                'action' => in_array($detection, [self::DETECTION_READY, self::DETECTION_EXISTING], true)
                    ? 'Import'
                    : 'Skip / Review',
            ];
        }

        return $out;
    }

    /**
     * @param  list<array{detection:string}>  $rows
     * @return array{total:int,ready:int,duplicate:int,existing:int,invalid:int}
     */
    private function summarizeRows(array $rows): array
    {
        $summary = ['total' => count($rows), 'ready' => 0, 'duplicate' => 0, 'existing' => 0, 'invalid' => 0];
        foreach ($rows as $row) {
            $key = match ($row['detection']) {
                self::DETECTION_READY => 'ready',
                self::DETECTION_DUPLICATE => 'duplicate',
                self::DETECTION_EXISTING => 'existing',
                default => 'invalid',
            };
            $summary[$key]++;
        }

        return $summary;
    }

    private function nextBatchCode(): string
    {
        $prefix = 'IMP-' . now()->format('ymd') . '-';
        $last = LeadImport::query()
            ->where('batch_code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('batch_code');

        $seq = 1;
        if (is_string($last) && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix . str_pad((string) $seq, 2, '0', STR_PAD_LEFT);
    }

    private function normalizeMethod(string $method): string
    {
        $method = strtolower(trim($method));

        return in_array($method, LeadImport::methods(), true)
            ? $method
            : LeadImport::METHOD_PASTE;
    }

    private function isValidPhone(string $normalized): bool
    {
        $len = strlen($normalized);

        return $len >= 10 && $len <= 15;
    }

    /**
     * @param  list<string>  $parts
     * @return array{0:string,1:string,2:?string,3:?string}
     */
    private function mapLooseColumns(array $parts): array
    {
        $name = '';
        $phone = '';
        $email = null;
        $source = null;

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if ($email === null && filter_var($part, FILTER_VALIDATE_EMAIL)) {
                $email = $part;
                continue;
            }
            $digits = preg_replace('/\D+/', '', $part) ?? '';
            if ($phone === '' && strlen($digits) >= 8 && preg_match('/^[\d+\s\-()]+$/', $part)) {
                $phone = $part;
                continue;
            }
            if ($name === '') {
                $name = $part;
                continue;
            }
            if ($source === null) {
                $source = $part;
            }
        }

        if ($phone === '' && isset($parts[1])) {
            $phone = $parts[1];
        }
        if ($name === '' && isset($parts[0])) {
            $name = $parts[0];
        }

        return [$name, $phone, $email, $source];
    }

    /**
     * @param  list<string>  $cells
     */
    private function looksLikeHeader(array $cells): bool
    {
        $joined = strtolower(implode(' ', $cells));

        return str_contains($joined, 'name') || str_contains($joined, 'nama')
            || str_contains($joined, 'phone') || str_contains($joined, 'telefon');
    }

    /**
     * @param  list<string>  $cells
     * @return array{name?:int,phone?:int,email?:int,source?:int}
     */
    private function headerMap(array $cells): array
    {
        $map = [];
        foreach ($cells as $i => $label) {
            $l = strtolower(trim($label));
            if ($l === '') {
                continue;
            }
            if (! isset($map['name']) && (str_contains($l, 'name') || str_contains($l, 'nama'))) {
                $map['name'] = $i;
            } elseif (! isset($map['phone']) && (str_contains($l, 'phone') || str_contains($l, 'telefon') || $l === 'tel' || $l === 'mobile')) {
                $map['phone'] = $i;
            } elseif (! isset($map['email']) && (str_contains($l, 'email') || str_contains($l, 'e-mel') || str_contains($l, 'emel'))) {
                $map['email'] = $i;
            } elseif (! isset($map['source']) && (str_contains($l, 'source') || str_contains($l, 'sumber') || str_contains($l, 'channel'))) {
                $map['source'] = $i;
            }
        }

        return $map;
    }

    /**
     * @param  list<string>  $cells
     */
    private function rowIsEmpty(array $cells): bool
    {
        foreach ($cells as $c) {
            if (trim((string) $c) !== '') {
                return false;
            }
        }

        return true;
    }

    private function positiveIntOrNull(mixed $raw): ?int
    {
        if ($raw === null || $raw === '' || $raw === 'all') {
            return null;
        }

        $id = (int) $raw;

        return $id > 0 ? $id : null;
    }
}
