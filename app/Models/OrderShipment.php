<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderShipment extends Model
{
    public const STATUS_CREATED = 'created';
    public const STATUS_CANCELLED = 'cancel';

    protected $fillable = [
        'order_id',
        'shipping_carrier_id',
        'provider',
        'status',
        'tracking_code',
        'tracking_url',
        'service_type_id',
        'payment_type_id',
        'required_note',
        'weight',
        'length',
        'width',
        'height',
        'shipping_fee',
        'cod_amount',
        'expected_delivery_time',
        'raw_request',
        'raw_response',
        'synced_at',
        'cancelled_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'service_type_id' => 'integer',
            'payment_type_id' => 'integer',
            'weight' => 'integer',
            'length' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'shipping_fee' => 'decimal:2',
            'cod_amount' => 'decimal:2',
            'expected_delivery_time' => 'datetime',
            'raw_request' => 'array',
            'raw_response' => 'array',
            'synced_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCarrier::class, 'shipping_carrier_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
