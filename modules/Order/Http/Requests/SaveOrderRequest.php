<?php

namespace Modules\Order\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;
use Modules\Order\Entities\Order;

class SaveOrderRequest extends Request
{
    protected $availableAttributes = 'order::attributes';

    public function rules(): array
    {
        return [
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_first_name' => ['nullable', 'string', 'max:255'],
            'customer_last_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([
                Order::PENDING,
                Order::PENDING_PAYMENT,
                Order::PROCESSING,
                Order::ON_HOLD,
                Order::COMPLETED,
                Order::CANCELED,
                Order::REFUNDED,
            ])],
            'payment_status' => ['nullable', Rule::in(Order::paymentStatuses())],
            'note' => ['nullable', 'string', 'max:5000'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}
