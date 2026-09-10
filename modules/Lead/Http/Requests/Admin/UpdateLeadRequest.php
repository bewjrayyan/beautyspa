<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Lead\Entities\Lead;

class UpdateLeadRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:191'],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:191'],
            'source' => ['nullable', 'string', 'max:64'],
            'status' => ['nullable', 'string', Rule::in(Lead::statuses())],
            'spa_branch_id' => ['nullable', 'integer', 'exists:spa_branches,id'],
            'beautician_id' => ['nullable', 'integer', 'exists:beauticians,id'],
        ];
    }

    /**
     * @return array{name:string,phone:string,email:?string,source:string,status:?string,spa_branch_id:?int,beautician_id:?int}
     */
    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'name' => (string) $validated['name'],
            'phone' => (string) $validated['phone'],
            'email' => $validated['email'] ?? null,
            'source' => (string) ($validated['source'] ?? 'manual'),
            'status' => isset($validated['status']) ? (string) $validated['status'] : null,
            'spa_branch_id' => isset($validated['spa_branch_id']) ? (int) $validated['spa_branch_id'] : null,
            'beautician_id' => isset($validated['beautician_id']) ? (int) $validated['beautician_id'] : null,
        ];
    }
}
