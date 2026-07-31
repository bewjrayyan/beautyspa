<?php

namespace Modules\Beautician\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Beautician\Support\JobTitleOptions;
use Modules\Core\Rules\ValidPhone;
use Modules\User\Http\Requests\RegisterRequest;

class RegisterBeauticianRequest extends RegisterRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['first_name'] = ['required', 'string', 'max:255'];
        $rules['last_name'] = ['required', 'string', 'max:255'];
        $rules['email'] = ['required', 'email', 'max:255', Rule::unique('users', 'email')];
        $rules['phone'] = [
            'required',
            new ValidPhone(),
            Rule::unique('users', 'phone'),
        ];
        $rules['job_title'] = [
            'nullable',
            'string',
            'max:255',
            Rule::in(JobTitleOptions::activeNames()),
        ];

        if (is_module_enabled('SpaBranch')) {
            $rules['spa_branches'] = ['required', 'array', 'min:1'];
            $rules['spa_branches.*'] = [
                'integer',
                Rule::exists('spa_branches', 'id')->where('is_active', true),
            ];
        }

        return $rules;
    }


    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'spa_branches.required' => trans('beautician::beauticians.self_registration.validation.branch_required'),
            'spa_branches.min' => trans('beautician::beauticians.self_registration.validation.branch_required'),
        ]);
    }
}
