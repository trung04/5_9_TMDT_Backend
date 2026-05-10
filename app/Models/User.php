<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    public const ROLE_CUSTOMER = 'CUSTOMER';
    public const ROLE_ADMIN = 'ADMIN';
    public const ROLE_WAREHOUSE_STAFF = 'WAREHOUSE_STAFF';
    public const ROLE_SUPPLIER = 'SUPPLIER';

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_INACTIVE = 'INACTIVE';

    public const STATUS_BLOCKED = 'BLOCKED';

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The password column used by Laravel authentication.
     *
     * @var string
     */
    protected $authPasswordName = 'password_hash';

    /**
     * Disable remember-me token support because the table has no column for it.
     *
     * @var string
     */
    protected $rememberTokenName = '';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password_hash',
        'address',
        'city',
        'favorite_region',
        'avatar_url',
        'newsletter',
        'sms_alerts',
        'order_email',
        'security_alerts',
        'reward_points',
        'reward_tier',
        'next_tier_points',
        'role',
        'status',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'newsletter' => 'boolean',
            'sms_alerts' => 'boolean',
            'order_email' => 'boolean',
            'security_alerts' => 'boolean',
            'reward_points' => 'integer',
            'next_tier_points' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Determine whether the account is allowed to sign in.
     */
    public function canAuthenticate(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->is_active;
    }

    /**
     * Get the carts that belong to this user.
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Get the active cart for this user.
     */
    public function activeCart(): HasOne
    {
        return $this->hasOne(Cart::class)->where('status', Cart::STATUS_ACTIVE);
    }

    /**
     * Get the orders that belong to this user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function rewardRedemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function adminSetting(): HasOne
    {
        return $this->hasOne(AdminSetting::class);
    }

    public function createdSupplierInvitations(): HasMany
    {
        return $this->hasMany(SupplierInvitation::class, 'created_by_user_id');
    }
}
