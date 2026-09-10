<?php

declare(strict_types=1);

namespace Modules\Lead\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Beautician\Entities\Beautician;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\Support\Eloquent\Model;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

class Lead extends Model
{
    use SoftDeletes;

    public const STATUS_NEW = 'new';

    public const STATUS_CLAIMED = 'claimed';

    public const STATUS_FOLLOW_UP = 'follow_up';

    public const STATUS_BOOKING = 'booking';

    public const STATUS_PAYMENT_VERIFIED = 'payment_verified';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_NO_RESPONSE = 'no_response';

    public const STATUS_LOST = 'lost';

    protected $table = 'leads';

    protected $fillable = [
        'phone',
        'name',
        'email',
        'source',
        'status',
        'spa_branch_id',
        'beautician_id',
        'customer_id',
        'lead_import_id',
        'is_duplicate',
        'is_existing_customer',
        'last_followed_up_at',
    ];

    protected $casts = [
        'spa_branch_id' => 'integer',
        'beautician_id' => 'integer',
        'customer_id' => 'integer',
        'lead_import_id' => 'integer',
        'is_duplicate' => 'boolean',
        'is_existing_customer' => 'boolean',
        'last_followed_up_at' => 'datetime',
    ];

    protected $attributes = [
        'source' => 'manual',
        'status' => self::STATUS_NEW,
        'is_duplicate' => false,
        'is_existing_customer' => false,
    ];

    protected static function booted(): void
    {
        static::saving(function (Lead $lead): void {
            if ($lead->phone !== null && $lead->phone !== '') {
                $lead->phone = PhoneNumber::normalize($lead->phone);
            }

            if ($lead->email !== null) {
                $email = trim((string) $lead->email);
                $lead->email = $email !== '' ? mb_strtolower($email) : null;
            }

            if ($lead->name !== null) {
                $lead->name = trim((string) $lead->name);
            }

            if ($lead->source !== null) {
                $lead->source = trim((string) $lead->source) ?: 'manual';
            }
        });
    }

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_CLAIMED,
            self::STATUS_FOLLOW_UP,
            self::STATUS_BOOKING,
            self::STATUS_PAYMENT_VERIFIED,
            self::STATUS_CONVERTED,
            self::STATUS_NO_RESPONSE,
            self::STATUS_LOST,
        ];
    }

    public function spaBranch(): BelongsTo
    {
        return $this->belongsTo(SpaBranch::class);
    }

    public function beautician(): BelongsTo
    {
        return $this->belongsTo(Beautician::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(LeadImport::class, 'lead_import_id');
    }

    public function getCodeAttribute(): string
    {
        $date = $this->created_at?->format('ymd') ?? now()->format('ymd');

        return sprintf('LD-%s%04d', $date, (int) $this->id);
    }

    public function getPhoneE164Attribute(): string
    {
        return PhoneNumber::toE164($this->phone);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'NEW',
            self::STATUS_CLAIMED => 'CLAIMED',
            self::STATUS_FOLLOW_UP => 'FOLLOW-UP',
            self::STATUS_BOOKING => 'BOOKING',
            self::STATUS_PAYMENT_VERIFIED => 'PAYMENT VERIFIED',
            self::STATUS_CONVERTED => 'CONVERTED',
            self::STATUS_NO_RESPONSE => 'NO RESPONSE',
            self::STATUS_LOST => 'LOST',
            default => strtoupper(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';
        $phoneNorm = PhoneNumber::normalize($term);

        return $query->where(function (Builder $q) use ($like, $phoneNorm, $term): void {
            $q->where('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('source', 'like', $like);

            if ($phoneNorm !== '') {
                $q->orWhereIn('phone', PhoneNumber::variants($phoneNorm));
            }

            // Lead code search: LD-yymmdd#### → id
            if (preg_match('/^LD-?\d{6}(\d+)$/i', preg_replace('/\s+/', '', $term) ?? '', $m)) {
                $q->orWhere('id', (int) $m[1]);
            } elseif (ctype_digit($term)) {
                $q->orWhere('id', (int) $term);
            }
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if ($status === null || $status === '' || $status === 'all') {
            return $query;
        }

        return $query->where('status', $status);
    }
}
