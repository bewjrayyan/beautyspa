<?php

namespace Modules\Shipping\Http\Requests;

use Modules\Core\Http\Requests\Request;

class SaveShippingClassRequest extends Request
{
    protected $availableAttributes = 'shipping::attributes';


    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'cost' => ['required', 'numeric', 'min:0', 'max:99999999999999'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
