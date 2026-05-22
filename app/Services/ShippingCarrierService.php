<?php

namespace App\Services;

use App\Models\ShippingCarrier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShippingCarrierService
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ShippingCarrier::query()->orderByDesc('id');

        if (! empty($filters['active_only'])) {
            $query->available();
        }

        if (! empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('provider', 'like', "%{$keyword}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function create(array $attributes): ShippingCarrier
    {
        return ShippingCarrier::query()->create($this->payload($attributes));
    }

    public function update(ShippingCarrier $carrier, array $attributes): ShippingCarrier
    {
        if (($attributes['is_active'] ?? false) === true && ! array_key_exists('is_deleted', $attributes)) {
            $attributes['is_deleted'] = false;
        }

        $carrier->update($this->payload($attributes, $carrier));

        return $carrier->refresh();
    }

    public function delete(ShippingCarrier $carrier): ShippingCarrier
    {
        $carrier->markDeleted();

        return $carrier->refresh();
    }

    public function payloadForResponse(ShippingCarrier $carrier): array
    {
        return [
            'id' => $carrier->id,
            'code' => $carrier->code,
            'name' => $carrier->name,
            'provider' => $carrier->provider,
            'tracking_url_template' => $carrier->tracking_url_template,
            'default_weight' => $carrier->default_weight,
            'default_length' => $carrier->default_length,
            'default_width' => $carrier->default_width,
            'default_height' => $carrier->default_height,
            'default_service_type_id' => $carrier->default_service_type_id,
            'default_payment_type_id' => $carrier->default_payment_type_id,
            'default_required_note' => $carrier->default_required_note,
            'pickup_name' => $carrier->pickup_name,
            'pickup_phone' => $carrier->pickup_phone,
            'pickup_address' => $carrier->pickup_address,
            'pickup_ward_code' => $carrier->pickup_ward_code,
            'pickup_ward_name' => $carrier->pickup_ward_name,
            'pickup_district_id' => $carrier->pickup_district_id,
            'pickup_district_name' => $carrier->pickup_district_name,
            'pickup_province_id' => $carrier->pickup_province_id,
            'pickup_province_name' => $carrier->pickup_province_name,
            'settings' => $carrier->settings,
            'is_active' => (bool) $carrier->is_active,
            'is_deleted' => (bool) $carrier->is_deleted,
            'created_at' => $carrier->created_at,
            'updated_at' => $carrier->updated_at,
        ];
    }

    private function payload(array $attributes, ?ShippingCarrier $carrier = null): array
    {
        return [
            'code' => $attributes['code'] ?? $carrier?->code,
            'name' => $attributes['name'] ?? $carrier?->name,
            'provider' => $attributes['provider'] ?? $carrier?->provider ?? ShippingCarrier::PROVIDER_MANUAL,
            'tracking_url_template' => ($attributes['tracking_url_template'] ?? $carrier?->tracking_url_template) ?: null,
            'default_weight' => $attributes['default_weight'] ?? $carrier?->default_weight ?? 1000,
            'default_length' => $attributes['default_length'] ?? $carrier?->default_length ?? 20,
            'default_width' => $attributes['default_width'] ?? $carrier?->default_width ?? 20,
            'default_height' => $attributes['default_height'] ?? $carrier?->default_height ?? 10,
            'default_service_type_id' => $attributes['default_service_type_id'] ?? $carrier?->default_service_type_id ?? 2,
            'default_payment_type_id' => $attributes['default_payment_type_id'] ?? $carrier?->default_payment_type_id ?? 1,
            'default_required_note' => $attributes['default_required_note'] ?? $carrier?->default_required_note ?? 'KHONGCHOXEMHANG',
            'pickup_name' => ($attributes['pickup_name'] ?? $carrier?->pickup_name) ?: null,
            'pickup_phone' => ($attributes['pickup_phone'] ?? $carrier?->pickup_phone) ?: null,
            'pickup_address' => ($attributes['pickup_address'] ?? $carrier?->pickup_address) ?: null,
            'pickup_ward_code' => ($attributes['pickup_ward_code'] ?? $carrier?->pickup_ward_code) ?: null,
            'pickup_ward_name' => ($attributes['pickup_ward_name'] ?? $carrier?->pickup_ward_name) ?: null,
            'pickup_district_id' => $attributes['pickup_district_id'] ?? $carrier?->pickup_district_id,
            'pickup_district_name' => ($attributes['pickup_district_name'] ?? $carrier?->pickup_district_name) ?: null,
            'pickup_province_id' => $attributes['pickup_province_id'] ?? $carrier?->pickup_province_id,
            'pickup_province_name' => ($attributes['pickup_province_name'] ?? $carrier?->pickup_province_name) ?: null,
            'settings' => $attributes['settings'] ?? $carrier?->settings,
            'is_active' => $attributes['is_active'] ?? $carrier?->is_active ?? true,
            'is_deleted' => $attributes['is_deleted'] ?? $carrier?->is_deleted ?? false,
        ];
    }
}
