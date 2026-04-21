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
            'sku' => ['required', 'string', 'max:80', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
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
}
