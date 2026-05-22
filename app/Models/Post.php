<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_PUBLISHED = 'PUBLISHED';

    protected $fillable = [
        'created_by_user_id',
        'title',
        'excerpt',
        'body',
        'cover_image_url',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class)->orderByDesc('created_at');
    }

    public function visibleComments(): HasMany
    {
        return $this->hasMany(PostComment::class)
            ->where('status', PostComment::STATUS_VISIBLE)
            ->orderByDesc('created_at');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }
}
