<?php

namespace Modules\User\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;

class UpdateProfileRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'user::attributes.users';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
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
        return [
            'email' => ['required', Rule::unique('users')->ignore($this->email, 'email')],
            'phone' => ['required', new ValidPhone()],
            'identity_number' => ['nullable', 'string', 'max:32', 'regex:/^[A-Za-z0-9\-]+$/'],
            'first_name' => ['required'],
            'last_name' => ['required'],
            'password' => ['nullable', 'confirmed', 'min:6'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
            'address_1' => ['nullable', 'string', 'max:255', 'required_with:city,state,zip,country'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255', 'required_with:address_1'],
            'state' => ['nullable', 'string', 'max:255', 'required_with:address_1'],
            'zip' => ['nullable', 'string', 'max:32', 'required_with:address_1'],
            'country' => ['nullable', 'string', 'max:2', 'required_with:address_1'],
        ];
    }


    /**
     * Hash the user password against the bcrypt algorithm.
     *
     * @return $this|null
     */
    public function bcryptPassword(): static
    {
        if ($this->filled('password')) {
            $this->merge(['password' => bcrypt($this->password)]);
        }

        return $this;
    }
}
