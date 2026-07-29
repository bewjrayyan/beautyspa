<?php

namespace Modules\Account\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;

class SaveDefaultAddressRequest extends Request
{
    public function rules(): array
    {
        return [
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where(
                    fn (Builder $query) => $query->where('customer_id', $this->user()->id)
                ),
            ],
        ];
    }
}
