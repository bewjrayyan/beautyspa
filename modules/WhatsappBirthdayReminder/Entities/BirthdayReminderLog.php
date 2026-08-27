<?php

namespace Modules\WhatsappBirthdayReminder\Entities;

use Modules\Coupon\Entities\Coupon;
use Modules\Support\Eloquent\Model;
use Modules\User\Entities\User;
use Modules\WhatsappBirthdayReminder\Enums\DeliveryStatus;

class BirthdayReminderLog extends Model
{
    protected $table = 'whatsapp_birthday_reminder_logs';

    protected $fillable = [
        'user_id',
        'year',
        'phone',
        'reward_type',
        'reward_payload',
        'coupon_code',
        'coupon_id',
        'points_awarded',
        'message_preview',
        'image_url',
        'delivery_status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'reward_payload' => 'array',
        'sent_at' => 'datetime',
        'year' => 'integer',
        'points_awarded' => 'integer',
        'coupon_id' => 'integer',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }


    public function isSent(): bool
    {
        return $this->delivery_status === DeliveryStatus::SENT;
    }


    public function markSent(?string $imageUrl = null): void
    {
        $this->update([
            'delivery_status' => DeliveryStatus::SENT,
            'image_url' => $imageUrl ?? $this->image_url,
            'error_message' => null,
            'sent_at' => now(),
        ]);
    }


    public function markFailed(string $message): void
    {
        $this->update([
            'delivery_status' => DeliveryStatus::FAILED,
            'error_message' => $message,
        ]);
    }


    public function markSkipped(string $reason): void
    {
        $this->update([
            'delivery_status' => DeliveryStatus::SKIPPED,
            'error_message' => $reason,
        ]);
    }
}
