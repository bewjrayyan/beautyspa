<?php

namespace Modules\User\Http\Requests\Admin;

use Modules\Core\Http\Requests\Request;
use Modules\Core\Rules\ValidPhone;

class AdminVerifyWhatsAppOtpRequest extends Request
{
    public function authorize(): bool
    {
        return (bool) setting('whatsapp_otp_login_enabled');
    }


    public function rules(): array
    {
        return [
            'phone' => ['required', new ValidPhone()],
            'otp' => ['required', 'string', 'size:6'],
        ];
    }
}
