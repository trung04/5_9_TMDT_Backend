<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const PAYMENT_METHOD_COD = 'COD';
    public const PAYMENT_METHOD_BANK_TRANSFER = 'BANK_TRANSFER';

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_CONFIRMED = 'CONFIRMED';
    public const STATUS_PACKED = 'PACKED';
    public const STATUS_SHIPPED = 'SHIPPED';
    public const STATUS_DELIVERED = 'DELIVERED';
    public const STATUS_DELIVERY_FAILED = 'DELIVERY_FAILED';
    public const STATUS_CANCELLED = 'CANCELLED';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'order_no',
        'recipient_name',
        'recipient_phone',
        'shipping_address',
        'shipping_line1',
        'shipping_province_id',
        'shipping_province_name',
        'shipping_district_id',
        'shipping_district_name',
        'shipping_ward_code',
        'shipping_ward_name',
        'payment_method',
        'status',
        'subtotal',
        'shipping_fee',
        'discount_amount',
        'total_amount',
        'stock_deducted',
        'stock_deducted_at',
        'shipping_carrier',
        'shipping_code',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'note',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'stock_deducted' => 'boolean',
            'shipping_province_id' => 'integer',
            'shipping_district_id' => 'integer',
            'stock_deducted_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return list<string>
     */
    public static function allowedStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PACKED,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_DELIVERY_FAILED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function allowedPaymentMethods(): array
    {
        return [
            self::PAYMENT_METHOD_COD,
            self::PAYMENT_METHOD_BANK_TRANSFER,
        ];
    }

    /**
     * Get the user that owns this order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items that belong to this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payment record for this order.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get the status history entries for this order.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('changed_at');
    }

    /**
     * Get the payment status history entries for this order.
     */
    public function paymentStatusHistory(): HasMany
    {
        return $this->hasMany(PaymentStatusHistory::class)->orderBy('changed_at');
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(OrderShipment::class);
    }
}
