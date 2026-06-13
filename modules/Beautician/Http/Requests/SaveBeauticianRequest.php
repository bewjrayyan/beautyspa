<?php

namespace Modules\Beautician\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Beautician\Support\JobTitleOptions;
use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;

class SaveBeauticianRequest extends Request
{
    protected $availableAttributes = 'beautician::attributes';


    protected function prepareForValidation(): void
    {
        if ($this->input('user_id') === '') {
            $this->merge(['user_id' => null]);
        }

        if ($this->has('spa_branches_present') && ! $this->has('spa_branches')) {
            $this->merge(['spa_branches' => []]);
        }
    }


    public function rules(): array
    {
        $id = $this->route('id');
        $linkedUserId = $this->input('user_id') ?: null;

        if ($id && ! $linkedUserId) {
            $linkedUserId = \Modules\Beautician\Entities\Beautician::query()
                ->whereKey($id)
                ->value('user_id');
        }

        return [
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                Rule::unique('beauticians', 'user_id')->ignore($id),
            ],
            'portal_email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($linkedUserId),
            ],
            'portal_password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', new ValidPhone()],
            'profile_color' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'job_title' => [
                'nullable',
                'string',
                'max:255',
                Rule::in(JobTitleOptions::activeNames()),
            ],
            'is_active' => 'required|boolean',
            'position' => 'nullable|integer|min:0',
            'spa_branches' => 'nullable|array',
            'spa_branches.*' => [
                'integer',
                Rule::exists('spa_branches', 'id')->where('is_active', true),
            ],
        ];
    }
}
