<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:120'],
            'recipient' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'line1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'ghn_province_id' => ['nullable', 'integer', 'min:1'],
            'ghn_province_name' => ['nullable', 'string', 'max:120'],
            'ghn_district_id' => ['nullable', 'integer', 'min:1'],
            'ghn_district_name' => ['nullable', 'string', 'max:120'],
            'ghn_ward_code' => ['nullable', 'string', 'max:30'],
            'ghn_ward_name' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:500'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
