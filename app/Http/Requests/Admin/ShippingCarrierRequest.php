<?php

namespace App\Http\Requests\Admin;

use App\Models\ShippingCarrier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShippingCarrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'name' => trim((string) $this->input('name')),
            'provider' => strtoupper(trim((string) $this->input('provider', ShippingCarrier::PROVIDER_MANUAL))),
            'tracking_url_template' => trim((string) $this->input('tracking_url_template', '')),
            'default_required_note' => strtoupper(trim((string) $this->input('default_required_note', 'KHONGCHOXEMHANG'))),
            'pickup_name' => trim((string) $this->input('pickup_name', '')),
            'pickup_phone' => trim((string) $this->input('pickup_phone', '')),
            'pickup_address' => trim((string) $this->input('pickup_address', '')),
            'pickup_ward_code' => trim((string) $this->input('pickup_ward_code', '')),
            'pickup_ward_name' => trim((string) $this->input('pickup_ward_name', '')),
            'pickup_district_name' => trim((string) $this->input('pickup_district_name', '')),
            'pickup_province_name' => trim((string) $this->input('pickup_province_name', '')),
        ]);
    }

    public function rules(): array
    {
        $carrierId = $this->route('carrier')?->id;

        return [
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('shipping_carriers', 'code')->ignore($carrierId),
            ],
            'name' => ['required', 'string', 'max:120'],
            'provider' => ['required', 'string', Rule::in(ShippingCarrier::allowedProviders())],
            'tracking_url_template' => ['nullable', 'string', 'max:255'],
            'default_weight' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'default_length' => ['nullable', 'integer', 'min:1', 'max:200'],
            'default_width' => ['nullable', 'integer', 'min:1', 'max:200'],
            'default_height' => ['nullable', 'integer', 'min:1', 'max:200'],
            'default_service_type_id' => ['nullable', 'integer', Rule::in([2, 5])],
            'default_payment_type_id' => ['nullable', 'integer', Rule::in([1, 2])],
            'default_required_note' => ['nullable', 'string', Rule::in(['CHOTHUHANG', 'CHOXEMHANGKHONGTHU', 'KHONGCHOXEMHANG'])],
            'pickup_name' => ['nullable', 'string', 'max:120'],
            'pickup_phone' => ['nullable', 'string', 'max:20'],
            'pickup_address' => ['nullable', 'string', 'max:255'],
            'pickup_ward_code' => ['nullable', 'string', 'max:30'],
            'pickup_ward_name' => ['nullable', 'string', 'max:120'],
            'pickup_district_id' => ['nullable', 'integer', 'min:1'],
            'pickup_district_name' => ['nullable', 'string', 'max:120'],
            'pickup_province_id' => ['nullable', 'integer', 'min:1'],
            'pickup_province_name' => ['nullable', 'string', 'max:120'],
            'settings' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'is_deleted' => ['nullable', 'boolean'],
        ];
    }
}
