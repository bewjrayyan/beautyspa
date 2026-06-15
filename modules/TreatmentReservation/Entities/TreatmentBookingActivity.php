<?php

namespace Modules\TreatmentReservation\Entities;

use Modules\Support\Eloquent\Model;
use Modules\User\Entities\User;

class TreatmentBookingActivity extends Model
{
    public const ACTION_STATUS_CHANGED = 'status_changed';

    public const ACTION_BEAUTICIAN_NOTES_UPDATED = 'beautician_notes_updated';

    public const ACTION_WHATSAPP_SENT = 'whatsapp_sent';

    public const ACTION_REMINDER_SENT = 'reminder_sent';

    public const ACTION_BEAUTICIAN_REMINDER_SENT = 'beautician_reminder_sent';

    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    protected $fillable = [
        'treatment_booking_id',
        'user_id',
        'action',
        'from_value',
        'to_value',
    ];


    public function booking()
    {
        return $this->belongsTo(TreatmentBooking::class, 'treatment_booking_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'action_label' => $this->actionLabel(),
            'from_value' => $this->from_value,
            'to_value' => $this->to_value,
            'summary' => $this->summary(),
            'actor_name' => $this->user?->full_name
                ?: trans('treatmentreservation::admin.activity.system'),
            'created_at' => $this->created_at?->format('d M Y, H:i'),
        ];
    }


    public function actionLabel(): string
    {
        return match ($this->action) {
            self::ACTION_STATUS_CHANGED => trans('treatmentreservation::admin.activity.status_changed'),
            self::ACTION_BEAUTICIAN_NOTES_UPDATED => trans('treatmentreservation::admin.activity.notes_updated'),
            self::ACTION_WHATSAPP_SENT => trans('treatmentreservation::admin.activity.whatsapp_sent'),
            self::ACTION_REMINDER_SENT => trans('treatmentreservation::admin.activity.reminder_sent'),
            self::ACTION_BEAUTICIAN_REMINDER_SENT => trans('treatmentreservation::admin.activity.beautician_reminder_sent'),
            self::ACTION_CREATED => trans('treatmentreservation::admin.activity.created'),
            self::ACTION_UPDATED => trans('treatmentreservation::admin.activity.updated'),
            default => $this->action,
        };
    }


    public function summary(): string
    {
        return match ($this->action) {
            self::ACTION_STATUS_CHANGED => trans('treatmentreservation::admin.activity.status_summary', [
                'from' => $this->statusLabel($this->from_value),
                'to' => $this->statusLabel($this->to_value),
            ]),
            self::ACTION_BEAUTICIAN_NOTES_UPDATED => trans('treatmentreservation::admin.activity.notes_summary'),
            self::ACTION_WHATSAPP_SENT => trans('treatmentreservation::admin.activity.whatsapp_summary'),
            self::ACTION_REMINDER_SENT => trans('treatmentreservation::admin.activity.reminder_summary'),
            self::ACTION_BEAUTICIAN_REMINDER_SENT => trans('treatmentreservation::admin.activity.beautician_reminder_summary'),
            self::ACTION_CREATED => trans('treatmentreservation::admin.activity.created_summary'),
            self::ACTION_UPDATED => trans('treatmentreservation::admin.activity.updated_summary'),
            default => $this->to_value ?? '',
        };
    }


    private function statusLabel(?string $status): string
    {
        if (! $status) {
            return '—';
        }

        $key = 'treatmentreservation::admin.kanban.' . $status;

        return trans()->has($key) ? trans($key) : $status;
    }
}
