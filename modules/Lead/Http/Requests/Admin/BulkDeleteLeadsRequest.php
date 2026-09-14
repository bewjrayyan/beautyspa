<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDeleteLeadsRequest extends FormRequest
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
            'ids' => ['required', 'array', 'min:1', 'max:200'],
            'ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('leads', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        return array_map('intval', $this->validated('ids'));
    }
}
