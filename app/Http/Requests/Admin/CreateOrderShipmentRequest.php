<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'recipient_name' => trim((string) $this->input('recipient_name', '')),
            'recipient_phone' => trim((string) $this->input('recipient_phone', '')),
            'required_note' => strtoupper(trim((string) $this->input('required_note', ''))),
            'tracking_code' => trim((string) $this->input('tracking_code', '')),
            'tracking_url' => trim((string) $this->input('tracking_url', '')),
            'shipping_line1' => trim((string) $this->input('shipping_line1', '')),
            'shipping_province_name' => trim((string) $this->input('shipping_province_name', '')),
            'shipping_district_name' => trim((string) $this->input('shipping_district_name', '')),
            'shipping_ward_code' => trim((string) $this->input('shipping_ward_code', '')),
            'shipping_ward_name' => trim((string) $this->input('shipping_ward_name', '')),
            'note' => trim((string) $this->input('note', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'shipping_carrier_id' => ['required', 'integer', 'exists:shipping_carriers,id'],
            'recipient_name' => ['nullable', 'string', 'max:120'],
            'recipient_phone' => ['nullable', 'string', 'max:20'],
            'tracking_code' => ['nullable', 'string', 'max:120'],
            'tracking_url' => ['nullable', 'string', 'max:255'],
            'service_type_id' => ['nullable', 'integer', Rule::in([1, 2, 5])],
            'payment_type_id' => ['nullable', 'integer', Rule::in([1, 2])],
            'required_note' => ['nullable', 'string', Rule::in(['CHOTHUHANG', 'CHOXEMHANGKHONGTHU', 'KHONGCHOXEMHANG'])],
            'weight' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'length' => ['nullable', 'integer', 'min:1', 'max:200'],
            'width' => ['nullable', 'integer', 'min:1', 'max:200'],
            'height' => ['nullable', 'integer', 'min:1', 'max:200'],
            'shipping_line1' => ['nullable', 'string', 'max:255'],
            'shipping_province_id' => ['nullable', 'integer', 'min:1'],
            'shipping_province_name' => ['nullable', 'string', 'max:120'],
            'shipping_district_id' => ['nullable', 'integer', 'min:1'],
            'shipping_district_name' => ['nullable', 'string', 'max:120'],
            'shipping_ward_code' => ['nullable', 'string', 'max:30'],
            'shipping_ward_name' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
