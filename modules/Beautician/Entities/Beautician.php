<?php

namespace Modules\Beautician\Entities;

use Modules\SpaBranch\Entities\SpaBranch;
use Modules\Beautician\Admin\BeauticianTable;
use Modules\Media\Entities\File;
use Modules\Support\Eloquent\Model;
use Modules\Media\Eloquent\HasMedia;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;
use Illuminate\Support\Collection;

class Beautician extends Model
{
    use HasMedia;

    public ?string $portalPassword = null;

    public ?string $portalEmail = null;

    protected $with = ['files', 'user'];

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'profile_color',
        'job_title',
        'is_active',
        'position',
        'user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    protected static function booted(): void
    {
        static::saving(function (Beautician $beautician) {
            if ($beautician->phone !== null && $beautician->phone !== '') {
                $beautician->phone = PhoneNumber::normalize($beautician->phone);
            }
        });
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function spaBranches()
    {
        return $this->belongsToMany(SpaBranch::class, 'beautician_spa_branch');
    }


    public static function findForUser(?int $userId): ?self
    {
        if (! $userId) {
            return null;
        }

        return static::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();
    }


    public static function activeList(): Collection
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }


    public static function sqlFullName(string $table = 'beauticians'): string
    {
        return "TRIM(CONCAT({$table}.first_name, ' ', COALESCE({$table}.last_name, '')))";
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    public static function activeListForCheckout(): array
    {
        $query = static::query()
            ->with('files')
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('first_name')
            ->orderBy('last_name');

        if (is_module_enabled('SpaBranch')) {
            $query->with('spaBranches:id');
        }

        return $query->get()
            ->map(fn (Beautician $beautician) => [
                'id' => $beautician->id,
                'name' => $beautician->name,
                'job_title' => $beautician->job_title,
                'profile_color' => $beautician->profile_color ?: '#6366f1',
                'profile_image' => $beautician->profile_image->exists
                    ? $beautician->profile_image->path
                    : null,
                'spa_branch_ids' => is_module_enabled('SpaBranch')
                    ? $beautician->spaBranches->pluck('id')->values()->all()
                    : [],
            ])
            ->values()
            ->all();
    }


    public function getProfileImageAttribute(): File
    {
        return $this->files->where('pivot.zone', 'profile')->first() ?: new File;
    }


    public function displayAvatarUrl(): ?string
    {
        if ($this->profile_image->exists) {
            return $this->profile_image->path;
        }

        $this->loadMissing('user');

        return $this->user?->avatarUrl();
    }


    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }


    public function getNameAttribute(): string
    {
        return $this->full_name !== '' ? $this->full_name : (string) $this->first_name;
    }


    public function getInitialsAttribute(): string
    {
        $first = mb_substr(trim((string) $this->first_name), 0, 1);
        $last = mb_substr(trim((string) $this->last_name), 0, 1);
        $initials = strtoupper($first . $last);

        return $initials !== '' ? $initials : '?';
    }


    public static function namesForFilter(): Collection
    {
        return static::query()
            ->orderBy('position')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('name', 'id');
    }


    public function table($request = null)
    {
        $nameSql = static::sqlFullName();

        $query = $this->newQuery()
            ->with('files')
            ->select('beauticians.*')
            ->selectRaw("{$nameSql} as name");

        if (is_module_enabled('SpaBranch')) {
            $query->with('spaBranches');
        }

        return new BeauticianTable($query);
    }
}
