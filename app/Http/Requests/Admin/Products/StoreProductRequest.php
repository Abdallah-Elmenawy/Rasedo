<?php

namespace App\Http\Requests\Admin\Products;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'type' => ['required', 'in:product,service'],
            'unit' => ['required', 'string', 'max:30'],
            'cost_price' => ['nullable', 'numeric'],
            'sale_price' => ['required', 'numeric'],
            'min_stock' => ['nullable', 'numeric'],
            'current_stock' => ['nullable', 'numeric'],
            'is_active' => ['nullable', 'boolean'],
            'track_serial' => ['nullable', 'boolean'],
            'pieces_per_box' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function productData(): array
    {
        return $this->validated() + [
            'is_active' => $this->boolean('is_active', true),
            'track_serial' => $this->boolean('track_serial'),
        ];
    }
}
