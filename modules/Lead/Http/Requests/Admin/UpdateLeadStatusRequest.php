<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Lead\Entities\Lead;

class UpdateLeadStatusRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::in(Lead::statuses())],
        ];
    }
}
