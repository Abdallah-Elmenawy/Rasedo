<?php

namespace App\Http\Requests\Admin\Accounting;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'unique:accounts,code'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(array_column(AccountType::cases(), 'value'))],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'opening_balance' => ['nullable', 'numeric'],
        ];
    }
}
