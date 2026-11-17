<?php

namespace App\Http\Requests\Admin\Expenses;

use App\Enums\MoneyEntryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMoneyEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([MoneyEntryType::Expense->value, MoneyEntryType::Revenue->value])],
            'money_category_id' => ['required', 'exists:money_categories,id'],
            'treasury_id' => ['required', 'exists:treasuries,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'entry_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }
}
