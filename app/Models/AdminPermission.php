<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdminPermission extends Model
{
    protected $fillable = [
        'key',
        'name',
        'group',
        'description',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_permission');
    }
}
