<?php

declare(strict_types=1);

namespace Modules\Lead\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Beautician\Entities\Beautician;
use Modules\SpaBranch\Entities\SpaBranch;
use Modules\Support\Eloquent\Model;
use Modules\User\Entities\User;

class LeadImport extends Model
{
    public const METHOD_PASTE = 'paste';

    public const METHOD_EXCEL = 'excel';

    public const METHOD_CSV = 'csv';

    public const METHOD_MANUAL = 'manual';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $table = 'lead_imports';

    protected $fillable = [
        'batch_code',
        'method',
        'file_name',
        'uploaded_by',
        'spa_branch_id',
        'beautician_id',
        'source',
        'raw_count',
        'ready_count',
        'unique_count',
        'duplicate_count',
        'existing_count',
        'invalid_count',
        'imported_count',
        'status',
    ];

    protected $casts = [
        'uploaded_by' => 'integer',
        'spa_branch_id' => 'integer',
        'beautician_id' => 'integer',
        'raw_count' => 'integer',
        'ready_count' => 'integer',
        'unique_count' => 'integer',
        'duplicate_count' => 'integer',
        'existing_count' => 'integer',
        'invalid_count' => 'integer',
        'imported_count' => 'integer',
    ];

    protected $attributes = [
        'source' => 'import',
        'status' => self::STATUS_COMPLETED,
        'raw_count' => 0,
        'ready_count' => 0,
        'unique_count' => 0,
        'duplicate_count' => 0,
        'existing_count' => 0,
        'invalid_count' => 0,
        'imported_count' => 0,
    ];

    /**
     * @return list<string>
     */
    public static function methods(): array
    {
        return [
            self::METHOD_PASTE,
            self::METHOD_EXCEL,
            self::METHOD_CSV,
            self::METHOD_MANUAL,
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function spaBranch(): BelongsTo
    {
        return $this->belongsTo(SpaBranch::class);
    }

    public function beautician(): BelongsTo
    {
        return $this->belongsTo(Beautician::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'lead_import_id');
    }

    public function getMethodLabelAttribute(): string
    {
        return strtoupper((string) $this->method);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'COMPLETED',
            self::STATUS_FAILED => 'FAILED',
            default => strtoupper(str_replace('_', ' ', (string) $this->status)),
        };
    }
}
