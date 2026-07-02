<?php

namespace Modules\User\Entities;

use Modules\Beautician\Entities\Beautician;
use Modules\Loyalty\Entities\LoyaltyWallet;
use Modules\Order\Entities\Order;
use Modules\User\Admin\UserTable;
use Illuminate\Http\JsonResponse;
use Modules\Review\Entities\Review;
use Illuminate\Auth\Authenticatable;
use Modules\Address\Entities\Address;
use Modules\Product\Entities\Product;
use Modules\Media\Entities\File;
use Modules\Media\Eloquent\HasMedia;
use Modules\User\Repositories\Permission;
use Modules\User\Support\PhoneNumber;
use Cartalyst\Sentinel\Users\EloquentUser;
use Modules\Address\Entities\DefaultAddress;
use Illuminate\Database\Eloquent\Collection;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends EloquentUser implements AuthenticatableContract
{
    use Authenticatable;
    use HasMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'phone',
        'identity_number',
        'password',
        'last_name',
        'first_name',
        'date_of_birth',
        'referral_code',
        'referred_by_user_id',
    ];


    public function beauticianProfile()
    {
        return $this->hasOne(Beautician::class, 'user_id');
    }


    public function loyaltyWallet(): HasOne
    {
        return $this->hasOne(LoyaltyWallet::class, 'user_id');
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions' => 'json',
        'last_login' => 'datetime',
        'date_of_birth' => 'date',
    ];


    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->phone !== null && $user->phone !== '') {
                $user->phone = PhoneNumber::normalize($user->phone);
            }

            if ($user->identity_number !== null && $user->identity_number !== '') {
                $user->identity_number = strtoupper(preg_replace('/\s+/', '', $user->identity_number));
            }
        });
    }


    public static function registered($email)
    {
        return static::where('email', $email)->exists();
    }


    public static function findByEmail($email)
    {
        return static::where('email', $email)->first();
    }


    public static function findByPhone(string $phone): ?self
    {
        $normalized = PhoneNumber::normalize($phone);

        if ($normalized === '') {
            return null;
        }

        return static::whereIn('phone', PhoneNumber::variants($normalized))->first();
    }


    public static function totalCustomers()
    {
        return Role::findOrNew(setting('customer_role'))->users()->count();
    }


    /**
     * Login the user.
     *
     * @return $this|bool
     */
    public function login()
    {
        return auth()->login($this);
    }


    /**
     * Determine if the user is a customer.
     *
     * @return bool
     */
    public function isCustomer()
    {
        if ($this->hasRoleName('admin')) {
            return false;
        }

        return $this->hasRoleId(setting('customer_role'));
    }


    /**
     * Whether this user should only access the beautician job-sheet portal.
     */
    public function isBeauticianOnly(): bool
    {
        if ($this->hasRoleName('admin')) {
            return false;
        }

        if (! $this->hasRoleName('Beautician')) {
            return false;
        }

        return Beautician::findForUser($this->id) !== null;
    }


    /**
     * Preferred admin landing page for this user.
     */
    public function adminHomeRoute(): string
    {
        if ($this->isBeauticianOnly()) {
            $beautician = Beautician::findForUser($this->id);

            if ($beautician) {
                return route('admin.beauticians.portal.dashboard', $beautician->id);
            }

            return route('admin.treatment_reservations.portal');
        }

        return route('admin.dashboard.index');
    }


    public function adminProfileRoute(): string
    {
        if (! $this->isBeauticianOnly()) {
            return route('admin.profile.edit');
        }

        $beautician = Beautician::findForUser($this->id);

        if ($beautician) {
            return route('admin.beauticians.portal.account', $beautician->id);
        }

        return route('admin.treatment_reservations.portal.account');
    }


    /**
     * Checks if a user belongs to the given Role Name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasRoleName($name)
    {
        return $this->roles()->whereTranslation('name', $name)->count() !== 0;
    }


    /**
     * Get the roles of the user.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withTimestamps();
    }


    /**
     * Checks if a user belongs to the given Role ID.
     *
     * @param int $roleId
     *
     * @return bool
     */
    public function hasRoleId($roleId)
    {
        return $this->roles()->whereId($roleId)->count() !== 0;
    }


    /**
     * Check if the current user is activated.
     *
     * @return bool
     */
    public function isActivated()
    {
        return Activation::completed($this);
    }


    /**
     * Get the recent orders of the user.
     *
     * @param int $take
     *
     * @return Collection
     */
    public function recentOrders($take)
    {
        $with = ['products', 'beautician', 'spaBranch'];

        if (is_module_enabled('TreatmentReservation')) {
            $with[] = 'treatmentBooking';
        }

        return $this->orders()->with($with)->latest()->take($take)->get();
    }


    /**
     * Get the orders of the user.
     *
     * @return HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }


    /**
     * Get the default address of the user.
     *
     * @return HasMany
     */
    public function defaultAddress()
    {
        return $this->hasOne(DefaultAddress::class, 'customer_id')->withDefault();
    }


    /**
     * Get the addresses of the user.
     *
     * @return HasMany
     */
    public function addresses()
    {
        return $this->hasMany(Address::class, 'customer_id');
    }


    /**
     * Get the reviews of the user.
     *
     * @return HasMany
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }


    /**
     * Get the full name of the user.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }


    public function getProfileImageAttribute(): File
    {
        if (! $this->relationLoaded('files')) {
            $this->load('files');
        }

        return $this->files->where('pivot.zone', 'profile')->first() ?: new File;
    }


    public function getInitialsAttribute(): string
    {
        $first = mb_substr(trim((string) $this->first_name), 0, 1);
        $last = mb_substr(trim((string) $this->last_name), 0, 1);
        $initials = strtoupper($first . $last);

        return $initials !== '' ? $initials : '?';
    }


    public function avatarUrl(): ?string
    {
        if ($this->profile_image->exists) {
            return $this->profile_image->path;
        }

        $beautician = $this->relationLoaded('beauticianProfile')
            ? $this->beauticianProfile
            : Beautician::findForUser($this->id);

        if ($beautician?->profile_image->exists) {
            return $beautician->profile_image->path;
        }

        $orphanedProfile = File::query()
            ->where('user_id', $this->id)
            ->where('path', 'like', 'media/profile/%')
            ->latest('id')
            ->first();

        return $orphanedProfile?->path;
    }


    public function avatarInitial(): string
    {
        $initial = mb_substr(trim((string) $this->first_name), 0, 1);

        if ($initial !== '') {
            return strtoupper($initial);
        }

        $beautician = Beautician::findForUser($this->id);

        if ($beautician?->first_name) {
            return strtoupper(mb_substr($beautician->first_name, 0, 1));
        }

        return '?';
    }


    public function avatarBackgroundColor(): string
    {
        $beautician = $this->relationLoaded('beauticianProfile')
            ? $this->beauticianProfile
            : Beautician::findForUser($this->id);

        return $beautician?->profile_color ?: '#2584f0';
    }


    /**
     * Set user's permissions.
     *
     * @param array $permissions
     *
     * @return void
     */
    public function setPermissionsAttribute(array $permissions)
    {
        $this->attributes['permissions'] = Permission::prepare($permissions);
    }


    /**
     * Determine if the user has access to the given permissions.
     *
     * @param array|string $permissions
     *
     * @return bool
     */
    public function hasAccess($permissions)
    {
        $permissions = is_array($permissions) ? $permissions : func_get_args();

        return $this->getPermissionsInstance()->hasAccess($permissions);
    }


    /**
     * Determine if the user has access to any given permissions
     *
     * @param array|string $permissions
     *
     * @return bool
     */
    public function hasAnyAccess($permissions)
    {
        $permissions = is_array($permissions) ? $permissions : func_get_args();

        return $this->getPermissionsInstance()->hasAnyAccess($permissions);
    }


    public function wishlistHas($productId)
    {
        return self::wishlist()->where('product_id', $productId)->exists();
    }


    /**
     * Get the wishlist of the user.
     *
     * @return BelongsToMany
     */
    public function wishlist()
    {
        return $this->belongsToMany(Product::class, 'wish_lists')->withTimestamps();
    }


    public function age(?\DateTimeInterface $asOf = null): ?int
    {
        if ($this->date_of_birth === null) {
            return null;
        }

        return (int) $this->date_of_birth->diffInYears($asOf ?? now());
    }


    /**
     * Get table data for the resource
     *
     * @return JsonResponse
     */
    public function table()
    {
        return new UserTable($this->newQuery()->with(['roles', 'files', 'beauticianProfile.files', 'loyaltyWallet.tier']));
    }
}
