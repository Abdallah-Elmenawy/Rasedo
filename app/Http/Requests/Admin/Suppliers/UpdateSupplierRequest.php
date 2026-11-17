<?php

namespace App\Http\Requests\Admin\Suppliers;

use App\Enums\PartyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('supplier')?->id;

        return [
            'code' => ['nullable', 'string', 'max:50', Rule::unique('suppliers', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'opening_balance' => ['nullable', 'numeric'],
            'status' => ['required', Rule::in([PartyStatus::Active->value, PartyStatus::Inactive->value])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
