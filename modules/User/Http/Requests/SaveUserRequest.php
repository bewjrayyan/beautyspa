<?php

namespace Modules\User\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;

class SaveUserRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'user::attributes.users';


    protected function prepareForValidation(): void
    {
        if ($this->has('date_of_birth') && $this->input('date_of_birth') === '') {
            $this->merge(['date_of_birth' => null]);
        }

        if ($this->has('identity_number') && $this->input('identity_number') === '') {
            $this->merge(['identity_number' => null]);
        }
    }


    public function rules()
    {
        $rules = [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email', $this->emailUniqueRule()],
            'phone' => ['required', new ValidPhone()],
            'identity_number' => ['nullable', 'string', 'max:32', 'regex:/^[A-Za-z0-9\-]+$/'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'password' => ['nullable', 'confirmed', 'min:6'],
            'address_1' => ['nullable', 'string', 'max:255', 'required_with:city,state,zip,country'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255', 'required_with:address_1'],
            'state' => ['nullable', 'string', 'max:255', 'required_with:address_1'],
            'zip' => ['nullable', 'string', 'max:32', 'required_with:address_1'],
            'country' => ['nullable', 'string', 'max:2', 'required_with:address_1'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];

        if ($this->routeIs('admin.users.update')) {
            $rules['roles'] = ['required', Rule::exists('roles', 'id')];
            $rules['activated'] = ['nullable', 'in:0,1'];
        } else {
            $rules['password'] = ['required', 'confirmed', 'min:6'];
            $rules['roles'] = ['required', Rule::exists('roles', 'id')];
            $rules['activated'] = ['nullable', 'in:0,1'];
        }

        return $rules;
    }


    private function emailUniqueRule()
    {
        $rule = Rule::unique('users');

        if ($this->route()->getName() === 'admin.users.update') {
            $userId = $this->route()->parameter('id');

            return $rule->ignore($userId);
        }

        return $rule;
    }
}
