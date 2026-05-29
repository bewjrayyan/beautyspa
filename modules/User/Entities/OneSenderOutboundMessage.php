<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class OneSenderOutboundMessage extends Model
{
    protected $table = 'onesender_outbound_queue';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'recipient',
        'recipient_type',
        'message_type',
        'message_hash',
        'dedupe_key',
        'source',
        'message_preview',
        'payload',
        'status',
        'scheduled_at',
        'processing_at',
        'sent_at',
        'cancelled_at',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'scheduled_at' => 'datetime',
        'processing_at' => 'datetime',
        'sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];


    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }


    public function canBeCancelled(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }


    public function canBeDeleted(): bool
    {
        return in_array($this->status, [
            self::STATUS_CANCELLED,
            self::STATUS_FAILED,
            self::STATUS_SENT,
        ], true);
    }
}
