<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'favorite_region' => ['nullable', 'string', 'max:120'],
            'avatar' => ['nullable', 'string'],
            'newsletter' => ['sometimes', 'boolean'],
            'sms_alerts' => ['sometimes', 'boolean'],
            'order_email' => ['sometimes', 'boolean'],
            'security_alerts' => ['sometimes', 'boolean'],
        ];
    }
}
