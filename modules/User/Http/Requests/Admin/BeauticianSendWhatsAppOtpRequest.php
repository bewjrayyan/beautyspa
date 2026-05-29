<?php

namespace Modules\User\Http\Requests\Admin;

use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;

class BeauticianSendWhatsAppOtpRequest extends Request
{
    public function rules(): array
    {
        return [
            'phone' => ['required', new ValidPhone()],
        ];
    }
}
