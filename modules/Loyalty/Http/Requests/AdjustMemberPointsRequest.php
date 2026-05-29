<?php

namespace Modules\Loyalty\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustMemberPointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'points' => 'required|integer|not_in:0',
            'description' => 'required|string|max:500',
        ];
    }
}
