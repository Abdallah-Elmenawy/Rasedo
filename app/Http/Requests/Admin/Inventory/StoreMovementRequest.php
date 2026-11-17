<?php

namespace App\Http\Requests\Admin\Inventory;

use App\Enums\InventoryMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', Rule::in(array_column(InventoryMovementType::cases(), 'value'))],
            'qty' => ['required', 'numeric', 'min:0.001'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
