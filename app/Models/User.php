<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
}
