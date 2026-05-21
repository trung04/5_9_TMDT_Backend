<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardRedemption extends Model
{
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_PENDING = 'PENDING';

    protected $fillable = [
        'user_id',
        'title',
        'points_used',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'points_used' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
