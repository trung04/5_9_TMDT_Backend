<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminRole extends Model
{
    public const SUPER_ADMIN_SLUG = 'super_admin';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_super',
        'is_system',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'is_super' => 'boolean',
            'is_system' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_role_permission')
            ->orderBy('admin_permissions.group')
            ->orderBy('admin_permissions.key');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }
}
