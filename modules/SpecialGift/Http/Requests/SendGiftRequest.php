<?php

namespace Modules\SpecialGift\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\SpecialGift\Services\SpecialGiftConfig;

class SendGiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(SpecialGiftConfig::class)->enabled();
    }


    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:255'],
            'order_number' => ['required', 'string', 'max:64'],
            'whatsapp_number' => ['required', 'string', 'max:32'],
            'sender_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
