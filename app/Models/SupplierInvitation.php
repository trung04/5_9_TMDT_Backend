<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierInvitation extends Model
{
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_SENT = 'SENT';

    protected $fillable = [
        'supplier_name',
        'contact_name',
        'email',
        'categories',
        'note',
        'status',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
