<?php

namespace App\Models;

use App\Models\Concerns\HasActiveState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingCarrier extends Model
{
    use HasActiveState;

    public const PROVIDER_GHN = 'GHN';
    public const PROVIDER_MANUAL = 'MANUAL';

    protected $fillable = [
        'code',
        'name',
        'provider',
        'tracking_url_template',
        'default_weight',
        'default_length',
        'default_width',
        'default_height',
        'default_service_type_id',
        'default_payment_type_id',
        'default_required_note',
        'pickup_name',
        'pickup_phone',
        'pickup_address',
        'pickup_ward_code',
        'pickup_ward_name',
        'pickup_district_id',
        'pickup_district_name',
        'pickup_province_id',
        'pickup_province_name',
        'settings',
        'is_active',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'default_weight' => 'integer',
            'default_length' => 'integer',
            'default_width' => 'integer',
            'default_height' => 'integer',
            'default_service_type_id' => 'integer',
            'default_payment_type_id' => 'integer',
            'pickup_district_id' => 'integer',
            'pickup_province_id' => 'integer',
            'settings' => 'array',
            'is_active' => 'boolean',
            'is_deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public static function allowedProviders(): array
    {
        return [
            self::PROVIDER_GHN,
            self::PROVIDER_MANUAL,
        ];
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(OrderShipment::class);
    }
}
