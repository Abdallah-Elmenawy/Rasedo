<?php

namespace App\Http\Requests\Admin\Treasury;

use Illuminate\Foundation\Http\FormRequest;

class StoreTreasuryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:cash,bank'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'opening_balance' => ['nullable', 'numeric'],
        ];
    }
}
