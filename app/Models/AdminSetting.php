<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminSetting extends Model
{
    protected $fillable = [
        'user_id',
        'store_name',
        'support_email',
        'support_phone',
        'low_stock_threshold',
        'dashboard_refresh_seconds',
        'order_auto_confirm',
        'send_daily_summary',
        'maintenance_mode',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'low_stock_threshold' => 'integer',
            'dashboard_refresh_seconds' => 'integer',
            'order_auto_confirm' => 'boolean',
            'send_daily_summary' => 'boolean',
            'maintenance_mode' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
