<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Lead\Entities\Lead;

class BulkUpdateLeadsRequest extends FormRequest
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
        $field = (string) $this->input('field');

        return [
            'ids' => ['required', 'array', 'min:1', 'max:200'],
            'ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('leads', 'id')->whereNull('deleted_at'),
            ],
            'field' => ['required', 'string', Rule::in(['status', 'source', 'created_at', 'beautician_id', 'spa_branch_id'])],
            'value' => match ($field) {
                'status' => ['required', 'string', Rule::in(Lead::statuses())],
                'source' => ['required', 'string', 'max:64'],
                'created_at' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
                'beautician_id' => ['nullable', 'integer', Rule::exists('beauticians', 'id')],
                'spa_branch_id' => ['nullable', 'integer', Rule::exists('spa_branches', 'id')],
                default => ['present'],
            },
        ];
    }

    /**
     * @return array{ids:list<int>,field:string,value:mixed}
     */
    public function payload(): array
    {
        $validated = $this->validated();
        $field = (string) $validated['field'];
        $value = $validated['value'] ?? null;

        if ($value !== null && in_array($field, ['beautician_id', 'spa_branch_id'], true)) {
            $value = (int) $value;
        }

        return [
            'ids' => array_map('intval', $validated['ids']),
            'field' => $field,
            'value' => $value,
        ];
    }
}
