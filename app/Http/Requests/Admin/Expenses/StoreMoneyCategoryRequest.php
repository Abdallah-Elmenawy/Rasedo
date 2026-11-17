<?php

namespace App\Http\Requests\Admin\Expenses;

use App\Enums\MoneyEntryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMoneyCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', Rule::in([MoneyEntryType::Expense->value, MoneyEntryType::Revenue->value])],
            'account_id' => ['nullable', 'exists:accounts,id'],
        ];
    }
}
