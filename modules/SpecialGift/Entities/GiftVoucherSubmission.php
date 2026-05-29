<?php

namespace Modules\SpecialGift\Entities;

use Modules\Order\Entities\Order;
use Modules\Support\Eloquent\Model;

class GiftVoucherSubmission extends Model
{
    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $table = 'gift_voucher_submissions';

    protected $fillable = [
        'recipient_name',
        'order_number',
        'order_id',
        'whatsapp_number',
        'sender_name',
        'generated_image_url',
        'delivery_status',
        'whatsapp_response',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
