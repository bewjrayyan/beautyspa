<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Modules\Lead\Entities\LeadImport;

class PreviewLeadImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'method' => ['required', 'string', Rule::in(LeadImport::methods())],
            'paste' => ['nullable', 'string', 'max:500000'],
            'file' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:csv,txt,xlsx,xls',
            ],
            'rows' => ['nullable', 'array', 'max:5000'],
            'rows.*.name' => ['nullable', 'string', 'max:191'],
            'rows.*.phone' => ['nullable', 'string', 'max:32'],
            'rows.*.email' => ['nullable', 'string', 'max:191'],
            'rows.*.source' => ['nullable', 'string', 'max:64'],
            'source' => ['nullable', 'string', 'max:64'],
            'spa_branch_id' => ['nullable', 'integer', 'exists:spa_branches,id'],
            'beautician_id' => ['nullable', 'integer', 'exists:beauticians,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $method = (string) $this->input('method');
            $hasPaste = trim((string) $this->input('paste', '')) !== '';
            $hasFile = $this->file('file') instanceof UploadedFile;
            $hasRows = is_array($this->input('rows')) && count($this->input('rows')) > 0;

            if ($method === LeadImport::METHOD_PASTE && ! $hasPaste && ! $hasRows) {
                $validator->errors()->add('paste', trans('lead::central.import.paste_required'));
            }

            if (in_array($method, [LeadImport::METHOD_EXCEL, LeadImport::METHOD_CSV], true) && ! $hasFile && ! $hasRows) {
                $validator->errors()->add('file', trans('lead::central.import.file_required'));
            }

            if ($method === LeadImport::METHOD_MANUAL && ! $hasRows) {
                $validator->errors()->add('rows', trans('lead::central.import.rows_required'));
            }
        });
    }

    /**
     * @return array{
     *     method:string,
     *     paste:?string,
     *     file:?UploadedFile,
     *     rows:?list<array{name?:string,phone?:string,email?:string|null,source?:string|null}>,
     *     source:?string,
     *     spa_branch_id:?int,
     *     beautician_id:?int
     * }
     */
    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'method' => (string) $validated['method'],
            'paste' => $validated['paste'] ?? null,
            'file' => $this->file('file'),
            'rows' => $validated['rows'] ?? null,
            'source' => $validated['source'] ?? 'import',
            'spa_branch_id' => isset($validated['spa_branch_id']) ? (int) $validated['spa_branch_id'] : null,
            'beautician_id' => isset($validated['beautician_id']) ? (int) $validated['beautician_id'] : null,
        ];
    }
}
