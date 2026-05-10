<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'recipient_name' => trim((string) $this->input('recipient_name')),
            'recipient_phone' => trim((string) $this->input('recipient_phone')),
            'shipping_address' => trim((string) $this->input('shipping_address')),
            'note' => trim((string) $this->input('note', '')),
            'payment_method' => trim((string) $this->input('payment_method', Order::PAYMENT_METHOD_COD)),
            'payment_gateway' => trim((string) $this->input('payment_gateway', '')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:120'],
            'recipient_phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', Rule::in(Order::allowedPaymentMethods())],
            'payment_gateway' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Recipient name is required.',
            'recipient_name.max' => 'Recipient name may not be greater than 120 characters.',
            'recipient_phone.required' => 'Recipient phone is required.',
            'recipient_phone.max' => 'Recipient phone may not be greater than 20 characters.',
            'shipping_address.required' => 'Shipping address is required.',
            'shipping_address.max' => 'Shipping address may not be greater than 255 characters.',
            'note.max' => 'Note may not be greater than 1000 characters.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Selected payment method is invalid.',
            'payment_gateway.max' => 'Payment gateway may not be greater than 120 characters.',
        ];
    }
}
