<?php

namespace App\Http\Requests\Admin\Treasury;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_treasury_id' => ['required', 'exists:treasuries,id'],
            'to_treasury_id' => ['required', 'exists:treasuries,id', 'different:from_treasury_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transferred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
