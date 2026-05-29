<?php

namespace Modules\User\Http\Requests;

use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;

class VerifyWhatsAppOtpRequest extends Request
{
    public function authorize(): bool
    {
        return setting('whatsapp_otp_login_enabled');
    }


    public function rules(): array
    {
        return [
            'phone' => ['required', new ValidPhone()],
            'otp' => ['required', 'string', 'digits:6'],
        ];
    }
}
