<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'sku' => ['required', 'string', 'max:80', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'image_url' => $this->hasFile('image_url')
                ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp']
                : ['nullable', 'string', 'max:2048'],
            'images' => ['nullable', 'array'],
            'images.*' => [$this->filePondImageRule()],

            'sale_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_deleted' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Danh mục là bắt buộc.',
            'category_id.integer' => 'Danh mục không hợp lệ.',
            'category_id.exists' => 'Danh mục không tồn tại.',

            'supplier_id.integer' => 'Nhà cung cấp không hợp lệ.',
            'supplier_id.exists' => 'Nhà cung cấp không tồn tại.',

            'sku.required' => 'SKU là bắt buộc.',
            'sku.max' => 'SKU không được vượt quá 80 ký tự.',
            'sku.unique' => 'SKU đã tồn tại.',

            'name.required' => 'Tên sản phẩm là bắt buộc.',
            'name.max' => 'Tên sản phẩm không được vượt quá 180 ký tự.',

            'sale_price.required' => 'Giá bán là bắt buộc.',
            'sale_price.numeric' => 'Giá bán phải là số.',
            'sale_price.min' => 'Giá bán không được âm.',

            'stock_quantity.required' => 'Số lượng tồn là bắt buộc.',
            'stock_quantity.integer' => 'Số lượng tồn phải là số nguyên.',
            'stock_quantity.min' => 'Số lượng tồn không được âm.',

            'is_active.boolean' => 'Trạng thái hoạt động không hợp lệ.',
        ];
    }

    private function filePondImageRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (is_string($value)) {
                return;
            }

            if (! $value instanceof \Illuminate\Http\UploadedFile || ! $value->isValid()) {
                $fail('Anh san pham khong hop le.');
                return;
            }

            if (! in_array($value->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $fail('Anh san pham phai co dinh dang jpg, jpeg, png hoac webp.');
                return;
            }

        };
    }
}
