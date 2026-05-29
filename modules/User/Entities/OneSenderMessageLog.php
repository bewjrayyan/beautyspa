<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class OneSenderMessageLog extends Model
{
    protected $table = 'onesender_message_logs';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED_DUPLICATE = 'skipped_duplicate';

    public const STATUS_SKIPPED_PAUSED = 'skipped_paused';

    public const STATUS_SKIPPED_DISABLED = 'skipped_disabled';

    public const STATUS_SKIPPED_CANCELLED = 'skipped_cancelled';

    public $timestamps = false;

    protected $fillable = [
        'recipient',
        'recipient_type',
        'message_type',
        'message_hash',
        'dedupe_key',
        'source',
        'message_preview',
        'status',
        'http_status',
        'api_response',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'api_response' => 'array',
        'created_at' => 'datetime',
    ];
}
