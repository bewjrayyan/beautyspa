<?php

namespace AestheticCart\Http\Requests;

use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;

class InstallRequest extends Request
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'app_url' => 'required|url',
            'db_host' => 'required',
            'db_port' => 'required',
            'db_username' => 'required',
            'db_password' => 'nullable',
            'db_database' => 'required',
            'admin_first_name' => 'required',
            'admin_last_name' => 'required',
            'admin_email' => 'required|email',
            'admin_phone' => ['required', new ValidPhone()],
            'admin_password' => 'required|confirmed|min:6',
            'store_name' => 'required',
            'store_email' => 'required|email',
            'store_phone' => ['required', new ValidPhone()],
        ];
    }

    public function attributes(): array
    {
        return [
            'app_url' => trans('install.attributes.app_url'),
            'db_host' => trans('install.attributes.db_host'),
            'db_port' => trans('install.attributes.db_port'),
            'db_username' => trans('install.attributes.db_username'),
            'db_password' => trans('install.attributes.db_password'),
            'db_database' => trans('install.attributes.db_database'),
            'admin_first_name' => trans('install.attributes.admin_first_name'),
            'admin_last_name' => trans('install.attributes.admin_last_name'),
            'admin_email' => trans('install.attributes.admin_email'),
            'admin_phone' => trans('install.attributes.admin_phone'),
            'admin_password' => trans('install.attributes.admin_password'),
            'store_name' => trans('install.attributes.store_name'),
            'store_email' => trans('install.attributes.store_email'),
            'store_phone' => trans('install.attributes.store_phone'),
        ];
    }
}
