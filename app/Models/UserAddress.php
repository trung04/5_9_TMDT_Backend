<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'recipient',
        'phone',
        'line1',
        'city',
        'ghn_province_id',
        'ghn_province_name',
        'ghn_district_id',
        'ghn_district_name',
        'ghn_ward_code',
        'ghn_ward_name',
        'note',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'ghn_province_id' => 'integer',
            'ghn_district_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
