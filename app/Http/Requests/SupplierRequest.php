<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'supplier_code' => trim((string) $this->input('supplier_code')),
            'name' => trim((string) $this->input('name')),
            'contact_name' => trim((string) $this->input('contact_name', '')),
            'phone' => trim((string) $this->input('phone')),
            'email' => trim((string) $this->input('email', '')),
            'address' => trim((string) $this->input('address', '')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|\Illuminate\Contracts\Validation\ValidationRule>|string>
     */
    public function rules(): array
    {
        $supplierId = $this->route('supplier')?->id;

        return [
            'supplier_code' => [
                'bail',
                'required',
                'string',
                'max:50',
                Rule::unique('suppliers', 'supplier_code')->ignore($supplierId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'phone' => [
                'bail',
                'required',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\-\(\)\s]+$/',
                Rule::unique('suppliers', 'phone')->ignore($supplierId),
            ],
            'email' => [
                'bail',
                'nullable',
                'email',
                'max:120',
                Rule::unique('suppliers', 'email')->ignore($supplierId),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}