<?php

namespace Modules\TreatmentReservation\Entities;

use Modules\Product\Entities\Product;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\Support\Eloquent\Model;

class AppointmentDateOverride extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_CUSTOM = 'custom';

    /** Branch-wide override (all treatments). */
    public const PRODUCT_ALL = 0;

    protected $fillable = [
        'spa_branch_id',
        'product_id',
        'override_date',
        'status',
        'reason',
        'capacity_per_slot',
        'duration_minutes',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'override_date' => 'date',
        'capacity_per_slot' => 'integer',
        'duration_minutes' => 'integer',
    ];


    public static function statuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_CLOSED,
            self::STATUS_CUSTOM,
        ];
    }


    public function spaBranch()
    {
        return $this->belongsTo(SpaBranch::class, 'spa_branch_id');
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function slots()
    {
        return $this->hasMany(AppointmentDateOverrideSlot::class, 'appointment_date_override_id')
            ->orderBy('start_time');
    }


    public function isBranchWide(): bool
    {
        return (int) $this->product_id === self::PRODUCT_ALL;
    }
}
