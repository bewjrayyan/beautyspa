<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Lead\Entities\LeadImport;

class ConfirmLeadImportRequest extends FormRequest
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
            'rows' => ['required', 'array', 'min:1', 'max:5000'],
            'rows.*.name' => ['nullable', 'string', 'max:191'],
            'rows.*.phone' => ['nullable', 'string', 'max:32'],
            'rows.*.email' => ['nullable', 'string', 'max:191'],
            'rows.*.source' => ['nullable', 'string', 'max:64'],
            'rows.*.import' => ['nullable', 'boolean'],
            'source' => ['nullable', 'string', 'max:64'],
            'file_name' => ['nullable', 'string', 'max:191'],
            'spa_branch_id' => ['nullable', 'integer', 'exists:spa_branches,id'],
            'beautician_id' => ['nullable', 'integer', 'exists:beauticians,id'],
        ];
    }

    /**
     * @return array{
     *     method:string,
     *     rows:list<array{name:string,phone:string,email?:string|null,source?:string|null,import?:bool|null}>,
     *     source:string,
     *     file_name:?string,
     *     spa_branch_id:?int,
     *     beautician_id:?int,
     *     uploaded_by:?int
     * }
     */
    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'method' => (string) $validated['method'],
            'rows' => array_values($validated['rows']),
            'source' => (string) ($validated['source'] ?? 'import'),
            'file_name' => $validated['file_name'] ?? null,
            'spa_branch_id' => isset($validated['spa_branch_id']) ? (int) $validated['spa_branch_id'] : null,
            'beautician_id' => isset($validated['beautician_id']) ? (int) $validated['beautician_id'] : null,
            'uploaded_by' => $this->user()?->id,
        ];
    }
}
