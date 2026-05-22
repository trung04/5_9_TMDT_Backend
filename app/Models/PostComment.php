<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostComment extends Model
{
    public const STATUS_VISIBLE = 'VISIBLE';
    public const STATUS_HIDDEN = 'HIDDEN';

    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'status',
        'hidden_by_user_id',
        'hidden_at',
    ];

    protected function casts(): array
    {
        return [
            'hidden_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hiddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_by_user_id');
    }
}
